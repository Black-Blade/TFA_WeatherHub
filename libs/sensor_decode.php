<?php
/*******************************************************************************
	@file					sensor_decode.php

	@author					Back-Blade and helhau
	@brief					Wertet ein Paket anhand eines Bausteins aus
	@date					01.09.2026

	@see					Ersetzt die feste if/else-Kette aus v1. Welcher
							Dekoder wie aufgerufen wird, steht in
							tfa_decoders() - eine neue Auswertung braucht
							hier keine Aenderung mehr.
							Bewusst frei von IPS-Aufrufen, damit sich das
							ohne laufendes IPS pruefen laesst.
*******************************************************************************/

//set base dir
if (!defined('__ROOT__'))  define('__ROOT__', dirname(dirname(__FILE__)));

//load helper functionen
require_once __ROOT__ . '/libs/sensor_registry.php';
require_once __ROOT__ . '/libs/tfa_help.php';

/*******************************************************************************
@author					Back-Blade and helhau
@brief					Wertet die Nutzdaten anhand eines Bausteins aus

@param[$def]			der Baustein
@param[$payload]		Nutzdaten des Paketes
@param[$timestamp]		Zeitstempel aus dem Paket
@param[$context]		array mit
						"offset"     Offset fuer die Windrichtung
						"raincounter" zuletzt gespeicherter Regenzaehler
						"rainfall"    zuletzt gespeicherte Regenmenge
						"has"        Funktion(ident) - gibt es die Variable?

@return					array ident => Wert, nur fuer auswertbare Gruppen

@see					Gruppen, deren Dekoder unbekannt ist oder deren
						Voraussetzungen fehlen, werden uebersprungen - der
						Rest des Paketes wird trotzdem ausgewertet.
@date					01.09.2026
*******************************************************************************/
function tfa_decode_payload($def, $payload, $timestamp, $context = array())
{
	$decoders = tfa_decoders();
	$values   = array();

	$offset      = array_key_exists("offset", $context)      ? $context["offset"]      : 0;
	$raincounter = array_key_exists("raincounter", $context) ? $context["raincounter"] : 0;
	$rainfall    = array_key_exists("rainfall", $context)    ? $context["rainfall"]    : 0;
	$has         = array_key_exists("has", $context)         ? $context["has"]         : null;

	/*
		Manche Dekoder reichen eine Zeit an die naechste Gruppe desselben
		Dekoders weiter. Je Dekoder eine eigene Kette, so wie in v1.
	*/
	$chain = array();

	foreach ($def["groups"] as $group)
	{
		$name = $group["decoder"];

		if (!array_key_exists($name, $decoders)) continue;
		if (!function_exists($name))             continue;

		$args  = $decoders[$name]["args"];
		$slice = substr($payload, $group["offset"], $group["length"]);

		if (strlen($slice) < $group["length"]) continue;

		if ($args == "rain")
		{
			/*
				Der Regendekoder rechnet mit den zuletzt gespeicherten
				Werten weiter. Fehlt eine der beiden Variablen, ergibt das
				keinen Sinn - dann lieber die Gruppe auslassen.
			*/
			if ($has !== null && (!$has("raincounter") || !$has("rainfall"))) continue;

			$result = $name($slice, $raincounter, $rainfall);
		}
		else if ($args == "offset")
		{
			$result = $name($slice, $offset);
		}
		else if ($args == "time")
		{
			$result = $name($slice, $timestamp);
		}
		else if ($args == "timechain")
		{
			$vorher = array_key_exists($name, $chain) ? $chain[$name] : $timestamp;
			$result = $name($slice, $vorher);

			if (is_array($result) && array_key_exists("time", $result)) $chain[$name] = $result["time"];
		}
		else
		{
			$result = $name($slice);
		}

		if (!is_array($result)) continue;

		foreach ($group["variables"] as $v)
		{
			if (!array_key_exists($v["field"], $result)) continue;

			$values[$v["ident"]] = $result[$v["field"]];
		}
	}

	return $values;
}
