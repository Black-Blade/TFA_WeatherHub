<?php
/*******************************************************************************
	@file					module.php

	@author					Back-Blade and helhau
	@brief					TFA Konfigurator - findet Sensoren und legt sie an
	@date					01.09.2026

	@see					Haengt als Kind am TFAGATEWAY und bekommt ohne
							Empfangsfilter jedes Sensorpaket. Daraus entsteht
							ein eigenes Geraeteregister, aus dem der Anwender
							die Instanzen anlegen laesst.
*******************************************************************************/

//set base dir
if (!defined('__ROOT__'))  define('__ROOT__', dirname(dirname(__FILE__)));

//load helper functionen
require_once __ROOT__ . '/libs/configurator_help.php';
require_once __ROOT__ . '/libs/frame_tools.php';

class TFACONFIGURATOR extends IPSModule
{
	private $name = "TFACONFIGURATOR";

	const GATEWAY   = '{39306106-5EBB-46E6-420D-063E9E05AB25}';
	const DATA_FROM = '{7E53E668-20E9-7CDB-459C-B22E3B16D24F}';

	const STATUS_OK          = 102;
	const STATUS_NO_GATEWAY  = 104;

/*******************************************************************************
@author					ips and Back-Blade and helhau
@brief					ueberschreibt die interne IPS_Create($id) Funktion
@date					01.09.2026
*******************************************************************************/
	public function Create()
	{
		parent::Create();

		$this->RegisterPropertyBoolean("var_debug", false);
		$this->RegisterAttributeString("var_devices", "{}");
		$this->RegisterAttributeString("var_manual", "");

		/*
			Die Startreihenfolge der Instanzen ist nicht garantiert. Wir
			melden uns beim Kernelstart, statt uns in Create() auf das
			Gateway zu verlassen.
		*/
		$this->RegisterMessage(0, IPS_KERNELSTARTED);

		$this->ConnectParent(self::GATEWAY);
	}

/*******************************************************************************
@author					ips and Back-Blade and helhau
@brief					ueberschreibt die interne IPS_ApplyChanges($id) Funktion
@date					01.09.2026
*******************************************************************************/
	public function ApplyChanges()
	{
		parent::ApplyChanges();

		$this->ConnectParent(self::GATEWAY);

		/*
			Leerer Filter: der Konfigurator will ausdruecklich jedes Paket
			sehen, auch von Sensoren, fuer die es noch keine Instanz gibt.
		*/
		$this->SetReceiveDataFilter("");

		if (IPS_GetKernelRunlevel() != KR_READY) return;

		$this->CheckStatus();
	}

/*******************************************************************************
@author					ips and Back-Blade and helhau
@brief					Nachrichten des Kernels
@date					01.09.2026
*******************************************************************************/
	public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
	{
		if ($Message == IPS_KERNELSTARTED) $this->CheckStatus();
	}

/*******************************************************************************
@author					Back-Blade and helhau
@brief					Meldet fehlende Voraussetzungen ueber den Instanzstatus
@date					01.09.2026
*******************************************************************************/
	private function CheckStatus()
	{
		$parent = IPS_GetInstance($this->InstanceID)["ConnectionID"];

		if ($parent == 0)
		{
			$this->SetStatus(self::STATUS_NO_GATEWAY);
			return;
		}

		$this->SetStatus(self::STATUS_OK);
	}

/*******************************************************************************
@author					ips and Back-Blade and helhau
@brief					Daten von der uebergeordneten Instanz
@date					01.09.2026
*******************************************************************************/
	public function ReceiveData($JSONString)
	{
		$dataarray = json_decode($JSONString);

		if ($dataarray->DataID != self::DATA_FROM) return;

		$deviceid = tfa_from_transport($dataarray->DeviceID);
		$header   = tfa_from_transport($dataarray->PackageHeader);
		$length   = tfa_from_transport($dataarray->PackageLengt);
		$sdata    = tfa_from_transport($dataarray->SDATA);

		$devices = json_decode($this->ReadAttributeString("var_devices"), true);
		if (!is_array($devices)) $devices = array();

		$devices = tfa_device_seen(
			$devices,
			$deviceid,
			ord($header),
			(int) $length,
			str2hexstr($sdata),
			time()
		);

		$this->WriteAttributeString("var_devices", json_encode($devices));

		if ($this->ReadPropertyBoolean("var_debug"))
		{
			$this->SendDebug("configurator", "gesehen: ".$deviceid, 0);
		}
	}

/*******************************************************************************
@author					ips and Back-Blade and helhau
@brief					Baut das Konfigurationsformular
@date					01.09.2026
*******************************************************************************/
	public function GetConfigurationForm()
	{
		$devices = json_decode($this->ReadAttributeString("var_devices"), true);
		if (!is_array($devices)) $devices = array();

		$rows = tfa_configurator_rows($devices, $this->CollectInstances(),
			function ($s) { return $this->Translate($s); });

		$form = array(
			"elements" => array(
				array(
					"type"  => "Image",
					"image" => $this->Logo(),
				),
				array(
					"type"       => "Configurator",
					"name"       => "devices",
					"caption"    => "found sensors",
					"rowCount"   => 20,
					"add"        => false,
					"delete"     => false,
					"sort"       => array("column" => "deviceid", "direction" => "ascending"),
					"columns"    => array(
						array("caption" => "device id", "name" => "deviceid", "width" => "160px"),
						array("caption" => "type",      "name" => "typ",      "width" => "60px"),
						array("caption" => "sensor",    "name" => "name",     "width" => "auto"),
						array("caption" => "article",   "name" => "article",  "width" => "140px"),
						array("caption" => "state",     "name" => "state",    "width" => "180px"),
						array("caption" => "packets",   "name" => "count",    "width" => "80px"),
					),
					"values"     => $rows,
				),
			),
			"actions" => array(
				array(
					"type"    => "PopupButton",
					"caption" => "unknown sensor",
					"popup"   => array(
						"caption" => "unknown sensor",
						"items"   => array(
							array(
								"type"    => "Label",
								"caption" => "Please send us the code below together with the sensor type.",
							),
							array(
								"type"    => "Select",
								"name"    => "pick",
								"caption" => "device id",
								"options" => $this->PickOptions($rows),
							),
							array(
								"type"     => "Button",
								"caption"  => "show code",
								"onClick"  => $this->name."_ShowFrame(\$id, \$pick);",
							),
							array(
								"type"     => "ValidationTextBox",
								"name"     => "framecode",
								"caption"  => "code",
								"multiline"=> true,
							),
						),
					),
				),
				array(
					"type"    => "PopupButton",
					"caption" => "enter bytes manually",
					"popup"   => array(
						"caption" => "enter bytes manually",
						"items"   => array(
							array(
								"type"    => "Label",
								"caption" => "Paste the 64 byte packet a user sent you, as hex. Separators do not matter.",
							),
							array(
								"type"     => "ValidationTextBox",
								"name"     => "manualhex",
								"caption"  => "packet (hex)",
								"multiline"=> true,
							),
							array(
								"type"    => "Button",
								"caption" => "analyse",
								"onClick" => $this->name."_AnalyseFrame(\$id, \$manualhex);",
							),
							array(
								"type"    => "Label",
								"name"    => "manualresult",
								"caption" => "",
							),
						),
					),
				),
				array(
					"type"    => "Button",
					"caption" => "clear list",
					"onClick" => $this->name."_ClearDevices(\$id);",
				),
			),
			"status" => array(
				array(
					"code"    => self::STATUS_OK,
					"icon"    => "active",
					"caption" => "active",
				),
				array(
					"code"    => self::STATUS_NO_GATEWAY,
					"icon"    => "error",
					"caption" => "no gateway connected",
				),
			),
		);

		return json_encode($form);
	}

/*******************************************************************************
@author					Back-Blade and helhau
@brief					Sucht zu jedem Sensormodul die schon angelegten Instanzen

@return					array Geraete-ID => InstanzID

@see					Konfiguratoren duerfen fremde Instanzen lesen, um
						Doppelanlagen zu vermeiden - geaendert wird nichts.
@date					01.09.2026
*******************************************************************************/
	private function CollectInstances()
	{
		$found = array();

		foreach (tfa_sensor_registry() as $def)
		{
			if ($def["guid"] == "") continue;

			foreach (IPS_GetInstanceListByModuleID($def["guid"]) as $id)
			{
				$sensorid = IPS_GetProperty($id, "var_sensor_id");
				if ($sensorid == "") continue;

				$found[strtoupper($def["typ"].$sensorid)] = $id;
			}
		}

		return $found;
	}

/*******************************************************************************
@author					Back-Blade and helhau
@brief					Auswahlliste der unbekannten Geraete
@date					01.09.2026
*******************************************************************************/
	private function PickOptions($rows)
	{
		$options = array();

		foreach ($rows as $r)
		{
			if (array_key_exists("create", $r)) continue;

			$options[] = array(
				"caption" => $r["deviceid"]." - ".$r["name"],
				"value"   => $r["deviceid"],
			);
		}

		if (count($options) == 0)
		{
			$options[] = array("caption" => $this->Translate("no unknown sensor seen"), "value" => "");
		}

		return $options;
	}

/*******************************************************************************
@author					Back-Blade and helhau
@brief					Zeigt den letzten Rohframe eines Geraetes

@param[$deviceid]		Geraete-ID

@date					01.09.2026
*******************************************************************************/
	public function ShowFrame(string $deviceid)
	{
		$devices = json_decode($this->ReadAttributeString("var_devices"), true);
		if (!is_array($devices)) $devices = array();

		$deviceid = strtoupper($deviceid);

		if (!array_key_exists($deviceid, $devices))
		{
			$this->UpdateFormField("framecode", "value", $this->Translate("device not found"));
			return;
		}

		$this->UpdateFormField("framecode", "value", $devices[$deviceid]["frame"]);
	}

/*******************************************************************************
@author					Back-Blade and helhau
@brief					Wertet einen von Hand eingegebenen Hexstring aus

@param[$hex]			Rohpaket als Hexstring

@see					Fuer Pakete, die ein Anwender uns geschickt hat und
						die nie durch unser Gateway gelaufen sind.
@date					01.09.2026
*******************************************************************************/
	public function AnalyseFrame(string $hex)
	{
		$error = "";
		$frame = tfa_hex_to_frame($hex, $error);

		if ($frame == "")
		{
			$this->UpdateFormField("manualresult", "caption", $this->Translate("error").": ".$error);
			return;
		}

		$d = tfa_frame_describe($frame);

		$text  = $this->Translate("device id").": ".$d["deviceid"]."\n";
		$text .= $this->Translate("type").": ".strtoupper($d["typ"])." - ".$this->Translate($d["name"])."\n";
		$text .= "Header: ".sprintf("0x%02X", $d["header"])."\n";
		$text .= $this->Translate("length").": ".$d["length"]."\n";
		$text .= "CRC: ".($d["crc_ok"] ? "ok" : sprintf("%d != %d", $d["crc_gelesen"], $d["crc_erwartet"]))."\n";
		$text .= $this->Translate("time").": ".$d["zeit"]."\n";
		$text .= $this->Translate("payload").": ".$d["payload_hex"];

		if ($d["bekannt"])
		{
			$text .= "\n".$this->Translate("matches the building block").": "
				  .  (($d["passt"]["header"] && $d["passt"]["length"]) ? "ja" : "nein");
		}

		$this->UpdateFormField("manualresult", "caption", $text);

		if ($d["crc_ok"])
		{
			$devices = json_decode($this->ReadAttributeString("var_devices"), true);
			if (!is_array($devices)) $devices = array();

			$devices = tfa_device_seen($devices, $d["deviceid"], $d["header"], $d["length"],
									   strtoupper(bin2hex($frame)), time());

			$this->WriteAttributeString("var_devices", json_encode($devices));
		}
	}

/*******************************************************************************
@author					Back-Blade and helhau
@brief					Leert das Geraeteregister
@date					01.09.2026
*******************************************************************************/
	public function ClearDevices()
	{
		$this->WriteAttributeString("var_devices", "{}");
		$this->ReloadForm();
	}

/*******************************************************************************
@author					Back-Blade and helhau
@brief					Das mit TFA abgestimmte Logo
@date					01.09.2026
*******************************************************************************/
	private function Logo()
	{
		return file_get_contents(__ROOT__ . '/libs/logo.txt');
	}
}
