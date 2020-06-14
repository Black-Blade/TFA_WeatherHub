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


class TFASENSOR08  extends IPSModule
{
	use help_class;
	
	private $sensortyp= array(
		"id"=>"08",
		"packageheader"=>0xe1,
		"packagelength"=>25,	
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
		"rain counter" => array(
			"_tfa_function"=> "dec_counter_rain",
			"_tfa_pos"=> 4,
			"_tfa_max"=> 2,
			"raincounter" => array(
				"typ"=>1,
				"profile"=>"",
				"pos"=>4,
				"tfa_data"=>"counter",
				"name"=>"rain counter"
				),
			"rainfall" => array(
				"typ"=>2,
				"profile"=>"~Rainfall",
				"pos"=>5,
				"tfa_data"=>"rainnew",
				"name"=>"rainfall"
				),
			),
		"temperature" => array(
			"_tfa_function"=> "dec_temperature_pos_rain",
			"_tfa_pos"=> 2,
			"_tfa_max"=> 2,
			"temp" => array(
				"typ"=>2,
				"profile"=>"",
				"pos"=>6,
				"tfa_data"=>"temp",
				"name"=>"temperatur"
				),
			"overflow" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>7,
				"tfa_data"=>"overflow",
				"name"=>"overflow"
				),
			"error" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>8,
				"tfa_data"=>"error",
				"name"=>"error"
				)
			),
		"position" => array(
			"_tfa_function"=> "dec_temperature_pos_rain",
			"_tfa_pos"=> 2,
			"_tfa_max"=> 2,
			"pos" => array(
				"typ"=>3,
				"profile"=>"",
				"pos"=>9,
				"tfa_data"=>"pos",
				"name"=>"position"
				),
			"uint" => array(
				"typ"=>1,
				"profile"=>"",
				"pos"=>10,
				"tfa_data"=>"uint",
				"name"=>"uint"
				),
			),
		"current time" => array(
				"_tfa_function"=> "dec_event_rain",
				"_tfa_pos"=> 6,
				"_tfa_max"=> 2,
				"current_time" => array(
					"typ"=>1,
					"profile"=>"~UnixTimestamp",
					"pos"=>11,
					"tfa_data"=>"time",
					"name"=>"current time"
					),
			),
		"previous time" => array(
				"_tfa_function"=> "dec_event_rain",
				"_tfa_pos"=> 8,
				"_tfa_max"=> 2,
				"previous_time" => array(
					"typ"=>1,
					"profile"=>"~UnixTimestamp",
					"pos"=>12,
					"tfa_data"=>"time",
					"name"=>"previous time"
					),
			),
		"previous time 1" => array(
			"_tfa_function"=> "dec_event_rain",
			"_tfa_pos"=> 10,
			"_tfa_max"=> 2,
			"previous_time1" => array(
				"typ"=>1,
				"profile"=>"~UnixTimestamp",
				"pos"=>13,
				"tfa_data"=>"time",
				"name"=>"previous time 1"
				),
		),
		"previous time 2" => array(
			"_tfa_function"=> "dec_event_rain",
			"_tfa_pos"=> 12,
			"_tfa_max"=> 2,
			"previous_time2" => array(
				"typ"=>1,
				"profile"=>"~UnixTimestamp",
				"pos"=>14,
				"tfa_data"=>"time",
				"name"=>"previous time 2"
				),
			),
		"previous time 3" => array(
			"_tfa_function"=> "dec_event_rain",
			"_tfa_pos"=> 14,
			"_tfa_max"=> 2,
			"previous_time3" => array(
				"typ"=>1,
				"profile"=>"~UnixTimestamp",
				"pos"=>15,
				"tfa_data"=>"time",
				"name"=>"previous time 3"
				),
			),	
		"previous time 4" => array(
			"_tfa_function"=> "dec_event_rain",
			"_tfa_pos"=> 16,
			"_tfa_max"=> 2,
			"previous_time4" => array(
				"typ"=>1,
				"profile"=>"~UnixTimestamp",
				"pos"=>16,
				"tfa_data"=>"time",
				"name"=>"previous time 4"
				),
			),
		"previous time 5" => array(
			"_tfa_function"=> "dec_event_rain",
			"_tfa_pos"=> 18,
			"_tfa_max"=> 2,
			"previous_time5" => array(
				"typ"=>1,
				"profile"=>"~UnixTimestamp",
				"pos"=>17,
				"tfa_data"=>"time",
				"name"=>"previous time 5"
				),
		),
		"previous time 6" => array(
			"_tfa_function"=> "dec_event_rain",
			"_tfa_pos"=> 20,
			"_tfa_max"=> 2,
			"previous_time6" => array(
				"typ"=>1,
				"profile"=>"~UnixTimestamp",
				"pos"=>18,
				"tfa_data"=>"time",
				"name"=>"previous time 6"
				),
			),
		"previous time 7" => array(
			"_tfa_function"=> "dec_event_rain",
			"_tfa_pos"=> 22,
			"_tfa_max"=> 2,
			"previous_time7" => array(
				"typ"=>1,
				"profile"=>"~UnixTimestamp",
				"pos"=>19,
				"tfa_data"=>"time",
				"name"=>"previous time 7"
				),
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

	