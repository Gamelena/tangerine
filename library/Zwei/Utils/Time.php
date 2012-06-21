<?php 
/**
 * Utilidades de Fecha/Hora
 * 
 */

class Zwei_Utils_Time{
	
	/**
	 * 
	 * @param $sec
	 * @param $padHours
	 * @param $showSeconds
	 * @param $showDays
	 * @param $addSufix
	 * @return string
	 */
	
	static public function secondsToHMS ($sec, $showSeconds = true, $showDays=false, $addSufix=false) 
	{
		$suDays = ($addSufix)?"<span>D&iacute;as</span>":"";
		$suHours = ($addSufix)?"<span>Horas</span>":"";
		$suMins = ($addSufix)?"<span>Minutos</span>":"";
		$suSecs = ($addSufix)?"<span>Segundos</span>":"";
		
	    $hms = "";
		
		if($showDays){
			$days = intval($sec / 86400);
			$hours = intval($sec / 3600) % (24);
			$hms.= str_pad($days, 2, "0", STR_PAD_LEFT).$suDays.":";
		}else{
			$hours = intval($sec / 3600);
		}
		
     	$hms .= str_pad($hours, 2, "0", STR_PAD_LEFT).$suHours. ":";
    
	    $minutes = intval(($sec / 60) % 60); 

	    $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT).$suMins;

	    if($showSeconds){
	    	$hms .= ":";
	    	$seconds = intval($sec % 60).$suSecs; 
	    	$hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);
	    }
	    return $hms;
	}
	
	/**
	 * 
	 * @param $datetime YYYY-MM-DD hh:mm:ss
	 * @param $lang "eng"||"esp"
	 * @return int timestamp
	 */
	
	static public function datetimeToTimestamp($datetime, $lang="eng")
	{
		try
		{
			//Zwei_Utils_Debug::write($datetime, false);
			$arrDateTime=explode(" ", trim($datetime));
	    	$arrDate=explode("-", $arrDateTime[0]);
	    	if(!is_array($arrDate)){ 
	    		$arrDate=explode("/", $arrDateTime[0]);
	    	}	
		    $arrTime=explode(":", $arrDateTime[1]);
		    //Zwei_Utils_Debug::write($datetime."|".$arrDateTime[0]."|".$arrDateTime[1]);
		    
		    
		    if ($lang=="eng"){
		    	$timestamp=@mktime($arrTime[0],$arrTime[1],$arrTime[2],(int)$arrDate[1],(int)$arrDate[2],(int)$arrDate[0]);
			}else if($lang=="esp"){
				$timestamp=@mktime((int)$arrTime[0],(int)$arrTime[1],(int)$arrTime[2],(int)$arrDate[1],(int)$arrDate[0],(int)$arrDate[2]);
			}
			//Zwei_Utils_Debug::write("mktime((int){$arrTime[0]},(int){$arrTime[1]},(int){$arrTime[2]},(int){$arrDate[1]},(int){$arrDate[2]},(int){$arrDate[0]})");
			
		}catch(Exception $e){
			$dumpDate=var_dump($arrDate);
			$dumpTime=var_dump($arrTime);
			
			Zwei_Utils_Debug::write("Error al convertir $datetime a TS ".$e->getCode()." ".$e->getMessage()."|$dumpDate|$dumpTime|");
		}	
		return $timestamp;
	}
}
