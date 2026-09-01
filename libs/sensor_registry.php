<?php

declare(strict_types=1);
/*
    @file					sensor_registry.php

    @author					Back-Blade and helhau
    @brief					Zentrale Registry aller bekannten TFA-Sensortypen
    @date					01.09.2026

    @see					Einzige Wahrheit fuer die Zuordnung
                            Typ-ID -> Modul / Paketkopf / Paketlaenge.
                            Wird vom Gateway, vom Konfigurator und von den
                            Sensormodulen gemeinsam benutzt.
 */

//set base dir
if (!defined('__ROOT__'))  define('__ROOT__', dirname(dirname(__FILE__)));

//load helper functionen
require_once __ROOT__ . '/libs/help.php';

/*
    Jedes Paket vom Gateway ist exakt 64 Byte lang, andere Laengen werden
    nicht angenommen. Byte 63 traegt die Pruefsumme ueber Byte 0..62.
 */
if (!defined('TFA_FRAME_SIZE'))     define('TFA_FRAME_SIZE', 64);
if (!defined('TFA_FRAME_CRC_POS'))  define('TFA_FRAME_CRC_POS', 63);
if (!defined('TFA_FRAME_ID_POS'))   define('TFA_FRAME_ID_POS', 6);
if (!defined('TFA_FRAME_ID_LEN'))   define('TFA_FRAME_ID_LEN', 6);

/*
    Das generische Sensormodul. Es kann jeden Baustein bedienen und wird
    ueberall dort angelegt, wo ein Baustein keine eigene Modul-GUID nennt -
    also insbesondere bei selbst gebauten Sensoren.
 */
if (!defined('TFA_GENERIC_SENSOR')) define('TFA_GENERIC_SENSOR', '{904322F3-ED27-4EDE-B235-393AFDF1FD5E}');

/*
@author					Back-Blade and helhau
@brief					Liefert die Tabelle aller bekannten Sensortypen

@return					array Typ-ID (klein, 2 Stellen hex) => Beschreibung

@see					"guid"    Modul-GUID, aus der der Konfigurator die
                                  Instanz anlegen laesst. Leer = bekannt, aber
                                  (noch) kein Modul vorhanden.
                        "header"  erwarteter Paketkopf (Byte 0)
                        "length"  erwartete Nutzdatenlaenge (Byte 5 minus 12)
                        "article" TFA Artikelnummer
                        "name"    Klartextname fuer den Konfigurator
                        "state"   getestet | beta | ungetestet | kein modul
@date					01.09.2026
 */
/*
@author					Back-Blade and helhau
@brief					Liste der Ordner, in denen Bausteine liegen

@return					array der Pfade

@see					Mitgelieferte Bausteine liegen unter libs/sensors und werden
                        bei einem Update ersetzt. Selbst gebaute liegen daneben
                        im Kernelverzeichnis und ueberleben das Update. Bei
                        gleicher Typ-ID gewinnt der selbst gebaute.
@date					01.09.2026
 */
function tfa_sensor_paths()
{
    $paths = [__ROOT__ . '/libs/sensors'];

    if (function_exists('IPS_GetKernelDir')) {
        $paths[] = IPS_GetKernelDir() . 'tfa_sensors';
    }
    elseif (array_key_exists('TFA_USER_SENSOR_DIR', $GLOBALS)) {
        $paths[] = $GLOBALS['TFA_USER_SENSOR_DIR'];
    }

    return $paths;
}

/*
@author					Back-Blade and helhau
@brief					Verwirft den Zwischenspeicher der Registry

@see					Noetig, nachdem ein Baustein geschrieben wurde.
@date					01.09.2026
 */
function tfa_sensor_registry_reset()
{
    tfa_sensor_registry(true);
}

function tfa_sensor_registry($reset = false)
{
    static $registry = null;
    if ($reset) $registry = null;
    if ($registry !== null) return $registry;

    $registry = [];
    $files = [];

    foreach (tfa_sensor_paths() as $dir) {
        $found = glob($dir . '/*.json');
        if ($found !== false) $files = array_merge($files, $found);
    }

    foreach ($files as $file) {
        if (substr(basename($file), 0, 1) == '_') continue;   // Vorlagen ueberspringen

        $doc = json_decode(file_get_contents($file), true);
        if (!is_array($doc) || !array_key_exists('typ', $doc)) continue;

        $doc['file'] = basename($file);

        $doc['typ'] = strtolower($doc['typ']);
        $registry[$doc['typ']] = $doc;
    }

    ksort($registry);
    return $registry;
}

/*
@author					Back-Blade and helhau
@brief					Liefert den vollstaendigen Baustein eines Sensortyps

@param[$typ]			Typ-ID, 2 Stellen hex

@return					array mit frame/options/groups oder false

@see					Die Gruppen beschreiben, welche Bytes mit welchem
                        Dekoder ausgewertet und in welche Variablen
                        geschrieben werden.
@date					01.09.2026
 */
function tfa_sensor_definition($typ)
{
    $registry = tfa_sensor_registry();
    $typ = strtolower($typ);

    if (!array_key_exists($typ, $registry)) return false;

    return $registry[$typ];
}

/*
@author					Back-Blade and helhau
@brief					Sucht den Sensortyp zu einer Geraete-ID

@param[$deviceid]		Geraete-ID, 12 Stellen hex (die ersten 2 = Typ)

@return					array der Registry oder false wenn unbekannt

@date					01.09.2026
 */
function tfa_sensor_by_deviceid($deviceid)
{
    $typ = strtolower(substr($deviceid, 0, 2));
    $registry = tfa_sensor_registry();

    if (!array_key_exists($typ, $registry)) return false;
    if ($registry[$typ]['guid'] == '')      return false;

    return $registry[$typ];
}

/*
@author					Back-Blade and helhau
@brief					Berechnet die Pruefsumme ueber Byte 0..62

@param[$frame]			Rohpaket, 64 Byte

@return					int Pruefsumme

@date					01.09.2026
 */
function tfa_frame_crc($frame)
{
    $cdata = byteStr2byteArray($frame);
    $sum = 0;

    for ($i = 0; $i < TFA_FRAME_CRC_POS; $i++) {
        $sum += $cdata[$i];
    }

    return $sum & 0x7F;
}

/*
@author					Back-Blade and helhau
@brief					Prueft Laenge und Pruefsumme eines Rohpaketes

@param[$frame]			Rohpaket

@return					true wenn 64 Byte lang und Pruefsumme stimmt

@see					Abweichende Laengen werden nicht angenommen.
@date					01.09.2026
 */
function tfa_frame_is_valid($frame)
{
    if (strlen($frame) != TFA_FRAME_SIZE) return false;

    $cdata = byteStr2byteArray($frame);

    return $cdata[TFA_FRAME_CRC_POS] == tfa_frame_crc($frame);
}

/*
@author					Back-Blade and helhau
@brief					Liest die Geraete-ID aus einem Rohpaket

@param[$frame]			Rohpaket, 64 Byte

@return					String 12 Stellen hex, gross, oder "" bei Laengenfehler

@date					01.09.2026
 */
function tfa_frame_deviceid($frame)
{
    if (strlen($frame) != TFA_FRAME_SIZE) return '';

    return strtoupper(str2hexstr(substr($frame, TFA_FRAME_ID_POS, TFA_FRAME_ID_LEN)));
}

/*
@author					Back-Blade and helhau
@brief					Zerlegt einen Uebertragungsblock in einzelne Rohpakete

@param[$body]			Nutzdaten einer HTTP-Uebertragung, n * 64 Byte
@param[&$rest]			gibt die Laenge eines unvollstaendigen Restes zurueck

@return					array der 64-Byte-Pakete (unvalidiert)

@see					Das Gateway puffert Pakete, wenn die IP-Verbindung nicht
                        zur Verfuegung steht, und schickt sie
                        spaeter gesammelt per HTTP. Ein Block enthaelt daher
                        beliebig viele 64-Byte-Pakete hintereinander.
                        Ein angebrochener Rest wird nicht angenommen, sondern
                        ueber $rest gemeldet.
@date					01.09.2026
 */
function tfa_frames_split($body, &$rest = 0)
{
    $len = strlen($body);
    $count = intdiv($len, TFA_FRAME_SIZE);
    $rest = $len % TFA_FRAME_SIZE;
    $frames = [];

    for ($i = 0; $i < $count; $i++) {
        $frames[] = substr($body, $i * TFA_FRAME_SIZE, TFA_FRAME_SIZE);
    }

    return $frames;
}

/*
@author					Back-Blade and helhau
@brief					Prueft einen Sensor-Baustein auf Vollstaendigkeit

@param[$doc]			dekodierter Inhalt einer sensors/xx.json
@param[$decoders]		Liste der bekannten Dekodernamen

@return					array der Fehlermeldungen, leer wenn alles stimmt

@see					Damit ein selbst gebauter Baustein nicht stumm
                        fehlschlaegt, sondern im Konfigurator mit Klartext
                        auftaucht.
@date					01.09.2026
 */
function tfa_sensor_validate($doc, $decoders = null)
{
    $errors = [];
    $types = ['boolean', 'integer', 'float', 'string'];
    if ($decoders === null) $decoders = array_keys(tfa_decoders());

    foreach (['typ', 'name', 'frame', 'groups'] as $key) {
        if (!array_key_exists($key, $doc)) $errors[] = 'Pflichtfeld fehlt: ' . $key;
    }
    if (count($errors) > 0) return $errors;

    if (!preg_match('/^[0-9a-f]{2}$/', strtolower($doc['typ']))) {
        $errors[] = 'typ muss genau 2 Stellen hex sein, ist: ' . $doc['typ'];
    }

    $header = $doc['frame']['header'];
    $length = $doc['frame']['length'];

    if (!is_int($header) || $header < 0 || $header > 255) $errors[] = 'frame.header muss 0..255 sein';
    if (!is_int($length) || $length < 0 || $length > 51)  $errors[] = 'frame.length muss 0..51 sein';

    $idents = [];

    foreach ($doc['groups'] as $g) {
        $gname = array_key_exists('name', $g) ? $g['name'] : '(ohne Namen)';

        if (!array_key_exists('decoder', $g) || !in_array($g['decoder'], $decoders)) {
            $errors[] = "Gruppe '" . $gname . "': unbekannter Dekoder '" . (array_key_exists('decoder', $g) ? $g['decoder'] : '') . "'";
            continue;
        }

        if ($g['offset'] + $g['length'] > $length) {
            $errors[] = "Gruppe '" . $gname . "': offset " . $g['offset'] . ' + length ' . $g['length']
                      . ' liegt hinter der Paketlaenge ' . $length;
        }

        foreach ($g['variables'] as $v) {
            if (!in_array($v['type'], $types)) {
                $errors[] = "Variable '" . $v['ident'] . "': type muss boolean|integer|float|string sein";
            }
            if (in_array($v['ident'], $idents)) {
                $errors[] = "Variable '" . $v['ident'] . "': ident kommt mehrfach vor";
            }
            $idents[] = $v['ident'];
        }
    }

    return $errors;
}

/*
@author					Back-Blade and helhau
@brief					Die bekannten Dekoder und wie viele Bytes sie lesen

@return					array Dekodername => Anzahl Bytes

@see					Gemeinsame Quelle fuer Baukasten und Pruefung. Wer
                        hier einen Dekoder ergaenzt, muss ihn auch in
                        tfa_help.php schreiben und in die Auswertung
                        eintragen.
@date					01.09.2026
 */
function tfa_decoders()
{
    /*
        bytes = wie viele Bytes der Dekoder liest
        args  = welche zusaetzlichen Angaben er braucht
                none      nur die Bytes
                time      der Zeitstempel des Paketes
                timechain wie time, gibt aber eine neue Zeit zurueck, die
                          die naechste Gruppe desselben Dekoders weiterreicht
                offset    der eingestellte Offset fuer die Windrichtung
                rain      die zuletzt gespeicherten Regenwerte
     */
    return [
        'dec_sensor_data'           => ['bytes' => 2, 'args' => 'time'],
        'dec_sensor_data_wind'      => ['bytes' => 2, 'args' => 'time'],
        'dec_sensor_data_dir'       => ['bytes' => 2, 'args' => 'offset'],
        'dec_temperature'           => ['bytes' => 2, 'args' => 'none'],
        'dec_humidity'              => ['bytes' => 2, 'args' => 'none'],
        'dec_humidity_decimalplace' => ['bytes' => 2, 'args' => 'none'],
        'dec_airQuality'            => ['bytes' => 2, 'args' => 'none'],
        'dec_wetness'               => ['bytes' => 1, 'args' => 'none'],
        'dec_doorwindows'           => ['bytes' => 1, 'args' => 'timechain'],
        'dec_temperature_pos_rain'  => ['bytes' => 2, 'args' => 'none'],
        'dec_counter_rain'          => ['bytes' => 2, 'args' => 'rain'],
        'dec_event_rain'            => ['bytes' => 2, 'args' => 'timechain'],
    ];
}

/*
@author					Back-Blade and helhau
@brief					Schreibt einen Baustein in den Benutzerordner

@param[$doc]			der Baustein
@param[&$error]			Klartext-Fehlermeldung

@return					Pfad der geschriebenen Datei oder "" im Fehlerfall

@date					01.09.2026
 */
function tfa_sensor_write($doc, &$error = '')
{
    $error = '';
    $paths = tfa_sensor_paths();
    $target = end($paths);

    if (count($paths) < 2) {
        $error = 'kein Ordner fuer eigene Bausteine vorhanden';
        return '';
    }

    if (!is_dir($target) && !mkdir($target, 0777, true)) {
        $error = 'Ordner laesst sich nicht anlegen: ' . $target;
        return '';
    }

    $file = $target . '/' . strtolower($doc['typ']) . '.json';
    $json = json_encode($doc, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    if (file_put_contents($file, $json . "\n") === false) {
        $error = 'Datei laesst sich nicht schreiben: ' . $file;
        return '';
    }

    tfa_sensor_registry_reset();

    return $file;
}
