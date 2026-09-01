<?php
/*******************************************************************************
	@file					module.php

	@author					Back-Blade and helhau
	@brief					TFA Mitschnitt - protokolliert alles vom Gateway
	@date					01.09.2026

	@see					Haengt als Kind am TFAGATEWAY und bekommt ohne
							Empfangsfilter jedes Paket. Nuetzlich, wenn ein
							Anwender ein Problem meldet: Mitschnitt
							einschalten, warten, Text herauskopieren.
*******************************************************************************/

//set base dir
if (!defined('__ROOT__'))  define('__ROOT__', dirname(dirname(__FILE__)));

//load helper functionen
require_once __ROOT__ . '/libs/frame_tools.php';

class TFALOGGER extends IPSModule
{
	private $name = "TFALOGGER";

	const GATEWAY   = '{EADE1EC1-66C7-4E18-89FF-8AD98F499DB2}';
	const DATA_FROM = '{99C7FBD6-D83A-4E3C-B395-CB508547A2FC}';

	const STATUS_OK         = 102;
	const STATUS_NO_GATEWAY = 104;

/*******************************************************************************
@author					ips and Back-Blade and helhau
@brief					ueberschreibt die interne IPS_Create($id) Funktion
@date					01.09.2026
*******************************************************************************/
	public function Create()
	{
		parent::Create();

		$this->RegisterPropertyBoolean("var_active",       true);
		$this->RegisterPropertyInteger("var_max",          200);
		$this->RegisterPropertyBoolean("var_only_unknown", false);
		$this->RegisterPropertyBoolean("var_to_syslog",    false);
		$this->RegisterPropertyString("var_filter_id",     "");

		$this->RegisterAttributeString("var_log", "[]");

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
			Leerer Filter: der Mitschnitt soll ausdruecklich alles sehen.
			Auf eine bestimmte ID wird erst beim Eintragen gefiltert, damit
			die Einstellung ohne Neustart der Verbindung wirkt.
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

		$this->SetStatus($parent == 0 ? self::STATUS_NO_GATEWAY : self::STATUS_OK);
	}

/*******************************************************************************
@author					ips and Back-Blade and helhau
@brief					Daten von der uebergeordneten Instanz
@date					01.09.2026
*******************************************************************************/
	public function ReceiveData($JSONString)
	{
		if (!$this->ReadPropertyBoolean("var_active")) return;

		$dataarray = json_decode($JSONString);

		if ($dataarray->DataID != self::DATA_FROM) return;

		$deviceid = tfa_from_transport($dataarray->DeviceID);
		$sdata    = tfa_from_transport($dataarray->SDATA);
		$header   = ord(tfa_from_transport($dataarray->PackageHeader));
		$length   = (int) tfa_from_transport($dataarray->PackageLengt);

		$typ   = strtolower(substr($deviceid, 0, 2));
		$known = tfa_sensor_definition($typ) !== false;

		if ($this->ReadPropertyBoolean("var_only_unknown") && $known) return;

		$wanted = strtoupper(trim($this->ReadPropertyString("var_filter_id")));
		if ($wanted != "" && strtoupper($deviceid) != $wanted) return;

		$entry = array(
			"time"     => date("Y-m-d H:i:s"),
			"deviceid" => strtoupper($deviceid),
			"typ"      => strtoupper($typ),
			"known"    => $known ? $this->Translate("known") : $this->Translate("unknown"),
			"header"   => sprintf("0x%02X", $header),
			"length"   => $length,
			"crc"      => tfa_frame_is_valid($sdata) ? "ok" : $this->Translate("wrong"),
			"frame"    => strtoupper(str2hexstr($sdata)),
		);

		$this->Append($entry);

		if ($this->ReadPropertyBoolean("var_to_syslog"))
		{
			$this->LogMessage(
				"TFA ".$entry["deviceid"]." Header ".$entry["header"]
				." Laenge ".$entry["length"]." CRC ".$entry["crc"],
				KL_NOTIFY
			);
		}

		$this->SendDebug("gateway", $entry["deviceid"]." ".$entry["frame"], 0);
	}

/*******************************************************************************
@author					Back-Blade and helhau
@brief					Haengt einen Eintrag an und kuerzt auf die Hoechstzahl

@param[$entry]			der Eintrag

@see					Der Mitschnitt liegt in einem Attribut, nicht in
						Statusvariablen - er ist kein Messwert.
@date					01.09.2026
*******************************************************************************/
	private function Append($entry)
	{
		$log = json_decode($this->ReadAttributeString("var_log"), true);
		if (!is_array($log)) $log = array();

		array_unshift($log, $entry);

		$max = $this->ReadPropertyInteger("var_max");
		if ($max < 1) $max = 1;

		if (count($log) > $max) $log = array_slice($log, 0, $max);

		$this->WriteAttributeString("var_log", json_encode($log));
	}

/*******************************************************************************
@author					ips and Back-Blade and helhau
@brief					Baut das Konfigurationsformular
@date					01.09.2026
*******************************************************************************/
	public function GetConfigurationForm()
	{
		$log = json_decode($this->ReadAttributeString("var_log"), true);
		if (!is_array($log)) $log = array();

		$form = array(
			"elements" => array(
				array("type" => "Image", "image" => $this->Logo()),

				array("type" => "CheckBox",       "name" => "var_active",       "caption" => "recording active"),
				array("type" => "NumberSpinner",  "name" => "var_max",          "caption" => "keep entries", "minimum" => 1, "maximum" => 5000),
				array("type" => "CheckBox",       "name" => "var_only_unknown", "caption" => "only unknown sensors"),
				array("type" => "ValidationTextBox", "name" => "var_filter_id", "caption" => "only this device id (optional)"),
				array("type" => "CheckBox",       "name" => "var_to_syslog",    "caption" => "also write to the message log"),

				array(
					"type"     => "List",
					"name"     => "log",
					"caption"  => "recording",
					"rowCount" => 20,
					"add"      => false,
					"delete"   => false,
					"columns"  => array(
						array("caption" => "time",      "name" => "time",     "width" => "160px"),
						array("caption" => "device id", "name" => "deviceid", "width" => "150px"),
						array("caption" => "type",      "name" => "typ",      "width" => "60px"),
						array("caption" => "known",     "name" => "known",    "width" => "110px"),
						array("caption" => "header",    "name" => "header",   "width" => "80px"),
						array("caption" => "length",    "name" => "length",   "width" => "80px"),
						array("caption" => "crc",       "name" => "crc",      "width" => "70px"),
						array("caption" => "packet",    "name" => "frame",    "width" => "auto"),
					),
					"values"   => $log,
				),
			),

			"actions" => array(
				array(
					"type"    => "PopupButton",
					"caption" => "copy as text",
					"popup"   => array(
						"caption" => "copy as text",
						"items"   => array(
							array("type" => "Button", "caption" => "create", "onClick" => $this->name."_MakeText(\$id);"),
							array("type" => "ValidationTextBox", "name" => "logtext", "caption" => "recording", "multiline" => true),
						),
					),
				),
				array("type" => "Button", "caption" => "clear recording", "onClick" => $this->name."_ClearLog(\$id);"),
				array("type" => "Label",  "name" => "result", "caption" => ""),
			),

			"status" => array(
				array("code" => self::STATUS_OK,         "icon" => "active", "caption" => "active"),
				array("code" => self::STATUS_NO_GATEWAY, "icon" => "error",  "caption" => "no gateway connected"),
			),
		);

		return json_encode($form);
	}

/*******************************************************************************
@author					Back-Blade and helhau
@brief					Baut den Mitschnitt als Text zum Herauskopieren
@date					01.09.2026
*******************************************************************************/
	public function MakeText()
	{
		$log = json_decode($this->ReadAttributeString("var_log"), true);
		if (!is_array($log)) $log = array();

		$lines = array();

		foreach (array_reverse($log) as $e)
		{
			$lines[] = $e["time"]."  ".$e["deviceid"]."  ".$e["known"]
					 ."  Header ".$e["header"]."  ".$this->Translate("length")." ".$e["length"]
					 ."  CRC ".$e["crc"]."\n    ".$e["frame"];
		}

		$this->UpdateFormField("logtext", "value", implode("\n", $lines));
	}

/*******************************************************************************
@author					Back-Blade and helhau
@brief					Leert den Mitschnitt
@date					01.09.2026
*******************************************************************************/
	public function ClearLog()
	{
		$this->WriteAttributeString("var_log", "[]");
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
