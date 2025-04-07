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

class TFASENSOR10  extends IPSModule
{
	use help_class;

	private $sensortyp= array(
		"id"=>"10",
		"packageheader"=>0xd3,
		"packagelength"=>11,	
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
		"doorwindows current" => array(
			"_tfa_function"=> "dec_doorwindows",
			"_tfa_pos"=> 2,
			"_tfa_max"=> 2,
			"doorwindows_current" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>4,
				"tfa_data"=>"sensor",
				"name"=>"switch current"
				),
			"doorwindows_incomming_current" => array(
				"typ"=>1,
				"profile"=>"~UnixTimestamp",
				"pos"=>5,
				"tfa_data"=>"time",
				"name"=>"lasttime current"
				)
		
		),
		"doorwindows previous" => array(
			"_tfa_function"=> "dec_doorwindows",
			"_tfa_pos"=> 4,
			"_tfa_max"=> 2,
			"doorwindows_previous" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>6,
				"tfa_data"=>"sensor",
				"name"=>"switch previous"
				),
			"doorwindows_incomming_previous" => array(
				"typ"=>1,
				"profile"=>"~UnixTimestamp",
				"pos"=>7,
				"tfa_data"=>"time",
				"name"=>"lasttime previous"
				)
		
		),
		"doorwindows previous 1" => array(
			"_tfa_function"=> "dec_doorwindows",
			"_tfa_pos"=> 6,
			"_tfa_max"=> 2,
			"doorwindows_previous1" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>8,
				"tfa_data"=>"sensor",
				"name"=>"switch previous 1"
				),
			"doorwindows_incomming_previous1" => array(
				"typ"=>1,
				"profile"=>"~UnixTimestamp",
				"pos"=>9,
				"tfa_data"=>"time",
				"name"=>"lasttime previous 1"
				)
		
		),
		"doorwindows previous 2" => array(
			"_tfa_function"=> "dec_doorwindows",
			"_tfa_pos"=> 8,
			"_tfa_max"=> 2,
			"doorwindows_previous2" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>10,
				"tfa_data"=>"sensor",
				"name"=>"switch previous 2"
				),
			"doorwindows_incomming_previous2" => array(
				"typ"=>1,
				"profile"=>"~UnixTimestamp",
				"pos"=>11,
				"tfa_data"=>"time",
				"name"=>"lasttime previous 2"
				)
		
		),
		"doorwindows previous 3" => array(
			"_tfa_function"=> "dec_doorwindows",
			"_tfa_pos"=> 10,
			"_tfa_max"=> 1,
			"doorwindows_previous3" => array(
				"typ"=>0,
				"profile"=>"",
				"pos"=>12,
				"tfa_data"=>"sensor",
				"name"=>"switch previous 3"
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

