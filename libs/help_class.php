<?php
/*******************************************************************************
 @author       Back-Blade
 @brief        help_class.php
 @date         25.04.2020

 @file         help_class.php

 @version      0.0.1 Doxygen style eingebaut und erstellen dieser File
 @see          
 *******************************************************************************/
//set base dir

if (!defined('__ROOT__'))  define('__ROOT__', dirname(dirname(__FILE__)));

//load helper functionen
require_once __ROOT__ . '/libs/tfa_help.php';



/*******************************************************************************
 @author       Back-Blade
 @brief        help_class
 @date         27.04.2020

 @version      0.0.1 Doxygen style eingebaut und erstellen dieser File
 @see          
 *******************************************************************************/
trait help_class
{
/*******************************************************************************
 @author       Back-Blade
 @brief        MyCreate
 @date         27.04.2020

 @param[]

 @return   

 @version      0.0.1 Doxygen style eingebaut und erstellen dieser File
 @see          
 *******************************************************************************/
protected function MyCreate()
{
    $this->ConnectParent('{39306106-5EBB-46E6-420D-063E9E05AB25}');
	$this->RegisterPropertySensor();
	foreach ($this->cat as $cat => $data)
	{
		$this->RegisterPropertyData($data);
    }
    
 
}
/*******************************************************************************
 @author       Back-Blade
 @brief        MyApplyChanges
 @date         27.04.2020

 @param[]

 @return 

 @version      0.0.1 Doxygen style eingebaut und erstellen dieser File
 @see          
 *******************************************************************************/
protected function MyApplyChanges()
{
	$this->ConnectParent('{39306106-5EBB-46E6-420D-063E9E05AB25}');
	$this->SetReceiveDataFilter(".*".strtoupper($this->sensortyp["id"].$this->ReadPropertyString ("var_sensor_id")).".*");
	foreach ($this->cat as $cat => $data)
	{
	    $this->CreateVariabel($data);
	}
}  
/*******************************************************************************
 @author       Back-Blade
 @brief        MyGetConfigurationForm
 @date         27.04.2020

 @param     []
 @return    [$str] daten für das Form

 @version      0.0.1 Doxygen style eingebaut und erstellen dieser File
 @see          
 *******************************************************************************/
protected function MyGetConfigurationForm()
{
    $str = $this->makevar_start($this->sensortyp["id"]);
    if (array_key_exists('windoffset',$this->sensortyp)) $str=$str.$this->makevar_windoffset();
    foreach ($this->cat as $cat => $data)
    {
        $str =$str.$this->makevar_data($cat,$data);
    }
    $str =$str.$this->makevar_cloud();
    $str =$str.$this->makevar_end();
    return $str;
}  

/*******************************************************************************
 @author       Back-Blade
 @brief        MyReceiveData
 @date         27.04.2020

 @param[$JSONString]  	JSONstring von Gateway

 @return 

 @version      0.0.1 Doxygen style eingebaut und erstellen dieser File
 @see          
 *******************************************************************************/
protected function MyReceiveData($JSONString)
{
    $adata = $this->Ceckdata($JSONString,$this->sensortyp);
    if ($adata===false) return;
    $timestamp=$adata["timestamp"];
    $timestamp_doorwindows=$timestamp;
    $timestamp_event_rain=$timestamp;  

    foreach ($this->cat as $cat => $datacat)
    {
  
        $tfa_function="";
        $tfa_pos=0;
        $tfa_max=0;
        
        $i=0;
        $tfa_sensor_data=array();

        foreach ($datacat as $ident => $data)
        {
            if ($ident=="_tfa_function")
            {
                $i=$i+1;
                $tfa_function=$data;
                continue;
            }
            if ($ident=="_tfa_pos")
            {
                $tfa_pos=$data;
                $i=$i+1;
                continue;
            }
            if ($ident=="_tfa_max")
            {
                $tfa_max=$data;
                $i=$i+1;
                continue;
            }
            if ($i==3)
            {
                $i=4;
                if ($tfa_function=="dec_sensor_data")                   $tfa_sensor_data =dec_sensor_data(substr($adata["data"],$tfa_pos,$tfa_max),$timestamp);
                else if ($tfa_function=="dec_sensor_data_wind")         $tfa_sensor_data =dec_sensor_data_wind(substr($adata["data"],$tfa_pos,$tfa_max),$timestamp);
                else if ($tfa_function=="dec_sensor_data_dir")          
                {
                    $offset=$this->ReadPropertyInteger("var_Winddirection_offset");
                    $tfa_sensor_data =dec_sensor_data_dir(substr($adata["data"],$tfa_pos,$tfa_max),$offset);
                }
                else if ($tfa_function=="dec_temperature")              $tfa_sensor_data =dec_temperature(substr($adata["data"],$tfa_pos,$tfa_max));
                else if ($tfa_function=="dec_humidity")                 $tfa_sensor_data =dec_humidity(substr($adata["data"],$tfa_pos,$tfa_max));
                else if ($tfa_function=="dec_humidity_decimalplace")    $tfa_sensor_data =dec_humidity_decimalplace(substr($adata["data"],$tfa_pos,$tfa_max));
                else if ($tfa_function=="dec_airQuality")               $tfa_sensor_data =dec_airQuality(substr($adata["data"],$tfa_pos,$tfa_max));
                else if ($tfa_function=="dec_wetness")                  $tfa_sensor_data =dec_wetness(substr($adata["data"],$tfa_pos,$tfa_max));
                else if ($tfa_function=="dec_doorwindows")
                {
                    $tfa_sensor_data =dec_doorwindows(substr($adata["data"],$tfa_pos,$tfa_max),$timestamp_doorwindows);
                    $timestamp_doorwindows=  $tfa_sensor_data["time"];
                } 
                else if ($tfa_function=="dec_temperature_pos_rain") $tfa_sensor_data =dec_temperature_pos_rain(substr($adata["data"],$tfa_pos,$tfa_max));
                
                else if ($tfa_function=="dec_counter_rain") 
                {
 
                    if ($this->GetIDForIdent("rainfall")===false)   $i=-1;
                    if ($this->GetIDForIdent("raincounter")===false) $i=-1;
    
                    $oldcounter= $this->GetValue("raincounter");
                    $oldrain=   $this->GetValue("rainfall");
                    
                    $tfa_sensor_data =dec_counter_rain(substr($adata["data"],$tfa_pos,$tfa_max),$oldcounter,$oldrain);                    
                }

                else if ($tfa_function=="dec_event_rain") 
                {
                    $tfa_sensor_data =dec_event_rain(substr($adata["data"],$tfa_pos,$tfa_max),$timestamp_event_rain);
                    $timestamp_event_rain=  $tfa_sensor_data["time"];
                }
                else 
                {
                    $i=-1;
                    break;
                }
            }
            if ($i==4)
            {
                if($this->ReadPropertyBoolean ("var_".$ident))	 
                {
                    if ($this->ReadPropertyBoolean("var_debug_sensor")) $this->SendDebug("sensor",$ident." ".$tfa_sensor_data[$data["tfa_data"]],0); 
                    $this->SetValue($ident, $tfa_sensor_data[$data["tfa_data"]]);
                }
            }
        }
        if($this->ReadPropertyBoolean("var_cloud_aktivate"))  $this->sendtocloud($JSONString);
       
    }
}
/*******************************************************************************
 @author       Back-Blade
 @brief        RegisterPropertySensor
 @date         25.04.2020

 @param[]

 @return 

 @version      0.0.1 Doxygen style eingebaut und erstellen dieser File
 @see          
 *******************************************************************************/
    protected function RegisterPropertySensor()
    {
        $this->RegisterPropertyString("var_sensor_id","");
        $this->RegisterPropertyBoolean("var_debug_sensor",FALSE);
        $this->RegisterPropertyBoolean("var_debug_parent",FALSE);
        $this->RegisterPropertyBoolean("var_debug_cloud",FALSE);
        
        
        $this->RegisterPropertyBoolean("var_cloud_aktivate",FALSE);
        $this->RegisterPropertyInteger("var_cloud_waitfor_response",500);
        $this->RegisterPropertyString("var_cloud_Host_Address","www.data199.com");
        $this->RegisterPropertyString("var_cloud_URL","/gateway/put");
    
        if (array_key_exists('windoffset',$this->sensortyp))   $this->RegisterPropertyInteger	("var_Winddirection_offset",0);

    }
/*******************************************************************************
 @author       Back-Blade
 @brief        RegisterPropertyData
 @date         25.04.2020

 @param[$array] 		array auf cat->$ident


 @return   false oder array der daten

 @version      0.0.1 Doxygen style eingebaut und erstellen dieser File
 @see          
 *******************************************************************************/
    protected function RegisterPropertyData($array)
    {
        if ($this->ReadPropertyBoolean("var_debug_sensor")) $this->SendDebug("create","RegisterProperty",0); 
        foreach ($array as $ident => $data)
        {
            if ($ident[0]=="_") continue;
            $this->RegisterPropertyBoolean	("var_".$ident,FALSE);
        
        }
    }
/*******************************************************************************
 @author       Back-Blade
 @brief        makevar_start
 @date         26.04.2020

 @param[$id]   ID des Sensor

 @return       

 @version      0.0.1 Doxygen style eingebaut und erstellen dieser File
 @see          
 *******************************************************************************/
    protected function makevar_start($id)
    {
        $data ='{
        "elements": [
        {
        "type": "Image",
        "image": "data:image/png;base64, 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"
        },    
        {
            "type": "ValidationTextBox",
            "name": "var_sensor_id",
            "caption": "Sensor ID '.$id.':"
            
        },';
     
    return $data;
    }

/*******************************************************************************
 @author       Back-Blade
 @brief        makevar_data
 @date         26.04.2020

 @param[$pop]  Name Catecory
 @param[$array] Array ident

 @return       

 @version      0.0.1 Doxygen style eingebaut und erstellen dieser File
 @see          
 *******************************************************************************/
    protected function makevar_data($cat,$array)
    {
        $i=0;
        $str='{
        "type": "PopupButton",
		"caption": "'.$cat.'",
		"popup": {
		"caption": "'.$cat.'",
        "items": [';
        foreach ($array as $ident => $data)
        {
            if ($ident[0]=="_") continue;
            $i=$i+1;
            $str=$str.'{
                "type": "CheckBox",
                "name": "var_'.$ident.'",
                "caption": "'.$data["name"].'"
                },';
        }
        if ($i>=1)$str=substr($str, 0, -1);
        $str=$str.']
		}
        },';
    return $str;
    }
/*******************************************************************************
 @author       Back-Blade
 @brief        makevar_start
 @date         26.04.2020

 @param[$id]   ID des Sensor

 @return       

 @version      0.0.1 Doxygen style eingebaut und erstellen dieser File
 @see          
 *******************************************************************************/
    protected function makevar_windoffset()
    {
        $data ='{
                "type": "Select", 
                "name": "var_Winddirection_offset", 
                "caption": "Wind direction offset",
                "options": [
                    { "caption": "N     0,0 C", "value": 8 },
                    { "caption": "NNO  22,5 C", "value": 9 },
                    { "caption": "NO   45,0 C", "value": 10 },
                    { "caption": "ONO  67,5 C", "value": 11 },
                    { "caption": "O    90,0 C", "value": 12 },
                    { "caption": "OSO 112,5 C", "value": 13 },
                    { "caption": "SO  135,0 C", "value": 14 },
                    { "caption": "SSO 157,5 C", "value": 15 },
                    { "caption": "S   180,0 C", "value": 0 },
                    { "caption": "SSW 202,5 C", "value": 1 },
                    { "caption": "SW  225,0 C", "value": 2 },
                    { "caption": "WSW 247,5 C", "value": 3 },
                    { "caption": "W   270,0 C", "value": 4 },
                    { "caption": "WNW 292,5 C", "value": 5 },
                    { "caption": "NW  315,0 C", "value": 6 },
                    { "caption": "NNW 337,5 C", "value": 7 }
                ]
            },';
     
    return $data;
    }

/*******************************************************************************
 @author       Back-Blade
 @brief        makevar_start
 @date         26.04.2020
 @param[$id]   ID des Sensor

 @return       

 @version      0.0.1 Doxygen style eingebaut und erstellen dieser File
 @see          
 *******************************************************************************/
protected function makevar_cloud()
{
    $data ='{
    "type": "PopupButton",
    "caption": "cloud settings",
    "popup": {
    "caption": "cloud settings",
    "items": [ 
    {
        "type": "CheckBox",
        "name": "var_cloud_aktivate",
        "caption": "cloud aktivate"
        
    },
    {
        "type": "NumberSpinner",
        "name": "var_cloud_waitfor_response",
        "caption": "wait for resonse(in ms)"
        
      },
      {
        "type": "ValidationTextBox",
        "name": "var_cloud_Host_Address",
        "caption": "cloud host address"
        
      },
      {
        "type": "ValidationTextBox",
        "name": "var_cloud_URL",
        "caption": "url parameter"
        
      }
      ]
    }
    },';
 
return $data;
}
/*******************************************************************************
 @author       Back-Blade
 @brief        makevar_end
 @date         26.04.2020

 @param[]      

 @return       

 @version      0.0.1 Doxygen style eingebaut und erstellen dieser File
 @see          
 *******************************************************************************/
    protected function makevar_end()
    {
        $data='{
            "type": "PopupButton",
            "caption": "debug",
            "popup": {
            "caption": "debug",
            "items": [
                {
                "type": "CheckBox",
                "name": "var_debug_parent",
                "caption": "parent"
                },
                {
                "type": "CheckBox",
                "name": "var_debug_sensor",
                "caption": "sensors"
                },
                {
                "type": "CheckBox",
                "name": "var_debug_cloud",
                "caption": "cloud"
                }
            ]
            }
        }
        ],
        "actions": [
        ],
        "status": [
        ]
    }';
    return $data;       
    }
/******************************************************************************
 @author       Back-Blade
 @brief        CreateVariabel
 @date         25.04.2020

 @param[$array]  array auf strucktur der daten  

 @return       

 @version      0.0.1 Doxygen style eingebaut und erstellen dieser File
 @see          
 *******************************************************************************/
    protected function CreateVariabel($array)
    {
        foreach ($array as $ident => $data)
        {
            if ($ident[0]=="_") continue;
            if($this->ReadPropertyBoolean ("var_".$ident))
            {
                if ($this->ReadPropertyBoolean("var_debug_sensor"))
                {
                    $this->SendDebug("sensor","create    :" .$ident,0); 
                    $this->SendDebug("sensor","typ       :" .$data["typ"],0); 
                    $this->SendDebug("sensor","name      :".$data["name"],0); 
                    $this->SendDebug("sensor","translate :".$this->Translate($data["name"]),0); 
                    $this->SendDebug("sensor","profile   :" .$data["profile"],0);
                    $this->SendDebug("sensor","pos       :" .$data["pos"],0);
                }
                
                if ($data["typ"]==0) $this->RegisterVariableBoolean	($ident,$this->Translate($data["name"]),$data["profile"],$data["pos"]);
                if ($data["typ"]==1) $this->RegisterVariableInteger	($ident,$this->Translate($data["name"]),$data["profile"],$data["pos"]);
                if ($data["typ"]==2) $this->RegisterVariableFloat	($ident,$this->Translate($data["name"]),$data["profile"],$data["pos"]);
                if ($data["typ"]==3) $this->RegisterVariableString	($ident,$this->Translate($data["name"]),$data["profile"],$data["pos"]);
            }
        }
    }






/*******************************************************************************
 @author       Back-Blade
 @brief        
 @date         25.04.2020

 @param[$JSONString]  	JSONstring von Gateway

 @return   false oder array der daten

 @version      0.0.1 Doxygen style eingebaut und erstellen dieser File
 @see          
 *******************************************************************************/
    protected function Ceckdata($JSONString)
    {
        $dataarray = json_decode($JSONString);

        $DataID= 		utf8_decode($dataarray->DataID);
        $data=			utf8_decode($dataarray->Data);
        $packageheader=	utf8_decode($dataarray->PackageHeader);
        $timestamp=		utf8_decode($dataarray->Timestamp);
        $packagelength=	utf8_decode($dataarray->PackageLengt);
        $deviceid=		utf8_decode($dataarray->DeviceID);
        $crc= 			utf8_decode($dataarray->CRC);

        if ($DataID !="{7E53E668-20E9-7CDB-459C-B22E3B16D24F}") return false;

        if ($deviceid != strtoupper(($this->sensortyp["id"].$this->ReadPropertyString ("var_sensor_id")))) return false;
        if($this->ReadPropertyBoolean("var_debug_parent")  ==true)
        {
            $this->SendDebug( "parent", "id found",0);
        }
        if ($packageheader != chr($this->sensortyp["packageheader"])) return false;
        if($this->ReadPropertyBoolean("var_debug_parent")  ==true)
        {
            $this->SendDebug( "parent", "packageheader found",0);
        }	
        $this->SendDebug( "parent", "Package Length :".$packagelength."\r\n",0);
        
        if (((int) $packagelength) != ($this->sensortyp["packagelength"])) return false;
        if($this->ReadPropertyBoolean("var_debug_parent")  ==true)
        {
            $this->SendDebug( "parent", "packagelength found :".$packagelength,0);
            $this->SendDebug( "parent", "Package Header :".str2hexstr($packageheader)."\r\n",0);
            $this->SendDebug( "parent", "Timestamp :".gmdate("Y-m-d\TH:i:s", $timestamp)."\r\n",0);
            $this->SendDebug( "parent", "Package Length :".$packagelength."\r\n",0);
            $this->SendDebug( "parent", "Device ID :".$deviceid."\r\n",0);
            $this->SendDebug( "parent", "DATA :".str2hexstr($data)."\r\n",0);
            $this->SendDebug( "parent", "CRC :".str2hexstr($crc)."\r\n",0);
        }

        return array(
            "data" 				=>$data,
            "packageheader" 	=>$packageheader,
            "timestamp" 		=>$timestamp,
            "packagelength"		=>$packagelength,
            "deviceid" 			=>$deviceid,
            "crc" 				=>$crc,
            );
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
    protected function sendtocloud($JSONString)
    {
          // Empfangene Daten vom GATEWAY
		$dataarray = json_decode($JSONString);
		$id=			utf8_decode($dataarray->IDENTIFY);
        $sdata=			utf8_decode($dataarray->SDATA);
        if (array_key_exists('windoffset',$this->sensortyp)) 
        {
           $cdata = byteStr2byteArray($sdata);
           $a = array();
           for($i=0;$i<63;$i++)
           {
               if (($i==15) ||($i==19) || ($i==23) ||($i==27) || ($i==31) || ($i==35))
               {
                    $offset=$this->ReadPropertyInteger("var_Winddirection_offset");
                    $hnibl =($cdata[$i] >> 4) &0x0f;
                    $lnibl =($cdata[$i] >> 0) &0x0f;
                    $hnibl =$hnibl +$offset;
                    $hnibl =$hnibl %16;
                    $cdata[$i] =  ($hnibl <<4) + $lnibl;
               }
               array_push($a,$cdata[$i]);
           }
           $bcrc= array_sum ($a)& 0x7F;
           array_push($a,$bcrc);
           $sdata=bytearray2String($a);
        }
    
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
			$this->SendDebug("cloud","HTTP RESONSE :". $re,0);
			$this->SendDebug("cloud","HTTP RESONSE :". strlen($sdata),0);
			$this->SendDebug("cloud","HTTP RESONSE :". $sdata,0);
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
                	$this->SendDebug( "cloud","HTTP RESONSE :ERROR SOCKET",0);
            }
            $this->LogMessage("HTTP RESONSE :ERROR SOCKET", KL_WARNING);
            return false;
		} 
		
        socket_write($socket, $re, strlen($re));
        if($this->ReadPropertyBoolean("var_debug_cloud")  ==true)
        {
		    $this->SendDebug( "cloud","HTTP RESONSE :SEND DATA",0);
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
}