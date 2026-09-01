<?php

declare(strict_types=1);
/*
    @file					presentations.php

    @author					Back-Blade and helhau
    @brief					Darstellungen der Variablen (ab Symcon 8.0)
    @date					01.09.2026

    @see					https://www.symcon.de/de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/darstellungen/

                            Seit Symcon 8 wird die Anzeige einer Variablen nicht
                            mehr ueber ein zentrales Profil bestimmt, sondern je
                            Variable ueber eine Darstellung. Sie wird als Array
                            an RegisterVariable... uebergeben.

                            Die Bausteine unter libs/sensors nennen die
                            Darstellung mit einem Kurzwort statt mit ihrer GUID,
                            damit sie ohne PHP lesbar bleiben.
 */

//set base dir
if (!defined('__ROOT__'))  define('__ROOT__', dirname(dirname(__FILE__)));

/*
@author					Back-Blade and helhau
@brief					Kurzwort einer Darstellung auf ihre GUID

@return					array Kurzwort => Konstante

@date					01.09.2026
 */
function tfa_presentation_ids()
{
    return [
        'VALUE'       => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
        'ENUMERATION' => VARIABLE_PRESENTATION_ENUMERATION,
        'DATETIME'    => VARIABLE_PRESENTATION_DATE_TIME,
    ];
}

/*
@author					Back-Blade and helhau
@brief					Kurzwort einer Vorlage auf ihre GUID

@return					array Kurzwort => Konstante

@date					01.09.2026
 */
function tfa_presentation_templates()
{
    return [
        'DATE'      => VARIABLE_TEMPLATE_DATE,
        'TIME'      => VARIABLE_TEMPLATE_TIME,
        'DATE_TIME' => VARIABLE_TEMPLATE_DATE_TIME,
    ];
}

/*
@author					Back-Blade and helhau
@brief					Baut aus der Angabe im Baustein die Darstellung

@param[$spec]			der Abschnitt "presentation" eines Bausteins
@param[$module]			die aufrufende Instanz, fuer Translate

@return					array fuer RegisterVariable..., oder "" wenn nichts
                        angegeben ist

@see					Kurzwoerter werden in GUIDs aufgeloest, Beschriftungen
                        uebersetzt und OPTIONS wie von Symcon verlangt als
                        JSON-String uebergeben.
@date					01.09.2026
 */
function tfa_build_presentation($spec, $module)
{
    if (!is_array($spec) || !array_key_exists('PRESENTATION', $spec)) return '';

    $ids = tfa_presentation_ids();

    if (!array_key_exists($spec['PRESENTATION'], $ids)) {
        $module->LogMessage('Unbekannte Darstellung: ' . $spec['PRESENTATION'], KL_WARNING);
        return '';
    }

    $p = $spec;
    $p['PRESENTATION'] = $ids[$spec['PRESENTATION']];

    if (array_key_exists('TEMPLATE', $p)) {
        $vorlagen = tfa_presentation_templates();

        if (array_key_exists($p['TEMPLATE'], $vorlagen)) {
            $p['TEMPLATE'] = $vorlagen[$p['TEMPLATE']];
        } else {
            unset($p['TEMPLATE']);
        }
    }

    if (array_key_exists('OPTIONS', $p) && is_array($p['OPTIONS'])) {
        $optionen = [];

        foreach ($p['OPTIONS'] as $o) {
            if (array_key_exists('Caption', $o)) {
                $o['Caption'] = $module->Translate($o['Caption']);
            }

            $optionen[] = $o;
        }

        $p['OPTIONS'] = json_encode($optionen);
    }

    return $p;
}

/*
@author					Back-Blade and helhau
@brief					Entfernt ein von uns gesetztes Legacy-Profil

@param[$variableID]		die Variable

@return					true wenn etwas entfernt wurde

@see					Eine aeltere Fassung dieses Moduls hat eigene Profile
                        TFA.* als Benutzerprofil gesetzt. Das wuerde die
                        Darstellung ueberdecken. Entfernt werden ausschliesslich
                        unsere eigenen Profile - was der Anwender selbst
                        eingestellt hat, bleibt.
@date					01.09.2026
 */
function tfa_clear_legacy_profile($variableID)
{
    if (!IPS_VariableExists($variableID)) return false;

    $eigene = IPS_GetVariable($variableID)['VariableCustomProfile'];

    if ($eigene === '' || substr($eigene, 0, 4) !== 'TFA.') return false;

    IPS_SetVariableCustomProfile($variableID, '');

    return true;
}
