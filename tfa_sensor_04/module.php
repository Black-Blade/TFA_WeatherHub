<?php
/*******************************************************************************
	@file					module.php
	
	@author					Back-Blade and helhau 
	@brief					TFA Modul 
	@date    				18.03.2020
	
	@see https://github.com/sarnau/MMMMobileAlerts/blob/master/MobileAlertsGatewayBinaryUpload.markdown
	@see https://github.com/sarnau/MMMMobileAlerts/blob/master/MobileAlertsGatewayWebInterface.markdown
	@see https://github.com/sarnau/MMMMobileAlerts/blob/master/MobileAlertsGatewayUDPInterface.markdown
*******************************************************************************/
  
//set base dir
if (!defined('__ROOT__'))  define('__ROOT__', dirname(dirname(__FILE__)));

//load ips functionen
require_once __ROOT__ . '/libs/help_class.php';


class TFASENSOR04  extends IPSModule
{
	use help_class;
	
	private $sensortyp= array(
		"id"=>"04",
		"packageheader"=>0xd4,
		"packagelength"=>12,	
	);

	private $cat =array(
		"sensors" => array(
			"_tfa_function"=> "dec_sensor_data",
			"_tfa_pos"=> 0,
			"_tfa_max"=> 2,
			"battery" => array(
				"typ"=>0,
				"profile"=>"~Battery",
				"pos"=>0,
				"tfa_data"=>"battery",
				"name"=>"battery"
				),
			"heartbeat" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>1,
				"tfa_data"=>"heartbeat",
				"name"=>"heartbeat"
				),
			"counter" => array(
				"typ"=>1,
				"profile"=>"",
				"pos"=>2,
				"tfa_data"=>"counter",
				"name"=>"counter"
				),
			"update" => array(
				"typ"=>1,
				"profile"=>"~UnixTimestamp",
				"pos"=>3,
				"tfa_data"=>"update",
				"name"=>"update"
			),
		),
		"temperature current" => array(
			"_tfa_function"=> "dec_temperature",
			"_tfa_pos"=> 2,
			"_tfa_max"=> 2,
			"up_05_current" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>4,
				"tfa_data"=>"up05",
				"name"=>"up 05 current"
				),
			"down_05_current" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>5,
				"tfa_data"=>"down05",
				"name"=>"down 05 current"
				),
			"overflow_current" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>6,
				"tfa_data"=>"overflow",
				"name"=>"overflow current"
				),
			"error_current" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>7,
				"tfa_data"=>"error",
				"name"=>"error current"
				),
			"temperature_current" => array(
				"typ"=>2,
				"profile"=>"~Temperature",
				"pos"=>8,
				"tfa_data"=>"temperature",
				"name"=>"temperature current"
				),
		),
		"humidity current" => array(
			"_tfa_function"=> "dec_humidity",
			"_tfa_pos"=> 4,
			"_tfa_max"=> 2,
			"humidity_up_05_current" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>9,
				"tfa_data"=>"up05",
				"name"=>"humidity up 05 current"
				),
			"humidity_down_05_current" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>10,
				"tfa_data"=>"down05",
				"name"=>"humidity down 05 current"
				),
			"humidity_id_current" => array(
				"typ"=>1,
				"profile"=>"",
				"pos"=>11,
				"tfa_data"=>"id",
				"name"=>"humidity id current"
				),
			"humidity_calculated_current" => array(
				"typ"=>3,
				"profile"=>"",
				"pos"=>12,
				"tfa_data"=>"average",
				"name"=>"humidity calculated current"
				),
			"humidity_current" => array(
				"typ"=>2,
				"profile"=>"~Humidity.F",
				"pos"=>13,
				"tfa_data"=>"humidity",
				"name"=>"humidity current"
				),
		),		
		"wetness current" => array(
			"_tfa_function"=> "dec_wetness",
			"_tfa_pos"=> 6,
			"_tfa_max"=> 1,
			"wetness_wet" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>14,
				"tfa_data"=>"wet",
				"name"=>"wet current"
				),
			"wetness_dry" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>15,
				"tfa_data"=>"dry",
				"name"=>"dry current"
				)
		),		
		"temperature previous" => array(
			"_tfa_function"=> "dec_temperature",
			"_tfa_pos"=> 7,
			"_tfa_max"=> 2,
			"up_05_previous" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>16,
				"tfa_data"=>"up05",
				"name"=>"up 05 previous"
				),
			"down_05_previous" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>17,
				"tfa_data"=>"down05",
				"name"=>"down 05 previous"
				),
			"overflow_previous" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>18,
				"tfa_data"=>"overflow",
				"name"=>"overflow previous"
				),
			"error_previous" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>19,
				"tfa_data"=>"error",
				"name"=>"error previous"
				),
			"temperature_previous" => array(
				"typ"=>2,
				"profile"=>"~Temperature",
				"pos"=>20,
				"tfa_data"=>"temperature",
				"name"=>"temperature previous"
				),
		),
		"humidity previous" => array(
			"_tfa_function"=> "dec_humidity",
			"_tfa_pos"=> 9,
			"_tfa_max"=> 2,
			"humidity_up_05_previous" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>21,
				"tfa_data"=>"up05",
				"name"=>"humidity up 05 previous"
				),
			"humidity_down_05_previous" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>22,
				"tfa_data"=>"down05",
				"name"=>"humidity down 05 previous"
				),
			"humidity_id_previous" => array(
				"typ"=>1,
				"profile"=>"",
				"pos"=>23,
				"tfa_data"=>"id",
				"name"=>"humidity id previous"
				),
			"humidity_calculated_previous" => array(
				"typ"=>3,
				"profile"=>"",
				"pos"=>24,
				"tfa_data"=>"average",
				"name"=>"humidity calculated previous"
				),
			"humidity_previous" => array(
				"typ"=>2,
				"profile"=>"~Humidity.F",
				"pos"=>25,
				"tfa_data"=>"humidity",
				"name"=>"humidity previous"
				),
		),
		"wetness previous" => array(
			"_tfa_function"=> "dec_wetness",
			"_tfa_pos"=> 11,
			"_tfa_max"=> 1,
			"wetness_wet_previous" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>26,
				"tfa_data"=>"wet",
				"name"=>"wet previous"
				),
			"wetness_dry_previous" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>27,
				"tfa_data"=>"dry",
				"name"=>"dry previous"
				)
		),		
	);
 
/*******************************************************************************
@author					Back-Blade and helhau
@brief					construct the class
@date    				13.10.2019
*******************************************************************************/	
function __construct($InstanceID) 
{
	parent::__construct($InstanceID);
}

/*******************************************************************************
@author					ips and Back-Blade and helhau
@brief					überschreibt die interne IPS_Create($id) Funktion
@date    				18.03.2020
*******************************************************************************/	
public function Create()
{
    parent::Create();
	$this->MyCreate();
}

/*******************************************************************************
@author					ips and Back-Blade and helhau
@brief					überschreibt die intere IPS_ApplyChanges($id) Funktion
@date    				18.03.2020
*******************************************************************************/	
	public function ApplyChanges() 
	{
		parent::ApplyChanges();
		$this->MyApplyChanges();
	
	}

/*******************************************************************************
@author					ips and Back-Blade and helhau
@brief					Daten von  übergeordnete Instanz
@date    				18.03.2020
*******************************************************************************/	
	public function GetConfigurationForm()
	{
		return $this->MyGetConfigurationForm();
	}

/*******************************************************************************
@author					ips and Back-Blade and helhau
@brief					Daten von  übergeordnete Instanz
@date    				18.03.2020
*******************************************************************************/	

    public function ReceiveData($JSONString)
    {
		$this->MyReceiveData($JSONString);
	}
}

