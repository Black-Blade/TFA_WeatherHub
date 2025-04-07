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

//load helper functionen
require_once __ROOT__ . '/libs/help.php';

class TFASENSORHTTPGATEWAY  extends IPSModule
{
        private $name="TFASENSORHTTPGATEWAY";
      
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
   
	$this->RegisterPropertyString("var_gateway_address","");
	$this->RegisterPropertyInteger("var_counter_ip_time",600);
	$this->RegisterPropertyBoolean("var_debugger_data",FALSE);
	$this->RegisterPropertyBoolean("var_debugger_row",FALSE);
	$this->RegisterTimer('TFA_Gateway_WWW_DATA_Timer', 0 ,$this->name."_readdata(".$this->InstanceID.");");

	$this->RegisterVariableString	("software",$this->Translate("software"),"",0);
	$this->RegisterVariableString	("compilation_date",$this->Translate("compilation date"),"",1);
	$this->RegisterVariableString	("compilation_time",$this->Translate("compilation time"),"",2);
	$this->RegisterVariableString	("serial_no_hex",$this->Translate("serial no. (hex.)"),"",3);
	$this->RegisterVariableString	("serial_no_dec",$this->Translate("serial no. (dec.)"),"",4);
	$this->RegisterVariableString	("name",$this->Translate("name"),"",5);
	$this->RegisterVariableString	("registration_state",$this->Translate("registration state"),"",6);
	$this->RegisterVariableString	("use_dhcp",$this->Translate("use dhcp"),"",7);
	$this->RegisterVariableString	("fixed_ip",$this->Translate("fixed ip"),"",8);
	$this->RegisterVariableString	("fixed_netmask",$this->Translate("fixed netmask"),"",9);
	$this->RegisterVariableString	("fixed_gateway",$this->Translate("fixed gateway"),"",10);
	$this->RegisterVariableString	("fixed_dns_ip",$this->Translate("fixed dns ip"),"",11);
	$this->RegisterVariableString	("dhcp_valid",$this->Translate("dhcp valid"),"",12);
	$this->RegisterVariableString	("dhcp_ip",$this->Translate("dhcp ip"),"",13);
	$this->RegisterVariableString	("dhcp_netmask",$this->Translate("dhcp netmask"),"",14);
	$this->RegisterVariableString	("dhcp_gateway",$this->Translate("dhcp gateway"),"",15);
	$this->RegisterVariableString	("dhcp_dns",$this->Translate("dhcp dns"),"",16);
	$this->RegisterVariableString	("dns_states",$this->Translate("dns states"),"",17);
	$this->RegisterVariableString	("data_server_name",$this->Translate("data server name"),"",18);
	$this->RegisterVariableString	("use_proxy",$this->Translate("use proxy"),"",19);
	$this->RegisterVariableString	("proxy_server_name",$this->Translate("proxy server name"),"",20);
	$this->RegisterVariableInteger	("proxy_port",$this->Translate("proxy port"),"",21);
	$this->RegisterVariableString	("proxy_server_ip",$this->Translate("proxy server ip"),"",22);
	$this->RegisterVariableInteger	("last_contact",$this->Translate("last contact"),"~UnixTimestamp",23);
	$this->RegisterVariableInteger	("rf_channel",$this->Translate("rf channel"),"",24);
	//$this->RegisterVariableInteger	("time",$this->Translate("time"),"~UnixTimestamp",25);
}

/*******************************************************************************
@author					ips and Back-Blade and helhau
@brief					überschreibt die intere IPS_ApplyChanges($id) Funktion
@date    				18.03.2020
*******************************************************************************/	
	public function ApplyChanges() 
	{
		parent::ApplyChanges();

		$interval =$this->ReadPropertyInteger("var_counter_ip_time")*1000;
		$this->SetTimerInterval("TFA_Gateway_WWW_DATA_Timer", $interval);
	
	}

	
/*******************************************************************************
@author					ips and Back-Blade and helhau
@brief					holt sich daten von gateway
@see					fixt curl not work
@date    				16.08.2021
*******************************************************************************/	

private function url_get_contents () {
	$address = gethostbyname($this->ReadPropertyString("var_gateway_address"));
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	if ($socket === false) die ("socket_create() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error()) . "\n");
	$result = socket_connect($socket, $address, 80);
	if ($result === false)  die ("socket_connect() fehlgeschlagen.\nGrund: ($result) " . socket_strerror(socket_last_error($socket)) . "\n");
	$in = "HEAD / HTTP/1.1\r\n";
	$in .= "Host: ".$this->ReadPropertyString("var_gateway_address")."\r\n";
	$in .= "Connection: Close\r\n\r\n";
	$out = '';
	$output ='';
	socket_write($socket, $in, strlen($in));
	while ($out = socket_read($socket, 2048)) {
		$output =$output.$out;
	}
	socket_close($socket);

    return $output;
}
/*******************************************************************************
@author					ips and Back-Blade and helhau
@brief					entfernt alle Http eingenschaften
@date    				18.03.2020
*******************************************************************************/		
private function explode_td($data)
{
	$data=str_replace("<TR>", "", $data);
	$data=str_replace("</TR>", "", $data);
	return explode ("</TD>", $data)[0];
}
/*******************************************************************************
@author					ips and Back-Blade and helhau
@brief					daten in array schreiben
@date    				18.03.2020
*******************************************************************************/	
private function makearray($data)
{
	// http tabel to array
	$stuecke = explode ("<TD>", $data);
	$array= array();
	for ($i=1; $i<count($stuecke);$i=$i+2)
		{
		
			$key = $this->explode_td($stuecke[$i]);
			$key=str_replace(" ", "_", $key);
			$vokale = array(".", "(", ")");
			$key = str_replace($vokale, "", $key);

			$key=strtolower($key);
			$value=$this->explode_td($stuecke[$i+1]);
			if ($key=="last_contact") $value = substr($value,0,-2);
			$array= array_merge($array, array ($key=>$value));
		}
		
	// time to array
	/*$datatime = explode ("<BR><BR>", $data)[2];
	$datatime = explode ("</BODY>", $datatime)[0];
	$datatime = explode (":", $datatime);
	$array= array_merge($array, array (strtolower($datatime[0])=>substr($datatime[1],0,-2)));
	*/	
	return $array;
}

/*******************************************************************************
@author					ips and Back-Blade and helhau
@brief					überschreibt die intere IPS_ApplyChanges($id) Funktion
@date    				18.03.2020
*******************************************************************************/	
	public function readdata()
	{
		$data= $this->url_get_contents();
		if($this->ReadPropertyBoolean("var_debugger_row"))
		{
			$this->SendDebug("gateway row",$data,0);
		}
		$data= $this->makearray($data);
	
		foreach ($data as $ident => $value)
		{
			if ($this->GetIDForIdent($ident)===false) continue;
			if($this->ReadPropertyBoolean("var_debugger_row"))
			{
				$this->SendDebug("gateway data",$ident.":".$value,0);
			}
			$this->SetValue($ident, $value);
    	}
	}		

}

