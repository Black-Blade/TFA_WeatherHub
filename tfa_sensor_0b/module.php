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


class TFASENSOR0b  extends IPSModule
{
	use help_class;
	
	private $sensortyp= array(
		"id"=>"0b",
		"packageheader"=>0xe2,
		"packagelength"=>26,
		"windoffset"=>"",	
	);
	private $cat =array(
		"sensors" => array(
			"_tfa_function"=> "dec_sensor_data_wind",
			"_tfa_pos"=> 0,
			"_tfa_max"=> 3,
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
		"wind current"=>array(
			"_tfa_function"=> "dec_sensor_data_dir",
			"_tfa_pos"=> 3,
			"_tfa_max"=> 4,
			"winddirection" => array(
				"typ"=>2,
				"profile"=>"~WindDirection.Text",
				"pos"=>4,
				"tfa_data"=>"winddirection",
				"name"=>"winddirection"
				),
			"windspeed" => array(
				"typ"=>2,
				"profile"=>"~WindSpeed.ms",
				"pos"=>5,
				"tfa_data"=>"windspeed",
				"name"=>"windspeed"
				),
			"gustspeed" => array(
				"typ"=>2,
				"profile"=>"~WindSpeed.ms",
				"pos"=>6,
				"tfa_data"=>"gustspeed",
				"name"=>"gustspeed"
				),
			"lasttransmit" => array(
				"typ"=>1,
				"profile"=>"~UnixTimestampTime",
				"pos"=>7,
				"tfa_data"=>"lasttransmit",
				"name"=>"lasttransmit"
				),
			),
		"wind previous"=>array(
			"_tfa_function"=> "dec_sensor_data_dir",
			"_tfa_pos"=> 7,
			"_tfa_max"=> 4,
			"winddirection_previous" => array(
				"typ"=>2,
				"profile"=>"~WindDirection.Text",
				"pos"=>8,
				"tfa_data"=>"winddirection",
				"name"=>"winddirection previous"
				),
			"windspeed_previous" => array(
				"typ"=>2,
				"profile"=>"~WindSpeed.ms",
				"pos"=>9,
				"tfa_data"=>"windspeed",
				"name"=>"windspeed"
				),
			"gustspeed_previous" => array(
				"typ"=>2,
				"profile"=>"~WindSpeed.ms",
				"pos"=>10,
				"tfa_data"=>"gustspeed",
				"name"=>"gustspeed previous"
				),
			"lasttransmit_previous" => array(
				"typ"=>1,
				"profile"=>"~UnixTimestampTime",
				"pos"=>11,
				"tfa_data"=>"lasttransmit",
				"name"=>"lasttransmit previous"
				),
			),
			"wind previous 1"=>array(
				"_tfa_function"=> "dec_sensor_data_dir",
				"_tfa_pos"=> 11,
				"_tfa_max"=> 4,
				"winddirection_previous_1" => array(
					"typ"=>2,
					"profile"=>"~WindDirection.Text",
					"pos"=>12,
					"tfa_data"=>"winddirection",
					"name"=>"winddirection"
					),
				"windspeed_previous_1" => array(
					"typ"=>2,
					"profile"=>"~WindSpeed.ms",
					"pos"=>13,
					"tfa_data"=>"windspeed",
					"name"=>"windspeed previous 1"
					),
				"gustspeed_previous_1" => array(
					"typ"=>2,
					"profile"=>"~WindSpeed.ms",
					"pos"=>14,
					"tfa_data"=>"gustspeed",
					"name"=>"gustspeed previous 1"
					),
				"lasttransmit_previous_1" => array(
					"typ"=>1,
					"profile"=>"~UnixTimestampTime",
					"pos"=>15,
					"tfa_data"=>"lasttransmit",
					"name"=>"lasttransmit previous 1"
					),
				),
				"wind previous 2"=>array(
					"_tfa_function"=> "dec_sensor_data_dir",
					"_tfa_pos"=> 15,
					"_tfa_max"=> 4,
					"winddirection_previous_2" => array(
						"typ"=>2,
						"profile"=>"~WindDirection.Text",
						"pos"=>16,
						"tfa_data"=>"winddirection",
						"name"=>"winddirection previous 2"
						),
					"windspeed_previous_2" => array(
						"typ"=>2,
						"profile"=>"~WindSpeed.ms",
						"pos"=>17,
						"tfa_data"=>"windspeed",
						"name"=>"windspeed previous 2"
						),
					"gustspeed_previous_2" => array(
						"typ"=>2,
						"profile"=>"~WindSpeed.ms",
						"pos"=>18,
						"tfa_data"=>"gustspeed",
						"name"=>"gustspeed previous 2"
						),
					"lasttransmit_previous_2" => array(
						"typ"=>1,
						"profile"=>"~UnixTimestampTime",
						"pos"=>19,
						"tfa_data"=>"lasttransmit",
						"name"=>"lasttransmit previous 2"
						),
					),
				"wind previous 3"=>array(
					"_tfa_function"=> "dec_sensor_data_dir",
					"_tfa_pos"=> 19,
					"_tfa_max"=> 4,
					"winddirection_previous_3" => array(
						"typ"=>2,
						"profile"=>"~WindDirection.Text",
						"pos"=>20,
						"tfa_data"=>"winddirection",
						"name"=>"winddirection previous 3"
						),
					"windspeed_previous_3" => array(
						"typ"=>2,
						"profile"=>"~WindSpeed.ms",
						"pos"=>21,
						"tfa_data"=>"windspeed",
						"name"=>"windspeed previous 3"
						),
					"gustspeed_previous_3" => array(
						"typ"=>2,
						"profile"=>"~WindSpeed.ms",
						"pos"=>22,
						"tfa_data"=>"gustspeed",
						"name"=>"gustspeed previous 3"
						),
					"lasttransmit_previous_3" => array(
						"typ"=>1,
						"profile"=>"~UnixTimestampTime",
						"pos"=>23,
						"tfa_data"=>"lasttransmit",
						"name"=>"lasttransmit previous 3"
						),
					),		
				"wind previous 4"=>array(
					"_tfa_function"=> "dec_sensor_data_dir",
					"_tfa_pos"=> 23,
					"_tfa_max"=> 3,
					"winddirection_previous_4" => array(
						"typ"=>2,
						"profile"=>"~WindDirection.Text",
						"pos"=>24,
						"tfa_data"=>"winddirection",
						"name"=>"winddirection previous 4"
						),
					"windspeed_previous_4" => array(
						"typ"=>2,
						"profile"=>"~WindSpeed.ms",
						"pos"=>25,
						"tfa_data"=>"windspeed",
						"name"=>"windspeed previous 4"
						),
					"gustspeed_previous_4" => array(
						"typ"=>2,
						"profile"=>"~WindSpeed.ms",
						"pos"=>26,
						"tfa_data"=>"gustspeed",
						"name"=>"gustspeed previous 4"
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

