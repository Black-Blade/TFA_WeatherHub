<?php

declare(strict_types=1);
/*
    @file					module.php

    @author					Back-Blade and helhau
    @brief					TFA Sensor - ein Modul fuer alle Sensortypen
    @date					01.09.2026

    @see					Der Sensortyp ist eine Eigenschaft. Was mit den
                            Bytes passiert, steht im Baustein unter sensors/
                            und wird zur Laufzeit gelesen. Ein neuer Sensor
                            braucht damit kein PHP mehr.
 */

//set base dir
if (!defined('__ROOT__'))  define('__ROOT__', dirname(dirname(__FILE__)));

//load helper functionen
require_once __ROOT__ . '/libs/sensor_decode.php';
require_once __ROOT__ . '/libs/profiles.php';
require_once __ROOT__ . '/libs/frame_tools.php';

class TFASENSOR_V2 extends IPSModule
{
    public const GATEWAY = '{EADE1EC1-66C7-4E18-89FF-8AD98F499DB2}';
    public const DATA_FROM = '{99C7FBD6-D83A-4E3C-B395-CB508547A2FC}';

    public const STATUS_OK = 102;
    public const STATUS_NO_GATEWAY = 104;
    public const STATUS_NO_TYPE = 201;
    public const STATUS_NO_ID = 202;
    public const STATUS_NO_BLOCK = 203;
    private $name = 'TFASENSOR_V2';

    /*
    @author					ips and Back-Blade and helhau
    @brief					ueberschreibt die interne IPS_Create($id) Funktion

    @see					Die Variablen-Eigenschaften werden fuer alle bekannten
                            Bausteine zusammen angelegt. Die Benennung var_<ident>
                            ist dieselbe wie in den alten Einzelmodulen, damit
                            uebernommene Einstellungen erhalten bleiben.
    @date					01.09.2026
     */
    public function Create()
    {
        parent::Create();

        $this->RegisterPropertyString('var_typ', '');
        $this->RegisterPropertyString('var_sensor_id', '');

        $this->RegisterPropertyBoolean('var_debug_sensor', false);
        $this->RegisterPropertyBoolean('var_debug_parent', false);
        $this->RegisterPropertyBoolean('var_debug_cloud', false);

        $this->RegisterPropertyBoolean('var_cloud_aktivate', false);
        $this->RegisterPropertyInteger('var_cloud_waitfor_response', 500);
        $this->RegisterPropertyString('var_cloud_Host_Address', 'www.data199.com');
        $this->RegisterPropertyString('var_cloud_URL', '/gateway/put');

        $this->RegisterPropertyInteger('var_Winddirection_offset', 0);

        foreach ($this->AllIdents() as $ident) {
            $this->RegisterPropertyBoolean('var_' . $ident, false);
        }

        $this->RegisterMessage(0, IPS_KERNELSTARTED);

        $this->ConnectParent(self::GATEWAY);
    }

    /*
    @author					ips and Back-Blade and helhau
    @brief					ueberschreibt die interne IPS_ApplyChanges($id) Funktion
    @date					01.09.2026
     */
    public function ApplyChanges()
    {
        parent::ApplyChanges();

        $this->ConnectParent(self::GATEWAY);

        $typ = strtolower($this->ReadPropertyString('var_typ'));
        $id = strtoupper(trim($this->ReadPropertyString('var_sensor_id')));

        /*
            Ohne Typ und ID darf nichts durchkommen. Ein leerer Filter wuerde
            jedes Paket jeder Instanz zustellen und bei vielen Geraeten
            spuerbar Leistung kosten.
         */
        $this->SetReceiveDataFilter($typ == '' || $id == '' ? ".*\x00NICHTS\x00.*" : '.*' . $typ . $id . '.*');

        if (IPS_GetKernelRunlevel() != KR_READY) return;

        if (!$this->CheckStatus()) return;

        tfa_create_profiles($this);
        $this->CreateVariables();
    }

    /*
    @author					ips and Back-Blade and helhau
    @brief					Nachrichten des Kernels
    @date					01.09.2026
     */
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        if ($Message == IPS_KERNELSTARTED) $this->ApplyChanges();
    }

    /*
    @author					ips and Back-Blade and helhau
    @brief					Daten von der uebergeordneten Instanz
    @date					01.09.2026
     */
    public function ReceiveData($JSONString)
    {
        $def = $this->Definition();
        if ($def === false) return;

        $dataarray = json_decode($JSONString);
        if ($dataarray->DataID != self::DATA_FROM) return;

        $deviceid = strtoupper(tfa_from_transport($dataarray->DeviceID));
        $header = tfa_from_transport($dataarray->PackageHeader);
        $length = (int) tfa_from_transport($dataarray->PackageLengt);
        $payload = tfa_from_transport($dataarray->Data);
        $timestamp = (int) tfa_from_transport($dataarray->Timestamp);

        $erwartet = strtoupper($def['typ'] . trim($this->ReadPropertyString('var_sensor_id')));

        if ($deviceid != $erwartet)              return;
        if ($header != chr($def['frame']['header'])) return;
        if ($length != $def['frame']['length'])  return;

        if ($this->ReadPropertyBoolean('var_debug_parent')) {
            $this->SendDebug('parent', 'ID ' . $deviceid . ' Laenge ' . $length, 0);
        }

        $werte = tfa_decode_payload($def, $payload, $timestamp, [
            'offset'      => $this->ReadPropertyInteger('var_Winddirection_offset'),
            'raincounter' => $this->SafeValue('raincounter', 0),
            'rainfall'    => $this->SafeValue('rainfall', 0),
            'has'         => function ($ident)
            { return $this->GetIDForIdent($ident) !== false; },
        ]);

        foreach ($werte as $ident => $wert) {
            if (!$this->ReadPropertyBoolean('var_' . $ident)) continue;
            if ($this->GetIDForIdent($ident) === false)     continue;

            if ($this->ReadPropertyBoolean('var_debug_sensor')) {
                $this->SendDebug('sensor', $ident . ' = ' . $wert, 0);
            }

            $this->SetValue($ident, $wert);
        }

        if ($this->ReadPropertyBoolean('var_cloud_aktivate')) $this->SendToCloud($JSONString);
    }

    /*
    @author					ips and Back-Blade and helhau
    @brief					Baut das Konfigurationsformular
    @date					01.09.2026
     */
    public function GetConfigurationForm()
    {
        $typen = [['caption' => '-', 'value' => '']];

        foreach (tfa_sensor_registry() as $def) {
            $typen[] = [
                'caption' => strtoupper($def['typ']) . ' - ' . $def['name']
                          . ($def['article'] != '' ? ' (' . $def['article'] . ')' : ''),
                'value'   => $def['typ'],
            ];
        }

        $elements = [
            ['type' => 'Image', 'image' => $this->Logo()],
            ['type' => 'Select', 'name' => 'var_typ', 'caption' => 'sensor type', 'options' => $typen],
            ['type'       => 'ValidationTextBox', 'name' => 'var_sensor_id',
                'caption' => 'sensor id (10 digits, without the leading type)'],
        ];

        $def = $this->Definition();

        if ($def === false) {
            $elements[] = ['type' => 'Label',
                'caption'         => 'Please choose a sensor type and apply the changes, then the variables appear here.'];
        }
        else {
            if ($def['options']['windoffset']) {
                $elements[] = ['type' => 'NumberSpinner', 'name' => 'var_Winddirection_offset',
                    'caption'         => 'wind direction offset', 'minimum' => 0, 'maximum' => 15];
            }

            foreach ($def['groups'] as $g) {
                $items = [];

                foreach ($g['variables'] as $v) {
                    $items[] = ['type' => 'CheckBox', 'name' => 'var_' . $v['ident'],
                        'caption'      => $v['name']];
                }

                $elements[] = [
                    'type'    => 'PopupButton',
                    'caption' => $g['name'],
                    'popup'   => ['caption' => $g['name'], 'items' => $items],
                ];
            }
        }

        $elements[] = [
            'type'  => 'ExpansionPanel', 'caption' => 'cloud settings',
            'items' => [
                ['type' => 'CheckBox',          'name' => 'var_cloud_aktivate',        'caption' => 'cloud aktivate'],
                ['type' => 'NumberSpinner',     'name' => 'var_cloud_waitfor_response', 'caption' => 'wait for resonse(in ms)'],
                ['type' => 'ValidationTextBox', 'name' => 'var_cloud_Host_Address',    'caption' => 'cloud host address'],
                ['type' => 'ValidationTextBox', 'name' => 'var_cloud_URL',             'caption' => 'url parameter'],
            ],
        ];

        $elements[] = [
            'type'  => 'ExpansionPanel', 'caption' => 'debug',
            'items' => [
                ['type' => 'CheckBox', 'name' => 'var_debug_parent', 'caption' => 'parent'],
                ['type' => 'CheckBox', 'name' => 'var_debug_sensor', 'caption' => 'sensors'],
                ['type' => 'CheckBox', 'name' => 'var_debug_cloud',  'caption' => 'cloud'],
            ],
        ];

        return json_encode([
            'elements' => $elements,
            'status'   => [
                ['code' => self::STATUS_OK,         'icon' => 'active', 'caption' => 'active'],
                ['code' => self::STATUS_NO_GATEWAY, 'icon' => 'error',  'caption' => 'no gateway connected'],
                ['code' => self::STATUS_NO_TYPE,    'icon' => 'inactive', 'caption' => 'no sensor type chosen'],
                ['code' => self::STATUS_NO_ID,      'icon' => 'inactive', 'caption' => 'no sensor id entered'],
                ['code' => self::STATUS_NO_BLOCK,   'icon' => 'error',  'caption' => 'building block not found'],
            ],
        ]);
    }

    /*
    @author					Back-Blade and helhau
    @brief					Meldet fehlende Voraussetzungen ueber den Instanzstatus

    @return					true wenn alles vorhanden ist

    @date					01.09.2026
     */
    private function CheckStatus()
    {
        if (IPS_GetInstance($this->InstanceID)['ConnectionID'] == 0) {
            $this->SetStatus(self::STATUS_NO_GATEWAY);
            return false;
        }

        if ($this->ReadPropertyString('var_typ') == '') {
            $this->SetStatus(self::STATUS_NO_TYPE);
            return false;
        }

        if (trim($this->ReadPropertyString('var_sensor_id')) == '') {
            $this->SetStatus(self::STATUS_NO_ID);
            return false;
        }

        if ($this->Definition() === false) {
            $this->SetStatus(self::STATUS_NO_BLOCK);
            return false;
        }

        $this->SetStatus(self::STATUS_OK);
        return true;
    }

    /*
    @author					Back-Blade and helhau
    @brief					Der Baustein zum eingestellten Typ
    @date					01.09.2026
     */
    private function Definition()
    {
        return tfa_sensor_definition($this->ReadPropertyString('var_typ'));
    }

    /*
    @author					Back-Blade and helhau
    @brief					Alle Variablennamen ueber alle Bausteine hinweg
    @date					01.09.2026
     */
    private function AllIdents()
    {
        $idents = [];

        foreach (tfa_sensor_registry() as $def) {
            foreach ($def['groups'] as $g) {
                foreach ($g['variables'] as $v) $idents[$v['ident']] = true;
            }
        }

        return array_keys($idents);
    }

    /*
    @author					Back-Blade and helhau
    @brief					Legt die angehakten Variablen an

    @see					Abgewaehlte Variablen werden nicht geloescht - was
                            der Anwender im Objektbaum hat, gehoert ihm.
    @date					01.09.2026
     */
    private function CreateVariables()
    {
        $def = $this->Definition();
        if ($def === false) return;

        foreach ($def['groups'] as $g) {
            foreach ($g['variables'] as $v) {
                if (!$this->ReadPropertyBoolean('var_' . $v['ident'])) continue;

                $name = $this->Translate($v['name']);
                $profile = $v['profile'];
                $pos = $v['position'];

                if ($v['type'] == 'boolean') $this->RegisterVariableBoolean($v['ident'], $name, $profile, $pos);
                if ($v['type'] == 'integer') $this->RegisterVariableInteger($v['ident'], $name, $profile, $pos);
                if ($v['type'] == 'float')   $this->RegisterVariableFloat($v['ident'], $name, $profile, $pos);
                if ($v['type'] == 'string')  $this->RegisterVariableString($v['ident'], $name, $profile, $pos);

                /*
                    IPS setzt das Profil nur beim Anlegen der Variablen.
                    Bestandsvariablen wuerden sonst fuer immer am alten
                    Systemprofil haengen.
                 */
                $vid = $this->GetIDForIdent($v['ident']);

                if ($vid !== false && tfa_fix_variable_profile($vid, $profile)) {
                    $this->SendDebug('profil', $v['ident'] . ' auf ' . $profile . ' umgezogen', 0);
                }
            }
        }
    }

    /*
    @author					Back-Blade and helhau
    @brief					Liest eine Variable, falls es sie gibt
    @date					01.09.2026
     */
    private function SafeValue($ident, $ersatz)
    {
        if ($this->GetIDForIdent($ident) === false) return $ersatz;

        return $this->GetValue($ident);
    }

    /*
    @author					Back-Blade and helhau
    @brief					Reicht das Paket an die TFA Cloud weiter
    @date					01.09.2026
     */
    private function SendToCloud($JSONString)
    {
        $dataarray = json_decode($JSONString);
        $id = tfa_from_transport($dataarray->IDENTIFY);
        $sdata = tfa_from_transport($dataarray->SDATA);

        if ($id == 'testcode') return;

        $def = $this->Definition();

        if ($def !== false && $def['options']['windoffset']) {
            $sdata = $this->ApplyWindOffset($sdata);
        }

        $host = $this->ReadPropertyString('var_cloud_Host_Address');
        $url = $this->ReadPropertyString('var_cloud_URL');

        $re = 'PUT http://' . $host . $url . " HTTP/1.1\r\n";
        $re .= 'Host: ' . $host . "\r\n";
        $re .= "Connection: close\r\n";
        $re .= 'HTTP_IDENTIFY:' . $id . "\r\n";
        $re .= "Content-Type: application/octet-stream\r\n";
        $re .= 'Content-Length: ' . strlen($sdata) . "\r\n\r\n";
        $re .= $sdata;

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if ($socket === false) {
            $this->LogMessage('Cloud: socket_create fehlgeschlagen', KL_WARNING);
            return;
        }

        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 3, 'usec' => 0]);
        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 3, 'usec' => 0]);

        $address = gethostbyname($host);

        if (socket_connect($socket, $address, 80) === false) {
            $this->LogMessage('Cloud nicht erreichbar: ' . socket_strerror(socket_last_error($socket)), KL_WARNING);
            socket_close($socket);
            return;
        }

        socket_write($socket, $re, strlen($re));

        if ($this->ReadPropertyBoolean('var_debug_cloud')) {
            $this->SendDebug('cloud', 'gesendet an ' . $host . $url, 0);
        }

        socket_close($socket);
    }

    /*
    @author					Back-Blade and helhau
    @brief					Rechnet den Windoffset in die Rohdaten fuer die Cloud

    @see					Damit die Cloud denselben korrigierten Wert sieht wie
                            das eigene System. Danach muss die Pruefsumme neu
                            gebildet werden.
    @date					01.09.2026
     */
    private function ApplyWindOffset($sdata)
    {
        $offset = $this->ReadPropertyInteger('var_Winddirection_offset');
        $cdata = byteStr2byteArray($sdata);
        $stellen = [15, 19, 23, 27, 31, 35];

        $a = [];

        for ($i = 0; $i < TFA_FRAME_CRC_POS; $i++) {
            if (in_array($i, $stellen)) {
                $hnibl = ((($cdata[$i] >> 4) & 0x0f) + $offset) % 16;
                $lnibl = $cdata[$i] & 0x0f;
                $cdata[$i] = ($hnibl << 4) + $lnibl;
            }

            $a[] = $cdata[$i];
        }

        $a[] = array_sum($a) & 0x7F;

        return bytearray2String($a);
    }

    /*
    @author					Back-Blade and helhau
    @brief					Das mit TFA abgestimmte Logo
    @date					01.09.2026
     */
    private function Logo()
    {
        return file_get_contents(__ROOT__ . '/libs/logo.txt');
    }
}
