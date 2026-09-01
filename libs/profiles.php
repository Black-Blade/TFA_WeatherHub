<?php

declare(strict_types=1);
/*
    @file					profiles.php

    @author					Back-Blade and helhau
    @brief					Eigene Variablenprofile des Moduls
    @date					01.09.2026

    @see					Eigene Profile immer mit Praefix "TFA.", damit sie
                            nicht mit anderen Modulen kollidieren. System- und
                            Fremdprofile werden nie angefasst.
 */

//set base dir
if (!defined('__ROOT__'))  define('__ROOT__', dirname(dirname(__FILE__)));

/*
    Die Variable haelt "Batterie in Ordnung": wahr = in Ordnung (gruen),
    falsch = schwach (rot). Sie kommt aus dem Dekoderfeld batteryok, das
    Bit 7 des ersten Sensorbytes umgedreht liefert.

    Das System-Profil ~Battery stellt das nicht so dar, wie wir es brauchen
    (gemeldet: "ok" erscheint rot). Deshalb ein eigenes Profil mit
    ausdruecklich gesetzten Texten und Farben.
 */
if (!defined('TFA_PROFILE_BATTERY')) define('TFA_PROFILE_BATTERY', 'TFA.Battery');

if (!defined('TFA_COLOR_OK'))   define('TFA_COLOR_OK', 0x28A745);   // gruen
if (!defined('TFA_COLOR_WARN')) define('TFA_COLOR_WARN', 0xDC3545);   // rot

/*
@author					Back-Blade and helhau
@brief					Die eigenen Messwertprofile

@return					array Profilname => Beschreibung

@see					typ     2 = Float
                        suffix  Einheit hinter dem Wert
                        digits  Nachkommastellen
                        max 0   heisst "keine obere Grenze"
@date					01.09.2026
 */
function tfa_value_profiles()
{
    return [
        'TFA.Temperature' => [
            'typ' => 2, 'icon' => 'Temperature', 'suffix' => ' °C',
            'min' => -50, 'max' => 80, 'digits' => 1,
        ],
        'TFA.Humidity' => [
            'typ' => 2, 'icon' => 'Drops', 'suffix' => ' %',
            'min' => 0, 'max' => 100, 'digits' => 1,
        ],
        'TFA.Rainfall' => [
            'typ' => 2, 'icon' => 'Rainfall', 'suffix' => ' mm',
            'min' => 0, 'max' => 0, 'digits' => 1,
        ],
        'TFA.WindSpeed' => [
            'typ' => 2, 'icon' => 'WindSpeed', 'suffix' => ' m/s',
            'min' => 0, 'max' => 0, 'digits' => 1,
        ],
        'TFA.WindDirection' => [
            'typ' => 2, 'icon' => 'WindDirection', 'suffix' => ' °',
            'min' => 0, 'max' => 360, 'digits' => 1,
        ],
    ];
}

/*
@author					Back-Blade and helhau
@brief					Die sechzehn Himmelsrichtungen

@see					In 22,5-Grad-Schritten. Die Kuerzel sind englisch
                        und werden ueber die locale.json uebersetzt.
@date					01.09.2026
 */
function tfa_wind_directions()
{
    return [
        'N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE',
        'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW',
    ];
}

/*
@author					Back-Blade and helhau
@brief					Legt die eigenen Profile an, falls sie fehlen

@param[$module]			die aufrufende Instanz, fuer Translate und Debug

@return					void

@see					Vorhandene Profile werden nicht ueberschrieben - der
                        Anwender darf Texte und Farben selbst aendern.
@date					01.09.2026
 */
function tfa_create_profiles($module)
{
    if (!IPS_VariableProfileExists(TFA_PROFILE_BATTERY)) {
        IPS_CreateVariableProfile(TFA_PROFILE_BATTERY, VARIABLETYPE_BOOLEAN);
    }

    IPS_SetVariableProfileIcon(TFA_PROFILE_BATTERY, 'Battery');

    /*
        Die Zuordnungen werden bei jedem Aufruf neu gesetzt, nicht nur beim
        Anlegen. Sonst bleibt ein Profil, das ein frueherer Stand mit
        falschen Farben erzeugt hat, fuer immer falsch.

        Der Wert ist eine Zahl, kein Wahrheitswert: die IPS-Schnittstelle
        erwartet an dieser Stelle einen Zahlenwert, und mit
        declare(strict_types=1) ist der Unterschied nicht mehr egal.
     */
    IPS_SetVariableProfileAssociation(
        TFA_PROFILE_BATTERY,
        1,
        $module->Translate('battery ok'),
        '',
        TFA_COLOR_OK
    );
    IPS_SetVariableProfileAssociation(
        TFA_PROFILE_BATTERY,
        0,
        $module->Translate('battery low'),
        '',
        TFA_COLOR_WARN
    );

    /*
        Jedes Profil einzeln absichern. Scheitert eines - etwa an einem
        Symbolnamen, den diese IPS-Fassung nicht kennt -, sollen die
        uebrigen trotzdem angelegt werden und der Aufrufer weiterlaufen.
     */
    foreach (tfa_value_profiles() as $name => $p) {
        try {
            if (!IPS_VariableProfileExists($name)) {
                IPS_CreateVariableProfile($name, $p['typ']);
            }

            IPS_SetVariableProfileText($name, '', $p['suffix']);
            IPS_SetVariableProfileValues($name, $p['min'], $p['max'], 0);
            IPS_SetVariableProfileDigits($name, $p['digits']);
            IPS_SetVariableProfileIcon($name, $p['icon']);
        } catch (Exception $e) {
            $module->LogMessage('Profil ' . $name . ' konnte nicht angelegt werden: ' . $e->getMessage(), KL_WARNING);
        }
    }

    /*
        Windrichtung zusaetzlich als Himmelsrichtung. Ohne Zuordnungen
        stuende dort nur die Gradzahl.
     */
    foreach (tfa_wind_directions() as $i => $kuerzel) {
        IPS_SetVariableProfileAssociation(
            'TFA.WindDirection',
            $i * 22.5,
            $module->Translate($kuerzel),
            '',
            -1
        );
    }
}

/*
@author					Back-Blade and helhau
@brief					Die Systemprofile, die frueher benutzt wurden

@see					Nur diese duerfen beim Umzug ersetzt werden. Alles
                        andere hat der Anwender selbst gewaehlt und bleibt
                        unangetastet.
@date					01.09.2026
 */
function tfa_legacy_profiles()
{
    return ['~Temperature', '~Humidity.F', '~Humidity', '~Rainfall', '~WindSpeed.ms', '~WindDirection.Text', '~Battery'];
}

/*
@author					Back-Blade and helhau
@brief					Zieht eine bestehende Variable auf das eigene Profil um

@param[$variableID]		die Variable
@param[$soll]			gewuenschtes Profil

@return					true wenn umgezogen wurde

@see					IPS setzt das Profil nur beim Anlegen einer Variablen.
                        Bestandsvariablen behalten sonst fuer immer das alte.
                        Umgezogen wird ausschliesslich, wenn dort noch eines
                        unserer frueheren Systemprofile steht.
@date					01.09.2026
 */
function tfa_fix_variable_profile($variableID, $soll)
{
    if ($soll === '' || !IPS_VariableExists($variableID)) return false;

    $v = IPS_GetVariable($variableID);
    $aktuell = $v['VariableCustomProfile'] !== '' ? $v['VariableCustomProfile'] : $v['VariableProfile'];

    if ($aktuell === $soll) return false;
    if (!in_array($aktuell, tfa_legacy_profiles(), true)) return false;

    IPS_SetVariableCustomProfile($variableID, $soll);

    return true;
}
