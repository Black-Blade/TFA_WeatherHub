<?php
/***************************************************************************//**
@author					Back-Blade and helhau
@brief					linux timestamp in byte array

@return					ByteArray						

@date    				13.10.2019
@version				0.0.1 Doxygen style eingebaut und erstellen dieser File
*******************************************************************************/
function gettimeinbyte()
{
	return unpack("C*", pack("L", time()));
}


/***************************************************************************//**
@author					Back-Blade and helhau
@brief					String  to Stirng HEX

@pram string			array of Byte

@return					Byte Array  
@exampel				"1234" -> "31323334"
						
@date    				13.10.2019
@version				0.0.1 Doxygen style eingebaut und erstellen dieser File
*******************************************************************************/	
 function str2hexstr($string){
    $hex='';
    for ($i=0; $i<strlen($string); $i++){
        $ord = ord($string[$i]);
        $hexCode = dechex($ord);
        $hex .= substr('0'.$hexCode, -2);
    }

    return $hex;
}

/***************************************************************************//**
@author					Back-Blade and helhau
@brief					StringHEX to Bytearray

@pram string			array of Byte

@return					Byte Array  
@exampel				"010203" -> (0x01,0x02,x002)
						
@date    				13.10.2019
@version				0.0.1 Doxygen style eingebaut und erstellen dieser File
*******************************************************************************/	
 function hexstr2byeARRAY($string)
{
 return byteStr2byteArray(hex2bin($string));
}

/***************************************************************************//**
@author					Back-Blade and helhau
@brief					String to Bytearray

@pram string			array of Byte

@return					Byte Array  
@exampel				"123" -> (0x31,0x32,x032)
						
@date    				13.10.2019
@version				0.0.1 Doxygen style eingebaut und erstellen dieser File
*******************************************************************************/	
 function byteStr2byteArray($string) {
    return array_slice(unpack("C*", "\0".$string), 1);
}

/***************************************************************************//**
@author					Back-Blade and helhau
@brief					ByteARRAY to HEX

@pram carray			array of Byte

@return					String  
@exampel				(0x00,0x0x01,0x02) -> "000102"
						
@date    				13.10.2019
@version				0.0.1 Doxygen style eingebaut und erstellen dieser File
*******************************************************************************/	
 function bytearry2hexstring($carray)
{
	$temp ="";
	for ($i=0;$i<sizeof($carray);$i++)
	{
		$temp.=$carray[$i];
	}
	return $temp;
}

/***************************************************************************//**
@author					Back-Blade and helhau
@brief					Ein Byte to HEX

@pram c					Byte

@return					String  
@exampel				0x00 -> "00"

						
@date    				13.10.2019
@version				0.0.1 Doxygen style eingebaut und erstellen dieser File
*******************************************************************************/	
 function byte2hexString($c)
{
	$temp = dechex($c);
	if (strlen($temp)==0) return "00";
	if (strlen($temp)==1) return "0".$temp;
	return $temp;
}
/***************************************************************************//**
@author					Back-Blade and helhau
@brief					bytearray2String

@pram c					byte of array

@return					String  
@exampel				(0x31,0x32,x032) -> "123"

						
@date    				13.10.2019
@version				0.0.1 Doxygen style eingebaut und erstellen dieser File
*******************************************************************************/	
function bytearray2String($carray)
{
	$temp ="";
	for ($i=0;$i<sizeof($carray);$i++)
	{
		$temp.=chr($carray[$i]);
	}
	return $temp;
}

/***************************************************************************//**
@author					Back-Blade and helhau
@brief					sucht sich die zu erwarten Daten aus dem HEADER

@parm	data			HEADER

@return					L�nge der zu erwarten Daten	

@date    				13.10.2019
@version				0.0.1 Doxygen style eingebaut und erstellen dieser File
*******************************************************************************/	

