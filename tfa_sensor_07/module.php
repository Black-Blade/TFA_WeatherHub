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

class TFASENSOR07  extends IPSModule
{
	use help_class;

	private $sensortyp= array(
		"id"=>"07",
		"packageheader"=>0xda,
		"packagelength"=>18,	
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
		"temperature current in" => array(
			"_tfa_function"=> "dec_temperature",
			"_tfa_pos"=> 2,
			"_tfa_max"=> 2,
			"up_05_current_in" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>4,
				"tfa_data"=>"up05",
				"name"=>"up 05 current in"
				),
			"down_05_current_in" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>5,
				"tfa_data"=>"down05",
				"name"=>"down 05 current in"
				),
			"overflow_current_in" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>6,
				"tfa_data"=>"overflow",
				"name"=>"overflow current in "
				),
			"error_current_in" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>7,
				"tfa_data"=>"error",
				"name"=>"error current in"
				),
			"temperature_current_in" => array(
				"typ"=>2,
				"profile"=>"~Temperature",
				"pos"=>8,
				"tfa_data"=>"temperature",
				"name"=>"temperature current in"
				),
		),
		"humidity current in" => array(
			"_tfa_function"=> "dec_humidity",
			"_tfa_pos"=> 4,
			"_tfa_max"=> 2,
			"humidity_up_05_current_in" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>9,
				"tfa_data"=>"up05",
				"name"=>"humidity up 05 current in"
				),
			"humidity_down_05_current_in" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>10,
				"tfa_data"=>"down05",
				"name"=>"humidity down 05 current in"
				),
			"humidity_id_current_in" => array(
				"typ"=>1,
				"profile"=>"",
				"pos"=>11,
				"tfa_data"=>"id",
				"name"=>"humidity id current in"
				),
			"humidity_calculated_current_in" => array(
				"typ"=>3,
				"profile"=>"",
				"pos"=>12,
				"tfa_data"=>"average",
				"name"=>"humidity calculated current in"
				),
			"humidity_current_in" => array(
				"typ"=>2,
				"profile"=>"~Humidity.F",
				"pos"=>13,
				"tfa_data"=>"humidity",
				"name"=>"humidity current in"
				),
		),
		"temperature current out" => array(
			"_tfa_function"=> "dec_temperature",
			"_tfa_pos"=> 6,
			"_tfa_max"=> 2,
			"up_05_current_out" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>4,
				"tfa_data"=>"up05",
				"name"=>"up 05 curren out"
				),
			"down_05_current_out" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>5,
				"tfa_data"=>"down05",
				"name"=>"down 05 current out"
				),
			"overflow_current_out" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>6,
				"tfa_data"=>"overflow",
				"name"=>"overflow current out"
				),
			"error_current_out" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>7,
				"tfa_data"=>"error",
				"name"=>"error current out"
				),
			"temperature_current_out" => array(
				"typ"=>2,
				"profile"=>"~Temperature",
				"pos"=>8,
				"tfa_data"=>"temperature",
				"name"=>"temperature current out"
				),
		),
		"humidity current out" => array(
			"_tfa_function"=> "dec_humidity",
			"_tfa_pos"=> 8,
			"_tfa_max"=> 2,
			"humidity_up_05_current_out" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>9,
				"tfa_data"=>"up05",
				"name"=>"humidity up 05 current out"
				),
			"humidity_down_05_current_out" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>10,
				"tfa_data"=>"down05",
				"name"=>"humidity down 05 current out"
				),
			"humidity_id_current_out" => array(
				"typ"=>1,
				"profile"=>"",
				"pos"=>11,
				"tfa_data"=>"id",
				"name"=>"humidity id current out"
				),
			"humidity_calculated_current_out" => array(
				"typ"=>3,
				"profile"=>"",
				"pos"=>12,
				"tfa_data"=>"average",
				"name"=>"humidity calculated current out"
				),
			"humidity_current_out" => array(
				"typ"=>2,
				"profile"=>"~Humidity.F",
				"pos"=>13,
				"tfa_data"=>"humidity",
				"name"=>"humidity current out"
				),
		),
		"temperature previous in" => array(
			"_tfa_function"=> "dec_temperature",
			"_tfa_pos"=> 10,
			"_tfa_max"=> 2,
			"up_05_previous_in" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>4,
				"tfa_data"=>"up05",
				"name"=>"up 05 curren in"
				),
			"down_05_previous_in" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>5,
				"tfa_data"=>"down05",
				"name"=>"down 05 previous in"
				),
			"overflow_previou_in" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>6,
				"tfa_data"=>"overflow",
				"name"=>"overflow previous in "
				),
			"error_previous_in" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>7,
				"tfa_data"=>"error",
				"name"=>"error previous in"
				),
			"temperature_previous_in" => array(
				"typ"=>2,
				"profile"=>"~Temperature",
				"pos"=>8,
				"tfa_data"=>"temperature",
				"name"=>"temperature previous in"
				),
		),
		"humidity previous in" => array(
			"_tfa_function"=> "dec_humidity",
			"_tfa_pos"=> 12,
			"_tfa_max"=> 2,
			"humidity_up_05_previous_in" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>9,
				"tfa_data"=>"up05",
				"name"=>"humidity up 05 previous in"
				),
			"humidity_down_05_previous_in" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>10,
				"tfa_data"=>"down05",
				"name"=>"humidity down 05 previous in"
				),
			"humidity_id_previous_in" => array(
				"typ"=>1,
				"profile"=>"",
				"pos"=>11,
				"tfa_data"=>"id",
				"name"=>"humidity id previous in"
				),
			"humidity_calculated_previous_in" => array(
				"typ"=>14,
				"profile"=>"",
				"pos"=>12,
				"tfa_data"=>"average",
				"name"=>"humidity calculated previous in"
				),
			"humidity_previous_in" => array(
				"typ"=>2,
				"profile"=>"~Humidity.F",
				"pos"=>13,
				"tfa_data"=>"humidity",
				"name"=>"humidity previous in"
				),
		),
		"temperature previous out" => array(
			"_tfa_function"=> "dec_temperature",
			"_tfa_pos"=> 14,
			"_tfa_max"=> 2,
			"up_05_previous_out" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>4,
				"tfa_data"=>"up05",
				"name"=>"up 05 curren out"
				),
			"down_05_previous_out" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>5,
				"tfa_data"=>"down05",
				"name"=>"down 05 previous out"
				),
			"overflow_previous_out" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>6,
				"tfa_data"=>"overflow",
				"name"=>"overflow previous out"
				),
			"error_previous_out" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>7,
				"tfa_data"=>"error",
				"name"=>"error previous out"
				),
			"temperature_previous_out" => array(
				"typ"=>2,
				"profile"=>"~Temperature",
				"pos"=>8,
				"tfa_data"=>"temperature",
				"name"=>"temperature previous out"
				),
		),
		"humidity previous out" => array(
			"_tfa_function"=> "dec_humidity",
			"_tfa_pos"=> 16,
			"_tfa_max"=> 2,
			"humidity_up_05_previous_out" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>9,
				"tfa_data"=>"up05",
				"name"=>"humidity up 05 previous out"
				),
			"humidity_down_05_previous_out" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>10,
				"tfa_data"=>"down05",
				"name"=>"humidity down 05 previous out"
				),
			"humidity_id_previous_out" => array(
				"typ"=>1,
				"profile"=>"",
				"pos"=>11,
				"tfa_data"=>"id",
				"name"=>"humidity id previous out"
				),
			"humidity_calculated_previous_out" => array(
				"typ"=>3,
				"profile"=>"",
				"pos"=>12,
				"tfa_data"=>"average",
				"name"=>"humidity calculated previous out"
				),
			"humidity_previous_out" => array(
				"typ"=>2,
				"profile"=>"~Humidity.F",
				"pos"=>13,
				"tfa_data"=>"humidity",
				"name"=>"humidity previous out"
				),
		)
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

