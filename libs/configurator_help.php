<?php
/*******************************************************************************
	@file					configurator_help.php

	@author					Back-Blade and helhau
	@brief					Baut die Geraeteliste des Konfigurators
	@date					01.09.2026

	@see					Bewusst frei von IPS-Aufrufen, damit sich die
							Listenbildung ohne laufendes IPS pruefen laesst.
*******************************************************************************/

//set base dir
if (!defined('__ROOT__'))  define('__ROOT__', dirname(dirname(__FILE__)));

//load helper functionen
require_once __ROOT__ . '/libs/sensor_registry.php';

/*******************************************************************************
@author					Back-Blade and helhau
@brief					Nimmt ein gesehenes Geraet in das Register auf

@param[$devices]		bisheriges Register
@param[$deviceid]		Geraete-ID, 12 Stellen hex
@param[$header]			Paketkopf
@param[$length]			Nutzdatenlaenge
@param[$frame_hex]		komplettes Rohpaket als Hexstring
@param[$now]			Zeitstempel

@return					array das erweiterte Register

@see					Ein bereits bekanntes Geraet wird aktualisiert, nicht
						doppelt eingetragen. Der letzte Rohframe wird
						mitgefuehrt, damit er bei unbekannten Sensoren zum
						Einschicken angezeigt werden kann.
@date					01.09.2026
*******************************************************************************/
function tfa_device_seen($devices, $deviceid, $header, $length, $frame_hex, $now)
{
	$deviceid = strtoupper($deviceid);

	if (array_key_exists($deviceid, $devices))
	{
		$devices[$deviceid]["count"]++;
	}
	else
	{
		$devices[$deviceid] = array(
			"deviceid" => $deviceid,
			"first"    => $now,
			"count"    => 1,
		);
	}

	$devices[$deviceid]["header"] = $header;
	$devices[$deviceid]["length"] = $length;
	$devices[$deviceid]["last"]   = $now;
	$devices[$deviceid]["frame"]  = strtoupper($frame_hex);

	return $devices;
}

/*******************************************************************************
@author					Back-Blade and helhau
@brief					Baut die Zeilen fuer das Konfigurator-Formular

@param[$devices]		Register der gesehenen Geraete
@param[$instances]		bereits angelegte Instanzen, Geraete-ID => InstanzID
@param[$translate]		Funktion zum Uebersetzen, oder null

@return					array der Formularzeilen

@see					Bekannter Typ ohne Instanz  -> anlegbar (create)
						Bekannter Typ mit Instanz   -> nur angezeigt
						Unbekannter Typ             -> markiert, mit dem
						letzten Rohcode zum Einschicken, nicht anlegbar.
@date					01.09.2026
*******************************************************************************/
function tfa_configurator_rows($devices, $instances, $translate = null)
{
	$t = function ($s) use ($translate) {
		return $translate === null ? $s : $translate($s);
	};

	$rows = array();

	foreach ($devices as $d)
	{
		$deviceid = strtoupper($d["deviceid"]);
		$typ      = strtolower(substr($deviceid, 0, 2));
		$def      = tfa_sensor_definition($typ);

		$row = array(
			"deviceid" => $deviceid,
			"typ"      => strtoupper($typ),
			"article"  => "",
			"name"     => $t("unknown sensor"),
			"state"    => $t("unknown"),
			"count"    => $d["count"],
			"last"     => $d["last"],
			"frame"    => $d["frame"],
			"instanceID" => 0,
		);

		if ($def !== false)
		{
			$row["article"] = $def["article"];
			$row["name"]    = $t($def["name"]);
			$row["state"]   = $t($def["state"]);

			/*
				Header und Laenge muessen zum hinterlegten Baustein passen.
				Tun sie das nicht, ist die ID zwar bekannt, das Paket aber
				nicht das erwartete - dann lieber als unklar melden, als
				eine falsche Instanz anlegen zu lassen.
			*/
			$passt = ($d["header"] == $def["frame"]["header"])
				  && ($d["length"] == $def["frame"]["length"]);

			if (!$passt)
			{
				$row["state"] = $t("does not match the building block");
			}
			else if ($def["guid"] == "")
			{
				$row["state"] = $t("no module available");
			}
			else if (array_key_exists($deviceid, $instances))
			{
				$row["instanceID"] = $instances[$deviceid];
			}
			else
			{
				/*
					Die Sensormodule erwarten die ID ohne die ersten beiden
					Stellen, also 10 Zeichen.
				*/
				$row["create"] = array(
					"moduleID"      => $def["guid"],
					"configuration" => array("var_sensor_id" => substr($deviceid, 2)),
				);
			}
		}

		$rows[] = $row;
	}

	usort($rows, function ($a, $b) {
		return strcmp($a["deviceid"], $b["deviceid"]);
	});

	return $rows;
}
