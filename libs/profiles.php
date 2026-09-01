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
}

