<?php
/*******************************************************************************
	@file					module.php
	
	@author					Back-Blade and helhau 
	@brief					TFA Modul 
	@date    				03.04.2025
	
	@see https://github.com/sarnau/MMMMobileAlerts/blob/master/MobileAlertsGatewayBinaryUpload.markdown
	@see https://github.com/sarnau/MMMMobileAlerts/blob/master/MobileAlertsGatewayWebInterface.markdown
	@see https://github.com/sarnau/MMMMobileAlerts/blob/master/MobileAlertsGatewayUDPInterface.markdown
*******************************************************************************/
  
//set base dir
if (!defined('__ROOT__'))  define('__ROOT__', dirname(dirname(__FILE__)));

//load ips functionen
require_once __ROOT__ . '/libs/help_class.php';

class TFASENSOR11  extends IPSModule
{
	use help_class;

	private $sensortyp= array(
		"id"=>"11",
		"packageheader"=>0xea,
		"packagelength"=>34,	
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
		"temperature current 1" => array(
			"_tfa_function"=> "dec_temperature",
			"_tfa_pos"=> 2,
			"_tfa_max"=> 2,
			"up_05_current_1" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>4,
				"tfa_data"=>"up05",
				"name"=>"up 05 current 1"
				),
			"down_05_current_1" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>5,
				"tfa_data"=>"down05",
				"name"=>"down 05 current 1"
				),
			"overflow_current_1" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>6,
				"tfa_data"=>"overflow",
				"name"=>"overflow current 1"
				),
			"error_current_1" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>7,
				"tfa_data"=>"error",
				"name"=>"error current 1"
				),
			"temperature_current_1" => array(
				"typ"=>2,
				"profile"=>"~Temperature",
				"pos"=>8,
				"tfa_data"=>"temperature",
				"name"=>"temperature current 1"
				),
		),
		"humidity current 1" => array(
			"_tfa_function"=> "dec_humidity",
			"_tfa_pos"=> 4,
			"_tfa_max"=> 2,
			"humidity_up_05_current_1" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>9,
				"tfa_data"=>"up05",
				"name"=>"humidity up 05 current 1"
				),
			"humidity_down_05_current_1" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>10,
				"tfa_data"=>"down05",
				"name"=>"humidity down 05 current 1"
				),
			"humidity_id_current_1" => array(
				"typ"=>1,
				"profile"=>"",
				"pos"=>11,
				"tfa_data"=>"id",
				"name"=>"humidity id current 1"
				),
			"humidity_calculated_current_1" => array(
				"typ"=>3,
				"profile"=>"",
				"pos"=>12,
				"tfa_data"=>"average",
				"name"=>"humidity calculated current 1"
				),
			"humidity_current_1" => array(
				"typ"=>2,
				"profile"=>"~Humidity.F",
				"pos"=>13,
				"tfa_data"=>"humidity",
				"name"=>"humidity current 1"
				),
		),
		"temperature current 2" => array(
			"_tfa_function"=> "dec_temperature",
			"_tfa_pos"=> 6,
			"_tfa_max"=> 2,
			"up_05_current_2" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>4,
				"tfa_data"=>"up05",
				"name"=>"up 05 current 2"
				),
			"down_05_current_2" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>5,
				"tfa_data"=>"down05",
				"name"=>"down 05 current 2"
				),
			"overflow_current_2" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>6,
				"tfa_data"=>"overflow",
				"name"=>"overflow current 2"
				),
			"error_current_2" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>7,
				"tfa_data"=>"error",
				"name"=>"error current 2"
				),
			"temperature_current_2" => array(
				"typ"=>2,
				"profile"=>"~Temperature",
				"pos"=>8,
				"tfa_data"=>"temperature",
				"name"=>"temperature current 2"
				),
		),
		"humidity current 2" => array(
			"_tfa_function"=> "dec_humidity",
			"_tfa_pos"=> 8,
			"_tfa_max"=> 2,
			"humidity_up_05_current_2" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>9,
				"tfa_data"=>"up05",
				"name"=>"humidity up 05 current 2"
				),
			"humidity_down_05_current_2" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>10,
				"tfa_data"=>"down05",
				"name"=>"humidity down 05 current 2"
				),
			"humidity_id_current_2" => array(
				"typ"=>1,
				"profile"=>"",
				"pos"=>11,
				"tfa_data"=>"id",
				"name"=>"humidity id current 2"
				),
			"humidity_calculated_current_2" => array(
				"typ"=>3,
				"profile"=>"",
				"pos"=>12,
				"tfa_data"=>"average",
				"name"=>"humidity calculated current 2"
				),
			"humidity_current_2" => array(
				"typ"=>2,
				"profile"=>"~Humidity.F",
				"pos"=>13,
				"tfa_data"=>"humidity",
				"name"=>"humidity current 2"
				),
		),
		"temperature current 3" => array(
			"_tfa_function"=> "dec_temperature",
			"_tfa_pos"=> 10,
			"_tfa_max"=> 2,
			"up_05_current_3" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>4,
				"tfa_data"=>"up05",
				"name"=>"up 05 current 3"
				),
			"down_05_current_3" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>5,
				"tfa_data"=>"down05",
				"name"=>"down 05 current 3"
				),
			"overflow_current_3" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>6,
				"tfa_data"=>"overflow",
				"name"=>"overflow current 3"
				),
			"error_current_3" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>7,
				"tfa_data"=>"error",
				"name"=>"error current 3"
				),
			"temperature_current_3" => array(
				"typ"=>2,
				"profile"=>"~Temperature",
				"pos"=>8,
				"tfa_data"=>"temperature",
				"name"=>"temperature current 3"
				),
		),
		"humidity current 3" => array(
			"_tfa_function"=> "dec_humidity",
			"_tfa_pos"=> 12,
			"_tfa_max"=> 2,
			"humidity_up_05_current_3" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>9,
				"tfa_data"=>"up05",
				"name"=>"humidity up 05 current 3"
				),
			"humidity_down_05_current_3" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>10,
				"tfa_data"=>"down05",
				"name"=>"humidity down 05 current 3"
				),
			"humidity_id_current_3" => array(
				"typ"=>1,
				"profile"=>"",
				"pos"=>11,
				"tfa_data"=>"id",
				"name"=>"humidity id current 3"
				),
			"humidity_calculated_current_3" => array(
				"typ"=>3,
				"profile"=>"",
				"pos"=>12,
				"tfa_data"=>"average",
				"name"=>"humidity calculated current 3"
				),
			"humidity_current_3" => array(
				"typ"=>2,
				"profile"=>"~Humidity.F",
				"pos"=>13,
				"tfa_data"=>"humidity",
				"name"=>"humidity current 3"
				),
		),
		"temperature current in" => array(
			"_tfa_function"=> "dec_temperature",
			"_tfa_pos"=> 14,
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
				"name"=>"overflow current in"
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
			"_tfa_pos"=> 16,
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
		"temperature previous 1" => array(
			"_tfa_function"=> "dec_temperature",
			"_tfa_pos"=> 18,
			"_tfa_max"=> 2,
			"up_05_previous_1" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>4,
				"tfa_data"=>"up05",
				"name"=>"up 05 curren 1"
				),
			"down_05_previous_1" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>5,
				"tfa_data"=>"down05",
				"name"=>"down 05 previous 1"
				),
			"overflow_previous_1" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>6,
				"tfa_data"=>"overflow",
				"name"=>"overflow previous 1"
				),
			"error_previous_1" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>7,
				"tfa_data"=>"error",
				"name"=>"error previous 1"
				),
			"temperature_previous_1" => array(
				"typ"=>2,
				"profile"=>"~Temperature",
				"pos"=>8,
				"tfa_data"=>"temperature",
				"name"=>"temperature previous 1"
				),
		),
		"humidity previous 1" => array(
			"_tfa_function"=> "dec_humidity",
			"_tfa_pos"=> 20,
			"_tfa_max"=> 2,
			"humidity_up_05_previous_1" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>9,
				"tfa_data"=>"up05",
				"name"=>"humidity up 05 previous 1"
				),
			"humidity_down_05_previous_1" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>10,
				"tfa_data"=>"down05",
				"name"=>"humidity down 05 previous 1"
				),
			"humidity_id_previous_1" => array(
				"typ"=>1,
				"profile"=>"",
				"pos"=>11,
				"tfa_data"=>"id",
				"name"=>"humidity id previous 1"
				),
			"humidity_calculated_previous_1" => array(
				"typ"=>3,
				"profile"=>"",
				"pos"=>12,
				"tfa_data"=>"average",
				"name"=>"humidity calculated previous 1"
				),
			"humidity_previous_1" => array(
				"typ"=>2,
				"profile"=>"~Humidity.F",
				"pos"=>13,
				"tfa_data"=>"humidity",
				"name"=>"humidity previous 1"
				),
		),
		"temperature previous 2" => array(
			"_tfa_function"=> "dec_temperature",
			"_tfa_pos"=> 22,
			"_tfa_max"=> 2,
			"up_05_previous_2" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>4,
				"tfa_data"=>"up05",
				"name"=>"up 05 curren 2"
				),
			"down_05_previous_2" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>5,
				"tfa_data"=>"down05",
				"name"=>"down 05 previous 2"
				),
			"overflow_previous_2" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>6,
				"tfa_data"=>"overflow",
				"name"=>"overflow previous 2"
				),
			"error_previous_2" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>7,
				"tfa_data"=>"error",
				"name"=>"error previous 2"
				),
			"temperature_previous_2" => array(
				"typ"=>2,
				"profile"=>"~Temperature",
				"pos"=>8,
				"tfa_data"=>"temperature",
				"name"=>"temperature previous 2"
				),
		),
		"humidity previous 2" => array(
			"_tfa_function"=> "dec_humidity",
			"_tfa_pos"=> 24,
			"_tfa_max"=> 2,
			"humidity_up_05_previous_2" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>9,
				"tfa_data"=>"up05",
				"name"=>"humidity up 05 previous 2"
				),
			"humidity_down_05_previous_2" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>10,
				"tfa_data"=>"down05",
				"name"=>"humidity down 05 previous 2"
				),
			"humidity_id_previous_2" => array(
				"typ"=>1,
				"profile"=>"",
				"pos"=>11,
				"tfa_data"=>"id",
				"name"=>"humidity id previous 2"
				),
			"humidity_calculated_previous_2" => array(
				"typ"=>3,
				"profile"=>"",
				"pos"=>12,
				"tfa_data"=>"average",
				"name"=>"humidity calculated previous 2"
				),
			"humidity_previous_2" => array(
				"typ"=>2,
				"profile"=>"~Humidity.F",
				"pos"=>13,
				"tfa_data"=>"humidity",
				"name"=>"humidity previous 2"
				),
		),"temperature previous 3" => array(
			"_tfa_function"=> "dec_temperature",
			"_tfa_pos"=> 26,
			"_tfa_max"=> 2,
			"up_05_previous_3" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>4,
				"tfa_data"=>"up05",
				"name"=>"up 05 curren 3"
				),
			"down_05_previous_3" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>5,
				"tfa_data"=>"down05",
				"name"=>"down 05 previous 3"
				),
			"overflow_previous_3" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>6,
				"tfa_data"=>"overflow",
				"name"=>"overflow previous 3"
				),
			"error_previous_3" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>7,
				"tfa_data"=>"error",
				"name"=>"error previous 3"
				),
			"temperature_previous_3" => array(
				"typ"=>2,
				"profile"=>"~Temperature",
				"pos"=>8,
				"tfa_data"=>"temperature",
				"name"=>"temperature previous 3"
				),
		),
		"humidity previous 3" => array(
			"_tfa_function"=> "dec_humidity",
			"_tfa_pos"=> 28,
			"_tfa_max"=> 2,
			"humidity_up_05_previous_3" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>9,
				"tfa_data"=>"up05",
				"name"=>"humidity up 05 previous 3"
				),
			"humidity_down_05_previous_3" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>10,
				"tfa_data"=>"down05",
				"name"=>"humidity down 05 previous 3"
				),
			"humidity_id_previous_3" => array(
				"typ"=>1,
				"profile"=>"",
				"pos"=>11,
				"tfa_data"=>"id",
				"name"=>"humidity id previous 3"
				),
			"humidity_calculated_previous_3" => array(
				"typ"=>3,
				"profile"=>"",
				"pos"=>12,
				"tfa_data"=>"average",
				"name"=>"humidity calculated previous 3"
				),
			"humidity_previous_3" => array(
				"typ"=>2,
				"profile"=>"~Humidity.F",
				"pos"=>13,
				"tfa_data"=>"humidity",
				"name"=>"humidity previous 3"
				),
		),
		"temperature previous in" => array(
			"_tfa_function"=> "dec_temperature",
			"_tfa_pos"=> 30,
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
			"_tfa_pos"=> 32,
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
				"typ"=>3,
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

