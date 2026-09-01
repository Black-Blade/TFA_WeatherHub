<?php

declare(strict_types=1);
/*
    @file					module.php

    @author					Back-Blade and helhau
    @brief					TFA Sensor-Baukasten - baut neue Sensortypen
    @date					01.09.2026

    @see					Erzeugt aus den Angaben im Formular einen
                            Baustein unter sensors/ bzw. im Benutzerordner.
                            Sobald er geschrieben ist, taucht die Sensor-ID
                            im Konfigurator auf.
 */

//set base dir
if (!defined('__ROOT__'))  define('__ROOT__', dirname(dirname(__FILE__)));

//load helper functionen
require_once __ROOT__ . '/libs/sensor_registry.php';
require_once __ROOT__ . '/libs/frame_tools.php';

class TFASENSORBUILDER extends IPSModule
{
    private $name = 'TFASENSORBUILDER';

    /*
    @author					ips and Back-Blade and helhau
    @brief					ueberschreibt die interne IPS_Create($id) Funktion
    @date					01.09.2026
     */
    public function Create()
    {
        parent::Create();

        $this->RegisterPropertyString('var_typ', '');
        $this->RegisterPropertyString('var_name', '');
        $this->RegisterPropertyString('var_article', '');
        $this->RegisterPropertyString('var_state', 'ungetestet');
        $this->RegisterPropertyInteger('var_header', 0);
        $this->RegisterPropertyInteger('var_length', 0);
        $this->RegisterPropertyString('var_guid', '');
        $this->RegisterPropertyBoolean('var_windoffset', false);
        $this->RegisterPropertyString('var_rows', '[]');
    }

    /*
    @author					ips and Back-Blade and helhau
    @brief					ueberschreibt die interne IPS_ApplyChanges($id) Funktion
    @date					01.09.2026
     */
    public function ApplyChanges()
    {
        parent::ApplyChanges();
    }

    /*
    @author					ips and Back-Blade and helhau
    @brief					Baut das Konfigurationsformular
    @date					01.09.2026
     */
    public function GetConfigurationForm()
    {
        $decoders = [];
        foreach (tfa_decoders() as $d => $info) {
            $decoders[] = ['caption' => $d . ' (' . $info['bytes'] . ' Byte)', 'value' => $d];
        }

        $vartypes = [];
        foreach (['boolean', 'integer', 'float', 'string'] as $t) {
            $vartypes[] = ['caption' => $t, 'value' => $t];
        }

        $known = [['caption' => '-', 'value' => '']];
        foreach (tfa_sensor_registry() as $def) {
            $known[] = [
                'caption' => strtoupper($def['typ']) . ' - ' . $def['name'],
                'value'   => $def['typ'],
            ];
        }

        $form = [
            'elements' => [
                ['type' => 'Image', 'image' => $this->Logo()],

                [
                    'type'    => 'ExpansionPanel',
                    'caption' => 'sensor',
                    'items'   => [
                        ['type' => 'ValidationTextBox', 'name' => 'var_typ',     'caption' => 'type id (2 hex digits)'],
                        ['type' => 'ValidationTextBox', 'name' => 'var_name',    'caption' => 'sensor name'],
                        ['type' => 'ValidationTextBox', 'name' => 'var_article', 'caption' => 'article'],
                        ['type'       => 'Select',            'name' => 'var_state',   'caption' => 'state',
                            'options' => [
                                ['caption' => 'ungetestet', 'value' => 'ungetestet'],
                                ['caption' => 'beta',       'value' => 'beta'],
                                ['caption' => 'getestet',   'value' => 'getestet'],
                            ]],
                        ['type' => 'NumberSpinner', 'name' => 'var_header', 'caption' => 'package header (decimal)', 'minimum' => 0, 'maximum' => 255],
                        ['type' => 'NumberSpinner', 'name' => 'var_length', 'caption' => 'payload length',           'minimum' => 0, 'maximum' => 51],
                        ['type' => 'ValidationTextBox', 'name' => 'var_guid', 'caption' => 'module guid (optional)'],
                        ['type' => 'CheckBox', 'name' => 'var_windoffset', 'caption' => 'wind direction offset'],
                    ],
                ],

                [
                    'type'    => 'ExpansionPanel',
                    'caption' => 'variables',
                    'items'   => [
                        [
                            'type'     => 'List',
                            'name'     => 'var_rows',
                            'caption'  => 'variables',
                            'rowCount' => 12,
                            'add'      => true,
                            'delete'   => true,
                            'columns'  => [
                                ['caption' => 'group',    'name' => 'group',    'width' => '130px', 'add' => 'sensors',
                                    'edit' => ['type' => 'ValidationTextBox']],
                                ['caption' => 'decoder',  'name' => 'decoder',  'width' => '230px', 'add' => 'dec_sensor_data',
                                    'edit' => ['type' => 'Select', 'options' => $decoders]],
                                ['caption' => 'offset',   'name' => 'offset',   'width' => '80px',  'add' => 0,
                                    'edit' => ['type' => 'NumberSpinner', 'minimum' => 0, 'maximum' => 50]],
                                ['caption' => 'bytes',    'name' => 'length',   'width' => '80px',  'add' => 2,
                                    'edit' => ['type' => 'NumberSpinner', 'minimum' => 1, 'maximum' => 4]],
                                ['caption' => 'ident',    'name' => 'ident',    'width' => '130px', 'add' => '',
                                    'edit' => ['type' => 'ValidationTextBox']],
                                ['caption' => 'name',     'name' => 'name',     'width' => '150px', 'add' => '',
                                    'edit' => ['type' => 'ValidationTextBox']],
                                ['caption' => 'type',     'name' => 'type',     'width' => '100px', 'add' => 'float',
                                    'edit' => ['type' => 'Select', 'options' => $vartypes]],
                                ['caption' => 'profile',  'name' => 'profile',  'width' => '150px', 'add' => '',
                                    'edit' => ['type' => 'ValidationTextBox']],
                                ['caption' => 'position', 'name' => 'position', 'width' => '80px',  'add' => 0,
                                    'edit' => ['type' => 'NumberSpinner', 'minimum' => 0, 'maximum' => 200]],
                                ['caption' => 'field',    'name' => 'field',    'width' => '130px', 'add' => '',
                                    'edit' => ['type' => 'ValidationTextBox']],
                            ],
                            'values'   => json_decode($this->ReadPropertyString('var_rows'), true),
                        ],
                        ['type' => 'Label', 'caption' => 'One row per variable. Rows with the same group name form one block; decoder, offset and bytes are taken from the first row of that group.'],
                    ],
                ],
            ],

            'actions' => [
                [
                    'type'    => 'PopupButton',
                    'caption' => 'prefill from packet',
                    'popup'   => [
                        'caption' => 'prefill from packet',
                        'items'   => [
                            ['type' => 'Label', 'caption' => 'Paste a 64 byte packet of the new sensor as hex.'],
                            ['type' => 'ValidationTextBox', 'name' => 'prefillhex', 'caption' => 'packet (hex)', 'multiline' => true],
                            ['type' => 'Button', 'caption' => 'read', 'onClick' => $this->name . '_Prefill($id, $prefillhex);'],
                            ['type' => 'Label', 'name' => 'prefillresult', 'caption' => ''],
                        ],
                    ],
                ],
                [
                    'type'    => 'PopupButton',
                    'caption' => 'load existing building block',
                    'popup'   => [
                        'caption' => 'load existing building block',
                        'items'   => [
                            ['type' => 'Select', 'name' => 'loadtyp', 'caption' => 'type', 'options' => $known],
                            ['type' => 'Button', 'caption' => 'load', 'onClick' => $this->name . '_LoadBlock($id, $loadtyp);'],
                        ],
                    ],
                ],
                [
                    'type'    => 'Button',
                    'caption' => 'check',
                    'onClick' => $this->name . '_Check($id, $var_typ, $var_name, $var_article, $var_state, $var_header, $var_length, $var_guid, $var_windoffset, json_encode($var_rows));',
                ],
                [
                    'type'    => 'Button',
                    'caption' => 'save building block',
                    'onClick' => $this->name . '_Save($id, $var_typ, $var_name, $var_article, $var_state, $var_header, $var_length, $var_guid, $var_windoffset, json_encode($var_rows));',
                ],
                ['type' => 'Label', 'name' => 'result', 'caption' => ''],
            ],
        ];

        return json_encode($form);
    }

    /*
    @author					Back-Blade and helhau
    @brief					Prueft die Angaben, ohne zu schreiben
    @date					01.09.2026
     */
    public function Check(
        string $typ,
        string $name,
        string $article,
        string $state,
        int $header,
        int $length,
        string $guid,
        bool $windoffset,
        string $rows
    ) {
        $doc = $this->BuildDoc($typ, $name, $article, $state, $header, $length, $guid, $windoffset, $rows);
        $errors = tfa_sensor_validate($doc);

        if (count($errors) == 0) {
            $this->UpdateFormField(
                'result',
                'caption',
                $this->Translate('building block is valid') . ': '
                . count($doc['groups']) . ' ' . $this->Translate('groups')
            );
            return;
        }

        $this->UpdateFormField(
            'result',
            'caption',
            $this->Translate('errors') . ":\n- " . implode("\n- ", $errors)
        );
    }

    /*
    @author					Back-Blade and helhau
    @brief					Prueft und schreibt den Baustein
    @date					01.09.2026
     */
    public function Save(
        string $typ,
        string $name,
        string $article,
        string $state,
        int $header,
        int $length,
        string $guid,
        bool $windoffset,
        string $rows
    ) {
        $doc = $this->BuildDoc($typ, $name, $article, $state, $header, $length, $guid, $windoffset, $rows);
        $errors = tfa_sensor_validate($doc);

        if (count($errors) > 0) {
            $this->UpdateFormField(
                'result',
                'caption',
                $this->Translate('not saved, please fix first') . ":\n- " . implode("\n- ", $errors)
            );
            return;
        }

        $error = '';
        $file = tfa_sensor_write($doc, $error);

        if ($file == '') {
            $this->UpdateFormField('result', 'caption', $this->Translate('error') . ': ' . $error);
            $this->LogMessage('Baustein ' . $doc['typ'] . ' nicht geschrieben: ' . $error, KL_ERROR);
            return;
        }

        $this->UpdateFormField(
            'result',
            'caption',
            $this->Translate('saved') . ': ' . $file . "\n" .
            $this->Translate('The sensor id now shows up in the configurator.')
        );

        $this->LogMessage('Baustein ' . strtoupper($doc['typ']) . ' geschrieben: ' . $file, KL_NOTIFY);
    }

    /*
    @author					Back-Blade and helhau
    @brief					Belegt Typ, Kopf und Laenge aus einem Rohpaket vor
    @date					01.09.2026
     */
    public function Prefill(string $hex)
    {
        $error = '';
        $frame = tfa_hex_to_frame($hex, $error);

        if ($frame == '') {
            $this->UpdateFormField('prefillresult', 'caption', $this->Translate('error') . ': ' . $error);
            return;
        }

        $d = tfa_frame_describe($frame);

        $this->UpdateFormField('var_typ', 'value', $d['typ']);
        $this->UpdateFormField('var_header', 'value', $d['header']);
        $this->UpdateFormField('var_length', 'value', $d['length']);

        $this->UpdateFormField(
            'prefillresult',
            'caption',
            $this->Translate('device id') . ': ' . $d['deviceid'] . "\n" .
            'CRC: ' . ($d['crc_ok'] ? 'ok' : $this->Translate('wrong')) . "\n" .
            $this->Translate('payload') . ': ' . $d['payload_hex']
        );
    }

    /*
    @author					Back-Blade and helhau
    @brief					Laedt einen vorhandenen Baustein ins Formular

    @see					Damit laesst sich ein aehnlicher Sensor als
                            Ausgangspunkt nehmen.
    @date					01.09.2026
     */
    public function LoadBlock(string $typ)
    {
        $def = tfa_sensor_definition($typ);

        if ($def === false) {
            $this->UpdateFormField('result', 'caption', $this->Translate('building block not found'));
            return;
        }

        $rows = [];

        foreach ($def['groups'] as $g) {
            foreach ($g['variables'] as $v) {
                $rows[] = [
                    'group'    => $g['name'],
                    'decoder'  => $g['decoder'],
                    'offset'   => $g['offset'],
                    'length'   => $g['length'],
                    'ident'    => $v['ident'],
                    'name'     => $v['name'],
                    'type'     => $v['type'],
                    'profile'  => $v['profile'],
                    'position' => $v['position'],
                    'field'    => $v['field'],
                ];
            }
        }

        $this->UpdateFormField('var_typ', 'value', $def['typ']);
        $this->UpdateFormField('var_name', 'value', $def['name']);
        $this->UpdateFormField('var_article', 'value', $def['article']);
        $this->UpdateFormField('var_state', 'value', $def['state']);
        $this->UpdateFormField('var_header', 'value', $def['frame']['header']);
        $this->UpdateFormField('var_length', 'value', $def['frame']['length']);
        $this->UpdateFormField('var_guid', 'value', $def['guid']);
        $this->UpdateFormField('var_windoffset', 'value', $def['options']['windoffset']);
        $this->UpdateFormField('var_rows', 'values', json_encode($rows));

        $this->UpdateFormField(
            'result',
            'caption',
            $this->Translate('loaded') . ': ' . strtoupper($def['typ']) . ' - ' . count($rows) . ' '
            . $this->Translate('variables')
        );
    }

    /*
    @author					Back-Blade and helhau
    @brief					Baut aus den Formularangaben einen Baustein

    @return					array der Baustein

    @see					Zeilen mit gleichem Gruppennamen bilden eine Gruppe.
                            Dekoder, Offset und Byteanzahl kommen aus der ersten
                            Zeile der jeweiligen Gruppe.
    @date					01.09.2026
     */
    private function BuildDoc($typ, $name, $article, $state, $header, $length, $guid, $windoffset, $rows)
    {
        $rows = json_decode($rows, true);
        if (!is_array($rows)) $rows = [];

        $groups = [];

        foreach ($rows as $r) {
            $g = $r['group'];

            if (!array_key_exists($g, $groups)) {
                $groups[$g] = [
                    'name'      => $g,
                    'decoder'   => $r['decoder'],
                    'offset'    => (int) $r['offset'],
                    'length'    => (int) $r['length'],
                    'variables' => [],
                ];
            }

            $groups[$g]['variables'][] = [
                'ident'    => $r['ident'],
                'name'     => $r['name'],
                'type'     => $r['type'],
                'profile'  => $r['profile'],
                'position' => (int) $r['position'],
                'field'    => $r['field'],
            ];
        }

        return [
            'typ'     => strtolower($typ),
            'article' => $article,
            'name'    => $name,
            'state'   => $state,
            'guid'    => $guid,
            'frame'   => ['header' => (int) $header, 'length' => (int) $length],
            'options' => ['windoffset' => (bool) $windoffset],
            'groups'  => array_values($groups),
        ];
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
