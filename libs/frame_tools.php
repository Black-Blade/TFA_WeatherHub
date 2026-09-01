<?php
/*******************************************************************************
	@file					frame_tools.php

	@author					Back-Blade and helhau
	@brief					Manuelle Byte-Eingabe und Analyse eines Rohpaketes
	@date					01.09.2026

	@see					Fuer den Fall, dass ein Anwender uns die Daten
							eines unbekannten Sensors als Hexstring schickt.
							Der String wird eingelesen, geprueft und
							aufgeschluesselt, damit daraus ein Baustein
							unter sensors/ gebaut werden kann.
*******************************************************************************/

//set base dir
if (!defined('__ROOT__'))  define('__ROOT__', dirname(dirname(__FILE__)));

//load helper functionen
require_once __ROOT__ . '/libs/sensor_registry.php';
require_once __ROOT__ . '/libs/tfa_help.php';

/*******************************************************************************
@author					Back-Blade and helhau
@brief					Liest einen von Hand eingegebenen Hexstring ein

@param[$hex]			Hexstring, Trennzeichen egal (Leerzeichen, :, -, 0x)
@param[&$error]			Klartext-Fehlermeldung, "" wenn alles stimmt

@return					String mit 64 Byte oder "" im Fehlerfall

@see					Bewusst tolerant beim Format, aber streng bei der
						Laenge: ein Paket hat exakt 64 Byte.
@date					01.09.2026
*******************************************************************************/
function tfa_hex_to_frame($hex, &$error = "")
{
	$error = "";

	$clean = strtolower($hex);
	$clean = str_replace(array("0x", "\\x"), "", $clean);
	$clean = preg_replace('/[^0-9a-f]/', '', $clean);

	if ($clean === "")
	{
		$error = "keine Hexzeichen gefunden";
		return "";
	}

	if (strlen($clean) % 2 != 0)
	{
		$error = "ungerade Anzahl Hexzeichen (".strlen($clean).")";
		return "";
	}

	$bytes = strlen($clean) / 2;

	if ($bytes != TFA_FRAME_SIZE)
	{
		$error = "Paket hat ".$bytes." Byte, erwartet werden ".TFA_FRAME_SIZE;
		return "";
	}

	return hex2bin($clean);
}

/*******************************************************************************
@author					Back-Blade and helhau
@brief					Schluesselt ein Rohpaket auf

@param[$frame]			Rohpaket, 64 Byte

@return					array mit Kopfdaten, Zuordnung und Nutzdaten

@date					01.09.2026
*******************************************************************************/
function tfa_frame_describe($frame)
{
	$cdata = byteStr2byteArray($frame);

	$header    = $cdata[0];
	$timestamp = ($cdata[1] << 24) + ($cdata[2] << 16) + ($cdata[3] << 8) + $cdata[4];
	$length    = $cdata[5] - 12;
	$deviceid  = tfa_frame_deviceid($frame);
	$typ       = strtolower(substr($deviceid, 0, 2));
	$payload   = substr($frame, 12, $length);

	$known     = tfa_sensor_definition($typ);
	$crc_soll  = tfa_frame_crc($frame);

	$result = array(
		"crc_ok"      => $cdata[TFA_FRAME_CRC_POS] == $crc_soll,
		"crc_gelesen" => $cdata[TFA_FRAME_CRC_POS],
		"crc_erwartet"=> $crc_soll,
		"header"      => $header,
		"timestamp"   => $timestamp,
		"zeit"        => gmdate("Y-m-d H:i:s", $timestamp),
		"length"      => $length,
		"deviceid"    => $deviceid,
		"typ"         => $typ,
		"bekannt"     => $known !== false,
		"name"        => $known !== false ? $known["name"] : "unbekannter Sensortyp",
		"payload_hex" => strtoupper(str2hexstr($payload)),
		"passt"       => array(),
	);

	if ($known !== false)
	{
		$result["passt"]["header"] = ($header == $known["frame"]["header"]);
		$result["passt"]["length"] = ($length == $known["frame"]["length"]);
	}

	return $result;
}

/*******************************************************************************
@author					Back-Blade and helhau
@brief					Probiert alle Dekoder ueber die Nutzdaten

@param[$frame]			Rohpaket, 64 Byte

@return					array Offset => Dekoder => Ergebnisfelder

@see					Hilfsmittel fuer unbekannte Sensoren: zeigt, welcher
						Dekoder an welcher Stelle plausible Werte liefert.
						Ergebnis ist ein Vorschlag, keine Wahrheit.
@date					01.09.2026
*******************************************************************************/
function tfa_frame_probe($frame)
{
	$cdata     = byteStr2byteArray($frame);
	$length    = $cdata[5] - 12;
	$payload   = substr($frame, 12, $length);
	$timestamp = ($cdata[1] << 24) + ($cdata[2] << 16) + ($cdata[3] << 8) + $cdata[4];

	$zwei = array("dec_temperature", "dec_humidity", "dec_humidity_decimalplace", "dec_airQuality");
	$eins = array("dec_wetness");

	$probe = array();

	for ($pos = 0; $pos + 1 < $length; $pos += 2)
	{
		$stueck = substr($payload, $pos, 2);
		$treffer = array();

		foreach ($zwei as $d)
		{
			$werte = $d($stueck);
			$klein = array();
			foreach ($werte as $k => $v)
			{
				if (substr($k, 0, 7) == "unknown") continue;
				$klein[$k] = $v;
			}
			$treffer[$d] = $klein;
		}

		$treffer["dec_sensor_data"] = dec_sensor_data($stueck, $timestamp);
		$treffer["dec_wetness"]     = dec_wetness(substr($payload, $pos, 1));

		$probe[$pos] = $treffer;
	}

	return $probe;
}

/*******************************************************************************
@author					Back-Blade and helhau
@brief					Wandelt einen Transportstring zurueck in rohe Bytes

@param[$s]				String wie ihn das Gateway im JSON liefert

@return					String mit den urspruenglichen Bytes

@see					Das Gateway verpackt die Rohbytes mit utf8_encode,
						damit sie durch json_encode passen. utf8_decode ist
						seit PHP 8.2 als veraltet markiert und wuerde das
						Meldungsprotokoll volllaufen lassen - deshalb hier
						der dokumentierte Ersatz. Byte fuer Byte identisch.
@date					01.09.2026
*******************************************************************************/
function tfa_from_transport($s)
{
	return mb_convert_encoding($s, 'ISO-8859-1', 'UTF-8');
}

/*******************************************************************************
@author					Back-Blade and helhau
@brief					Verpackt rohe Bytes fuer den Transport im JSON

@param[$s]				String mit rohen Bytes

@return					String, json_encode-tauglich

@date					01.09.2026
*******************************************************************************/
function tfa_to_transport($s)
{
	return mb_convert_encoding($s, 'UTF-8', 'ISO-8859-1');
}
