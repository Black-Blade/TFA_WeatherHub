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

class TFAGATEWAY  extends IPSModule
{
        private $name="TFAGATEWAY";
      
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
    $this->ForceParent('{8062CF2B-600E-41D6-AD4B-1BA66C32D6ED}');

	$this->RegisterPropertyString("var_firewall_whitelist","");
	$this->RegisterPropertyBoolean("var_firewall",FALSE);

    $this->RegisterPropertyBoolean("debug_debug_gateway",FALSE);
    $this->RegisterPropertyBoolean("var_debug_http_request",FALSE);
    $this->RegisterPropertyBoolean("var_debug_http_response",FALSE);
	$this->RegisterPropertyBoolean("var_debug_sensors",FALSE);
	$this->RegisterPropertyBoolean("var_debug_reset",FALSE);
	$this->RegisterPropertyBoolean("var_debug_cloud",FALSE);
	  
	$this->RegisterPropertyBoolean("var_cloud_aktive",FALSE);
	$this->RegisterPropertyInteger("var_cloud_waitfor_response",1);
	$this->RegisterPropertyString("var_cloud_Host_Address","www.data199.com");
	$this->RegisterPropertyString("var_cloud_URL","/gateway/put");

	
	$this->RegisterPropertyBoolean("var_reset_script",FALSE);

	$this->RegisterPropertyString("var_testcode","");
	$this->RegisterPropertyInteger("var_testcode_time",0);

	$this->RegisterTimer('TFA_testcode_Timer', 0 ,$this->name."_testdata(".$this->InstanceID.");");

}

/*******************************************************************************
@author					ips and Back-Blade and helhau
@brief					überschreibt die intere IPS_ApplyChanges($id) Funktion
@date    				18.03.2020
*******************************************************************************/	
	public function ApplyChanges() 
	{
		parent::ApplyChanges();
		$this->ForceParent('{8062CF2B-600E-41D6-AD4B-1BA66C32D6ED}');

		
		$interval =$this->ReadPropertyInteger("var_testcode_time")*1000;
		$this->SetTimerInterval("TFA_testcode_Timer", $interval);

	}
    
/*******************************************************************************
@author					ips and Back-Blade and helhau
@brief					Daten von  übergeordnete Instanz
@date    				18.03.2020
*******************************************************************************/	

    public function ReceiveData($JSONString)
    {
        // Empfangene Daten vom I/O
		$dataarray = json_decode($JSONString);
	
        $data= $dataarray->Buffer;
        $DataID= $dataarray->DataID;
        $ClientIP= $dataarray->ClientIP;
		$ClientPort= $dataarray->ClientPort;
		$Type=$dataarray->Type;

        if ($DataID !="{7A1272A4-CBDB-46EF-BFC6-DCF4A53D2FC7}") return;
		if ($Type==1)  // neue Verbindung
		{
			if($this->ReadPropertyBoolean("debug_debug_gateway") ==true)
			{
				$this->SendDebug( "gateway","NEW CONNECTION IP :".$ClientIP." PORT :".$ClientPort."\r\n",0);
				
			}
			$this->SetBuffer("DataBuffer".$ClientIP.$ClientPort, "");
			return; 
		}
		if ($Type==2) // Verbing gelöscht
		{
			$this->SetBuffer("DataBuffer".$ClientIP.$ClientPort, "");
			IPS_RunScriptText ($this->name."_closesocket(".$this->InstanceID.",\"".$ClientIP."\",".$ClientPort.");");
			return; 		
		}

		// DATEN AUSWERTUNG
		if($this->ReadPropertyBoolean("debug_debug_gateway") ==true)
			{
				foreach ($this->GetBufferList() as $x )
				{
					$this->SendDebug("gateway", "BUFFERNAME :" .$x."\r\n",0);
				}
			}

		
		$request = $this->GetBuffer("DataBuffer".$ClientIP.$ClientPort).utf8_decode($data);
		
		// HEADER AUSWERTUNG
		if ($this->GetBuffer("DataBuffer".$ClientIP.$ClientPort)=="")

		{
			if($this->ReadPropertyBoolean("debug_debug_gateway") ==true)
			{
				$this->SendDebug( "gateway","UUID        :".$DataID."\r\n",0);
				$this->SendDebug( "gateway","CLIENT IP   :".$ClientIP."\r\n",0);
				$this->SendDebug( "gateway","CLINET PORT :".$ClientPort."\r\n",0);
			}


			// FIREWAHL DARF DER CLIENT DATEN ÜBERMITTELEN
			if($this->ReadPropertyBoolean("var_firewall") ==true)
			{
				if($this->ReadPropertyString("var_firewall_whitelist")=="")
				{
					IPS_RunScriptText ($this->name."_closesocket(".$this->InstanceID.",\"".$ClientIP."\",".$ClientPort.");");
					return;
				}
				$pos_start = strpos($this->ReadPropertyString("var_firewall_whitelist"),$ClientIP);
				if ($pos_start ===false)
				{
					IPS_RunScriptText ($this->name."_closesocket(".$this->InstanceID.",\"".$ClientIP."\",".$ClientPort.");");
					return;
				}
			}

			if($this->ReadPropertyBoolean("var_debug_http_request") ==true)
			{
				$this->SendDebug("http_request","READHEADER :".$request."\r\n",0);
			}
			
			// suche nach HOST
			$pos_start = strpos($request, "Host: www.data199.com");
			if ($pos_start ===false)
			{
				IPS_RunScriptText ($this->name."_closesocket(".$this->InstanceID.",\"".$ClientIP."\",".$ClientPort.");");
				return;
			}
			
			// suche Put
			$pos_start = strpos($request, "PUT http://www.data199.com/gateway/put HTTP/1.1");
			if ($pos_start ===false)
			{
				IPS_RunScriptText ($this->name."_closesocket(".$this->InstanceID.",\"".$ClientIP."\",".$ClientPort.");");
				return;
			}

		}

		//suche mac im HEDER
		$pos_start = strpos($request, "HTTP_IDENTIFY:");
		if ($pos_start ===false)
		{
			IPS_RunScriptText ($this->name."_closesocket(".$this->InstanceID.",\"".$ClientIP."\",".$ClientPort.");");
			return;
		}
		$pos_start1=$pos_start+24;
		$mac =explode(":",substr($request, $pos_start1))[0]; // Suche mac
		
		$pos_start2=$pos_start+14;
		$gatwayid=explode(":",substr($request, $pos_start2))[0]; // Suche id

		if($this->ReadPropertyBoolean("debug_debug_gateway")  ==true)
		{
			$this->SendDebug( "gateway","FOUND MAC :".$mac,0);
			$this->SendDebug( "gateway","FOUND ID  :".$gatwayid,0);
		}

		if($this->ReadPropertyBoolean("var_reset_script")  ==true)	
		{
			$this->make_reset_script($mac,$ClientIP);
		}
		
		
		// suche im Heder "Content-Length"
		$pos_start = strpos($request, "Content-Length:");
		if ($pos_start ===false)
		{
			IPS_RunScriptText ($this->name."_closesocket(".$this->InstanceID.",\"".$ClientIP."\",".$ClientPort.");");
			return;
		}
		$pos_start+=16; //"Content-Length: "  

		// Wiefie Daten sollen übertragen werden;
        $datalensoll =explode("\r",substr($request, $pos_start))[0]; // Suche Datenlänge
		
		$len=(String) $datalensoll; // in String umwandeln
		$slen= strlen($len); // Lange des Strings
		$pos_start = @strpos($request, $len);
		$pos_start +=$slen +4 ;//<cr><lf><cr><lf> // AB wo liegen die Daten
        
        
        // Daten und Datenlänge
        $data       =substr($request, $pos_start);
        $datalen    =strlen($data);

        if (($datalensoll ==$datalen) && ($datalensoll !=""))
		{
			if($this->ReadPropertyBoolean("var_debug_http_request") ==true)
			{
                $this->SendDebug( "http_request",str2hexstr($data)."\r\n",0);
				$this->SendDebug( "http_request","SOLL 		:".$datalensoll."\r\n",0);
				$this->SendDebug( "http_request","IST  		:".$datalen."\r\n",0);
				$this->SendDebug( "http_request","IDENTIFY  :".$gatwayid.":".$mac.":C0"."\r\n",0);
			}

			$this->SetBuffer("DataBuffer".$ClientIP.$ClientPort, $data);
			$this->SetBuffer("DataBufferIDENTIFY".$ClientIP.$ClientPort, $gatwayid.":".$mac.":C0");
			$this->HTTP_Response_OK($ClientIP,$ClientPort);
			
			// Send REST Gateway to Cloud
			if ($datalen==15)  
			{
				IPS_RunScriptText ($this->name."_sendtocloud(".$this->InstanceID.",\"".$ClientIP."\",".$ClientPort.");");
			}
			// Übergabe der Daten an anderen Thrad
			if ($datalen %64 ==0)
			{
				IPS_RunScriptText ($this->name."_splitesensordata(".$this->InstanceID.",\"".$ClientIP."\",".$ClientPort.");");
			}
		}
		//if there is too much data in the instance, delete the instance
        else if ($datalensoll <$datalen) 
        {
			$this->SetBuffer("DataBuffer".$ClientIP.$ClientPort, "");
			$this->SetBuffer("DataBufferIDENTIFY".$ClientIP.$ClientPort,"");		
        }
            //collect data
		else  
		{
			$this->SetBuffer("DataBuffer".$ClientIP.$ClientPort, $request);
		}
	}

/*******************************************************************************
@author					Back-Blade and helhau
@brief					Sends an OK with the current date to the gateway
@return					String of response
@date    				18.03.2020
*******************************************************************************/	
	public function HTTP_Response_OK(String $ClientIP,int $ClientPort)
	{
		$paID= IPS_GetInstance($this->InstanceID);
		
		$connectionID=$paID['ConnectionID'];
		if ($connectionID ==0) return;
	
		$linuxtimestamp = gettimeinbyte();
		$dataresponse = array(0x00,0x00,0x00,0x01,0x00,0x00,0x00,0x00,0x00,0x00,0x00,0x00,0x00,0x00,0x00,0x01,0x17,0x61,0xd4,0x80,0x00,0x00,0x00,0x01);
		$dataresponse[8]=$linuxtimestamp[4];	
		$dataresponse[9]=$linuxtimestamp[3];
		$dataresponse[10]=$linuxtimestamp[2];
		$dataresponse[11]=$linuxtimestamp[1];

		$response ='HTTP/1.1 200 OK'.chr(13).chr(10);
		$response .='Pragma: no-cache'.chr(13).chr(10);
		$response .='Content-Length: 24'.chr(13).chr(10);
	
		$response .='Content-type: application/octet-stream'.chr(13).chr(10);
		$response .='Expires: -1'.chr(13).chr(10);
		$response .='Connection: close'.chr(13).chr(10);
		$response .=chr(13).chr(10);
		
		for ($i=0; $i < count ($dataresponse); $i++)
				{
			$response.=chr($dataresponse[$i]);
		}
	
		$Server['DataID'] = '{C8792760-65CF-4C53-B5C7-A30FCC84FEFE}';
		$Server['Type'] =  0; // DATEN SENDEN
		$Server['Buffer'] =  utf8_encode($response);
		$Server['ClientIP'] = $ClientIP;
        $Server['ClientPort']= $ClientPort;
    	@$ServerJSON = json_encode($Server);
		@$this->SendDataToParent($ServerJSON);

		if($this->ReadPropertyBoolean("var_debug_http_response") ==true)
		{
			$this->SendDebug( "http response", $response."\r\n",0);
			$this->SendDebug( "http response", bytearry2hexstring($dataresponse)."\r\n",0);
		}


		IPS_RunScriptText ($this->name."_closesocket(".$this->InstanceID.",\"".$ClientIP."\",".$ClientPort.");");


		return; 
		
	}
/*******************************************************************************
@author					Back-Blade and helhau
@brief					ZUM Abbau der Verbinung
@return					String of response
@date    				18.03.2020
*******************************************************************************/
	public function closesocket(string $ClientIP,int $ClientPort)
	{
		//IPS_Sleep(100);

		$Server['DataID'] = '{C8792760-65CF-4C53-B5C7-A30FCC84FEFE}';
		$Server['Type'] =  2; // VERBINDUNG TRENNEN
		$Server['ClientIP'] = $ClientIP;
        $Server['ClientPort']= $ClientPort;
        $Server['Buffer'] =  utf8_encode('');
		@$ServerJSON = json_encode($Server);
		@$this->SendDataToParent($ServerJSON);

		if($this->ReadPropertyBoolean("debug_debug_gateway") ==true)
		{
			$this->SendDebug("gateway","CLOSE CONNECTION IP :".$ClientIP." PORT :".$ClientPort."\r\n",0);
		}

	}
/*******************************************************************************
@author					Back-Blade and helhau
@brief					Wertet die Daten vom Senser aus
@return					String of response
@date    				18.03.2020
*******************************************************************************/
	public function splitesensordata(string $ClientIP,int $ClientPort)
	{
		$dataset =10;
		$data = $this->GetBuffer("DataBuffer".$ClientIP.$ClientPort);
		$id = $this->GetBuffer("DataBufferIDENTIFY".$ClientIP.$ClientPort);

		$datalen    =strlen($data);
		$maxdataset =$dataset*64;
		if ($datalen > $maxdataset)
		{
			$this->LogMessage("Buffer overflow > ".(String) ($dataset)." DATASET", KL_WARNING);
			$this->LogMessage("Buffer sets       ".(String) ($datalen/64)." DATASET", KL_WARNING);
			$data 		= substr($data,$datalen-$maxdataset,$maxdataset);
			$datalen     =strlen($data);
		}

		$this->SetBuffer("DataBuffer".$ClientIP.$ClientPort, "");
		$this->SetBuffer("DataBufferIDENTIFY".$ClientIP.$ClientPort,"");		
      
		if (IPS_SemaphoreEnter("SEM".$this->InstanceID, 10000))
			{
				for ($i=0;$i<$datalen;$i++)
				{
					$this->senddatatosensor(substr($data,$i,64),$id);
					$i+=63;
				}			
				
				IPS_SemaphoreLeave("SEM".$this->InstanceID);	
			  		
			}
	}
/*******************************************************************************
@author					Back-Blade and helhau
@brief					Wertet die Daten vom Senser aus
@return					String of response
@date    				18.03.2020
*******************************************************************************/
	private function senddatatosensor(String $data,$id)
	{
		$datalen = strlen($data);
		$sdata= $data;
		$cdata = byteStr2byteArray($data);

		$a = array();
		for($i=0;$i<63;$i++)
		{
			array_push($a,$cdata[$i]);
		}
		
		$bcrc= array_sum ($a)& 0x7F;
		if ($cdata[63] != $bcrc) $sensor_crc = 0;
		else $sensor_crc =1;
		
		$packageheader= substr($data,0,1);
		$timestamp=  ($cdata[1]<<24) + ($cdata[2]<<16) + ($cdata[3]<<8) + ($cdata[4]);
		$packagelength= $cdata[5]-12;

		$deviceid= strtoupper(str2hexstr(substr($data,6,6)));
	 	
		$crc= substr($data,63,1);
		$data= substr($data,12,$packagelength);

		if (!$sensor_crc) return;
	
		if($this->ReadPropertyBoolean("var_debug_sensors")  ==true)
		{
			$this->SendDebug( "sensor", "CRC OK:".$sensor_crc."\r\n",0);
			$this->SendDebug( "sensor", "Package Header :".str2hexstr($packageheader)."\r\n",0);
			$this->SendDebug( "sensor", "Timestamp :".gmdate("Y-m-d\TH:i:s", $timestamp)."\r\n",0);
			$this->SendDebug( "sensor", "Package Length :".$packagelength."\r\n",0);
			$this->SendDebug( "sensor", "Device ID :".$deviceid."\r\n",0);
			$this->SendDebug( "sensor", "DATA :".str2hexstr($data)."\r\n",0);
			$this->SendDebug( "sensor", "CRC :".str2hexstr($crc)."\r\n",0);
			$this->SendDebug( "sensor", "HTTP_IDENTIFY :".$id."\r\n",0);
			$this->SendDebug( "sensor", "SDATA :".str2hexstr($sdata)."\r\n",0);
		
		}
		

		$json = json_encode([
			'DataID'     => '{7E53E668-20E9-7CDB-459C-B22E3B16D24F}',
			'InstanceID' => (int) $this->InstanceID,
			'Data' =>utf8_encode($data),
			'PackageHeader'=>utf8_encode($packageheader),
			'Timestamp'=>utf8_encode($timestamp),
			'PackageLengt'=>utf8_encode($packagelength),
			'DeviceID'=>utf8_encode($deviceid),
			'CRC' =>utf8_encode($crc),
			'IDENTIFY' =>utf8_encode($id),
			'SDATA' =>utf8_encode($sdata),
				
		]);
		$this->SendDataToChildren($json);
		
	
	}

/*******************************************************************************
@author					Back-Blade and helhau
@brief					CREAT a REST Skript 

@pram data				Daten

@date    				13.10.2019

*******************************************************************************/
private function make_reset_script($mac,$ip)
{
	$id="resetscript".$mac.$ip;
	$vokale = array(".", ":", ",");
	$id = str_replace($vokale, "", $id);

	if (@$this->GetIDForIdent($id)===false) 
	{
		if($this->ReadPropertyBoolean("var_debug_reset")  ==true)
		{
			$this->SendDebug("resetscript",$mac.":".$ip." : create rest script",0);
		}
		$data='<?'
.$this->name.'_Send_Rest_to_Gateway('.$this->InstanceID.',"'.$mac.'","'.$ip.'");';
		$this->RegisterScript ($id,"resetscript ->".$mac.":".$ip,$data,0);
	}

}
/*******************************************************************************
@author					Back-Blade and helhau
@brief					Send a RESET pakect to GATEWAY

@pram data				Daten

@date    				13.10.2019

*******************************************************************************/
public function Send_Rest_to_Gateway(String $mac,String $ip)
{
	$this->Send_UDP_PACKET(0x05,$mac,$ip);
}

/*******************************************************************************
@author					Back-Blade and helhau
@brief					Send a UDP pakect to GATEWAY

@pram data				Daten

@date    				13.10.2019

*******************************************************************************/	
	private function Send_UDP_PACKET($cmd,$mac,$ip)
	{

		$data = array(0x00,0x00 ,0x00 ,0x00 ,0x00 ,0x00 ,0x00 ,0x00 ,0x00 ,0x0a);
		$smac = hexstr2byeARRAY($mac);
		$data[0]=($cmd >> 8) & 0xFF;
		$data[1]=($cmd >> 0) & 0xFF;
		$data[2]=$smac[0];
		$data[3]=$smac[1];
		$data[4]=$smac[2];
		$data[5]=$smac[3];
		$data[6]=$smac[4];
		$data[7]=$smac[5];
		
		$response="";
		for ($i=0; $i < count ($data); $i++)
		{
			$response.=chr($data[$i]);
		} 
		if($this->ReadPropertyBoolean("var_debug_reset")  ==true)
		{
			$this->SendDebug( "reset","UDP CMD       :". $cmd,0);
			$this->SendDebug( "reset","UDP MAC       :". $mac,0);
			$this->SendDebug( "reset","UDO IP        :". $ip,0);
			$this->SendDebug( "reset","UDO SEND DATA :".$response,0);
		}
		$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP); 
		socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);
		socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>1, "usec"=>0));	
		socket_sendto($sock, $response, strlen($response), 0, $ip,8003);
		socket_close($sock);
	
	}

	/*******************************************************************************
 @author       Back-Blade
 @brief        
 @date         25.04.2020

 @param[$JSONString]  	JSONstring von Gateway

 @return   
 @version      0.0.1 Doxygen style eingebaut und erstellen dieser File
 @see          
 *******************************************************************************/
	public function sendtocloud(string $ClientIP,int $ClientPort)
	{
		$sdata = $this->GetBuffer("DataBuffer".$ClientIP.$ClientPort);
		$id = $this->GetBuffer("DataBufferIDENTIFY".$ClientIP.$ClientPort);
	
		$this->SetBuffer("DataBuffer".$ClientIP.$ClientPort, "");
		$this->SetBuffer("DataBufferIDENTIFY".$ClientIP.$ClientPort,"");	

		if($this->ReadPropertyBoolean("var_cloud_aktive")  ==false) return;
		
		//socket 		
		$address = gethostbyname($this->ReadPropertyString("var_cloud_Host_Address"));
		if($this->ReadPropertyBoolean("var_debug_cloud")  ==true)
		{
			$this->SendDebug("cloud",$this->ReadPropertyString("var_cloud_Host_Address")." is ".$address,0);
		}
		$re="PUT http://".$this->ReadPropertyString("var_cloud_Host_Address").$this->ReadPropertyString("var_cloud_URL"). " HTTP/1.1\r\n";
		$re=$re."Host: ".$this->ReadPropertyString("var_cloud_Host_Address")."\r\n";
		$re=$re."Connection: close\r\n";
		$re=$re."HTTP_IDENTIFY:".$id."\r\n";
		$re=$re."Content-Type: application/octet-stream\r\n";
		$re=$re."Content-Length: ".strlen($sdata)."\r\n";
		$re=$re."\r\n";
		if($this->ReadPropertyBoolean("var_debug_cloud")  ==true)
		{
			$this->SendDebug("cloud","HTTP RESPONSE :". $re,0);
			$this->SendDebug("cloud","HTTP RESPONSE :". strlen($sdata),0);
			$this->SendDebug("cloud","HTTP RESPONSE :". $sdata,0);
		}
		$re=$re.$sdata;

		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 1, 'usec' => 0));
		
		if ($socket === false) return false;
		$result = socket_connect($socket, $address, 80);
		if ($result === false)
		{
			if($this->ReadPropertyBoolean("var_debug_cloud")  ==true)
			{
					$this->SendDebug( "cloud","HTTP RESPONSE :ERROR SOCKET",0);
			}
			$this->LogMessage("HTTP RESPONSE :ERROR SOCKET", KL_WARNING);
			return false;
		} 
		
		socket_write($socket, $re, strlen($re));
		if($this->ReadPropertyBoolean("var_debug_cloud")  ==true)
		{
			$this->SendDebug( "cloud","HTTP RESPONSE :SEND DATA",0);
		}

		if($this->ReadPropertyBoolean("var_debug_cloud")  ==true)
		{
			$this->SendDebug( "cloud","Wait for REQUEST",0);
		}
		IPS_Sleep(  $this->ReadPropertyInteger("var_cloud_waitfor_response"));

		$out = socket_read($socket, 2048) ;
		socket_close($socket);		

		if (strlen($out)==0) 
		{
			if($this->ReadPropertyBoolean("var_debug_cloud")  ==true)
			{
				$this->SendDebug( "cloud","HTTP REQUEST > NO DATA",0);
			}
			$this->LogMessage("HTTP REQUEST > NO DATA", KL_WARNING);
			return false;
		}

		if($this->ReadPropertyBoolean("var_debug_cloud")  ==true)
		{
			$this->SendDebug("cloud", "HTTP REQUEST :".  bin2hex ($out).":",0);
			$this->SendDebug("cloud","HTTP REQUEST :".  $out.":",0);
		}

		if  (strpos($out,"200 OK")>0)
		{
			if($this->ReadPropertyBoolean("var_debug_cloud")  ==true)
			{
				$this->SendDebug("cloud", "HTTP REQUEST :OK",0);
			}
		}
		else
		{
			if($this->ReadPropertyBoolean("var_debug_cloud")  ==true)
			{
				$this->SendDebug("cloud", "HTTP REQUEST :NOK",0);
		
			}
			$this->LogMessage("HTTP REQUEST :NOK", KL_WARNING);
			return false;
		}
		return true;
	}

	/*******************************************************************************
	@author					ips and Back-Blade and helhau
	@brief					Teststring testen
	@date    				25.08.2021
	*******************************************************************************/	
	public function testdata()
	{
		if($this->ReadPropertyBoolean("var_debug_sensors")  ==true)
			{
				$this->SendDebug("testet", $this->ReadPropertyString("var_testcode"),0);
			}
		$data = hexstr2byeARRAY($this->ReadPropertyString("var_testcode"));
		$data = bytearray2String($data);
		$this->senddatatosensor($data,"testcode");

	}
}
