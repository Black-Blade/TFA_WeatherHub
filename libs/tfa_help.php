<?php
//set base dir

if (!defined('__ROOT__'))  define('__ROOT__', dirname(dirname(__FILE__)));

//load helper functionen
require_once __ROOT__ . '/libs/help.php';




/***************************************************************************//**
@author					Back-Blade and helhau
@brief					decodiert sensordaten

@return					Array						

@date    				23.03.2020
@version				0.0.1 Doxygen style eingebaut und erstellen dieser File
*******************************************************************************/
function dec_sensor_data($data,$timestamp)
{
    $data= substr($data,0,2); 
    $cdata = byteStr2byteArray($data);

    $array = array(
        "battery" =>   (($cdata[0]& 0x80)>>7),
        "heartbeat" =>  (($cdata[0]& 0x40)>>6),
        "counter" =>     (($cdata[0]& 0x3f) <<8) + ($cdata[1]),
        "update" =>     $timestamp,
    );
 
  
    return $array;
}



/***************************************************************************//**
@author					Back-Blade and helhau
@brief					decodiert sensordaten

@return					Array						

@date    				23.03.2020
@version				0.0.1 Doxygen style eingebaut und erstellen dieser File
*******************************************************************************/
function dec_sensor_data_wind($data,$timestamp)
{
    $data= substr($data,0,3); 
    $cdata = byteStr2byteArray($data);

    $array = array(
        "battery" =>    (($cdata[0]& 0x80)>>7),
        "heartbeat" =>  (($cdata[0]& 0x40)>>6),
        "counter" =>    (($cdata[0]& 0x3f) <<16) + (($cdata[1]) << 8)+ ($cdata[2]),
        "update" =>     $timestamp,
    );
 
    return $array;
}
/***************************************************************************//**
@author					Back-Blade and helhau
@brief					decodiert sensordaten

@return					Array						

@date    				23.03.2020
@version				0.0.1 Doxygen style eingebaut und erstellen dieser File
*******************************************************************************/
function dec_sensor_data_dir($data,$offset)
{
    $datalen=strlen($data);
    $data= substr($data,0,4); 
    $cdata = byteStr2byteArray($data);

    $sensor_winddirection= (($cdata[0]& 0xF0)>>4);
    $sensor_winddirection =$sensor_winddirection +$offset;
    $sensor_winddirection =$sensor_winddirection %16;
    $sensor_winddirection= ((float) $sensor_winddirection)*22.5;
    
    //$sensor_unknownbit27= (($cdata[0]& 0x08)>>3); 
    //$sensor_unknownbit26= (($cdata[0]& 0x04)>>2);
    $sensor_windspeed=(float) ((($cdata[0]& 0x02)>>1) *256); 
    $sensor_gustspeed=(float) ((($cdata[0]& 0x01)>>0)*256); 
    
    $sensor_windspeed= (float)($sensor_windspeed + $cdata[1])/10;
    $sensor_gustspeed= (float)($sensor_gustspeed + $cdata[2])/10;
    
    $sensor_lasttransmit=-1;
    if ($datalen==4) $sensor_lasttransmit = $cdata[3]*2;

    $array = array(
        "winddirection" =>    $sensor_winddirection,
        "windspeed" =>  $sensor_windspeed,
        "gustspeed" =>  $sensor_gustspeed,
        "lasttransmit" =>  $sensor_lasttransmit,
    );
 
    return $array;

}
/***************************************************************************//**
@author					Back-Blade and helhau
@brief					decodiert 

@return					Array	temperature					

@date    				23.03.2020
@version				0.0.1 Doxygen style eingebaut und erstellen dieser File
*******************************************************************************/


function dec_temperature($data)
{
    $tempf=0.0;
    $data= substr($data,0,2);
    $cdata = byteStr2byteArray($data);

    $temp = (($cdata[0]& 0x07) <<8) + ($cdata[1]);
    if (($temp >=0) && ($temp <=1023)) $tempf = $temp*0.1;
    else if (($temp >=1024) && ($temp <=2047)) $tempf = (2048-$temp) * -0.1;


    $array = array(
        "up05" =>   (($cdata[0]& 0x80)>>7),
        "down05" =>  (($cdata[0]& 0x40)>>6),
        "overflow" =>  (($cdata[0]& 0x20)>>5),
        "error" =>  (($cdata[0]& 0x10)>>4),
        //"unknownbit12" =>  (($cdata[0]& 0x10)>>5),
        "temperature" =>  $tempf,
    );
    return $array;
}

/***************************************************************************//**
@author					Back-Blade and helhau
@brief					decodiert humidity

@return					Array						

@date    				23.03.2020
@version				0.0.1 Doxygen style eingebaut und erstellen dieser File
*******************************************************************************/
function dec_humidity($data)
{
    $data= substr($data,0,2);
    $cdata = byteStr2byteArray($data);

    $array = array(
        "up05" =>   (($cdata[0]& 0x80)>>7),
        "down05" =>  (($cdata[0]& 0x40)>>6),
        //"unknownbit14" =>  (($cdata[0]& 0x20)>>5),
        //"unknownbit13" =>  (($cdata[0]& 0x10)>>4),
        "id"           =>(($cdata[0]& 0x0F)>>0),
        "average"=>(($cdata[1]& 0x80)>>7),
        "humidity"=>(float)(($cdata[1]& 0x7F)),
    );
    return $array;
}

/***************************************************************************//**
@author					Back-Blade and helhau
@brief					decodiert humidity_decimalplace
@return					Array						

@date    				23.03.2020
@version				0.0.1 Doxygen style eingebaut und erstellen dieser File
*******************************************************************************/
function dec_humidity_decimalplace($data)
{
    $data= substr($data,0,2);
    $cdata = byteStr2byteArray($data);

    $hum = (float) (($cdata[0]& 0x03) <<8) + ($cdata[1]);
    $hum =($hum/10);

    $array = array(
        //"unknownbit16 " =>   (($cdata[0]& 0x80)>>7),
        //"unknownbit15" =>  (($cdata[0]& 0x40)>>6),
        //"unknownbit14" =>  (($cdata[0]& 0x20)>>5),
        //"unknownbit13" =>  (($cdata[0]& 0x10)>>4),
        //"unknownbit12 " =>   (($cdata[0]& 0x08)>>3),
        //"unknownbit11" =>  (($cdata[0]& 0x04)>>2),
       "humidity "=> $hum,
    );
    return $array;
}

/***************************************************************************//**
@author					Back-Blade and helhau
@brief					decodiert AirQuality
@return					Array						

@date    				23.03.2020
@version				0.0.1 Doxygen style eingebaut und erstellen dieser File
*******************************************************************************/
function dec_airQuality($data)
{
    $data= substr($data,0,2);
    $cdata = byteStr2byteArray($data);

    $array = array(
        //"unknownbit16 " =>   (($cdata[0]& 0x80)>>7),
        //"unknownbit15" =>  (($cdata[0]& 0x40)>>6),
        //"unknownbit14" =>  (($cdata[0]& 0x20)>>5),
        //"unknownbit13" =>  (($cdata[0]& 0x10)>>4),
        //"unknownbit12 " =>   (($cdata[0]& 0x08)>>3),
        //"unknownbit11" =>  (($cdata[0]& 0x04)>>2),
        //"unknownbit10" =>  (($cdata[0]& 0x02)>>1),
        "overflow" =>  (($cdata[0]& 0x01)>>0),
        "ppm"=> $cdata[1]*50,
    );
    return $array;
}


/***************************************************************************//**
@author					Back-Blade and helhau
@brief					decodiert Wetness
@return					Array						

@date    				23.03.2020
@version				0.0.1 Doxygen style eingebaut und erstellen dieser File
*******************************************************************************/
function dec_wetness($data)
{
    $data= substr($data,0,1);
    $cdata = byteStr2byteArray($data);

    $array = array(
        //"unknownbit8 " =>   (($cdata[0]& 0x80)>>7),
        //"unknownbit7" =>  (($cdata[0]& 0x40)>>6),
        //"unknownbit6" =>  (($cdata[0]& 0x20)>>5),
        //"unknownbit5" =>  (($cdata[0]& 0x10)>>4),
        //"unknownbit4 " =>   (($cdata[0]& 0x08)>>3),
        //"unknownbit3" =>  (($cdata[0]& 0x04)>>2),
        "wet"=>  (($cdata[0]& 0x02)>>1),
		"dry"=> (($cdata[0]& 0x01)>>0),
    );
    return $array;
}

/*******************************************************************************
 @author       Back-Blade
 @brief        decodiert Fenster und Tuersensor
 @date         04.04.2020

 @param[]      

 @return       

 @version      0.0.1 Doxygen style eingebaut und erstellen dieser File
 @see          
 *******************************************************************************/
function dec_doorwindows($data,$timestamp)
{
    $datalen=strlen($data);
    $data= substr($data,0,2);
    $cdata = byteStr2byteArray($data);


    $time=0;
    if ($datalen==2) $time = (int)  (($cdata[0]& 0x1f) <<8) + ($cdata[1]);
    $uint = (int)  (($cdata[0]& 0x60)>>5);

    if ($uint==0) $time= 86400* $time;
    if ($uint==1) $time= 3600* $time;
    if ($uint==2) $time= 60* $time;
    if ($uint==3) $time= $time;
    
    $time=$timestamp-$time;
    $array = array(
        "sensor" =>     (($cdata[0]& 0x80)>>7),
        "time" =>       $time,
        "uint" =>        $uint,
    );

    return $array;
}
/***************************************************************************//**
@author					Back-Blade and helhau
@brief					decodiert 

@return					Array	temperature					

@date    				23.03.2020
@version				0.0.1 Doxygen style eingebaut und erstellen dieser File
*******************************************************************************/


function dec_temperature_pos_rain($data)
{
    $data= substr($data,0,2);
    $cdata = byteStr2byteArray($data);
    
    $pos="";
    $uint = (int)  (($cdata[0]& 0xC0)>>6);

    if ($uint==0) $pos="2 hour idle timer";
    if ($uint==1) $pos="right";
    if ($uint==2) $pos="left";
    if ($uint==3) $pos="!probably unused!";

    $tempf=0.0;
    $temp = (($cdata[0]& 0x07) <<8) + ($cdata[1]);
    if (($temp >=0) && ($temp <=1023)) $tempf = $temp*0.1;
    else if (($temp >=1024) && ($temp <=2047)) $tempf = (2048-$temp) * -0.1;

    //to do neagive temeraturen ?
    $array = array(
        "pos" =>        $pos,
        "temp" =>       $tempf,
        "overflow" =>   (($cdata[0]& 0x20)>>5),
        "error" =>      (($cdata[0]& 0x10)>>4),
        "uint"=>        $uint
    );
    return $array;
}



/***************************************************************************//**
@author					Back-Blade and helhau
@brief					decodiert 

@return					Array	temperature					

@date    				23.03.2020
@version				0.0.1 Doxygen style eingebaut und erstellen dieser File
*******************************************************************************/


function dec_counter_rain($data,$oldcounter,$oldrain)
{
    $tempf=0.0;
    $data= substr($data,0,2);
    $cdata = byteStr2byteArray($data);

    $counter =(int) (($cdata[0]& 0xFF) <<8) + ($cdata[1]);
    $rain = (float) ($counter - $oldcounter) * 0.25; 

    if ($rain >0) $newrain= $rain+$oldrain;
    else $newrain=$oldrain;

    $array = array(
        "counter"   =>         $counter,
        "rainnew"   =>         $newrain,
    
    );

    return $array;

}


/*******************************************************************************
 @author       Back-Blade
 @brief        decodiert Fenster und Tuersensor
 @date         04.04.2020

 @param[]      

 @return       

 @version      0.0.1 Doxygen style eingebaut und erstellen dieser File
 @see          
 *******************************************************************************/
function dec_event_rain($data,$timestamp)
{
    $data= substr($data,0,2);
    $cdata = byteStr2byteArray($data);

    $uint = (int)  (($cdata[0]& 0xC0)>>6);
    $time = (int)  (($cdata[0]& 0x3f) <<8) + ($cdata[1]);

    if ($uint==0) $time= 86400* $time;
    if ($uint==1) $time= 3600* $time;
    if ($uint==2) $time= 60* $time;
    if ($uint==3) $time= $time;
     
    $time=$timestamp-$time;
    $array = array(
        "time" =>       $time,
        "uint" =>       $uint,
    );

    return $array;
}