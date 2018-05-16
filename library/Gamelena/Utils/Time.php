<?php
/**
 * Utilidades de Fecha/Hora
 */

class Gamelena_Utils_Time
{
    
    /**
     * Convierte segundos a hh:mm:ss
     * @param $sec int
     * @param $showSeconds boolean
     * @param $showDays boolean
     * @param $addSufix boolean
     * @return string
     */
    
    static public function secondsToHMS($sec, $showSeconds = true, $showDays = false, $addSufix = false)
    {
        $suDays  = ($addSufix) ? "<span>D&iacute;as</span>" : "";
        $suHours = ($addSufix) ? "<span>Horas</span>" : "";
        $suMins  = ($addSufix) ? "<span>Minutos</span>" : "";
        $suSecs  = ($addSufix) ? "<span>Segundos</span>" : "";
        
        $hms = "";
        
        if ($showDays) {
            $days  = intval($sec / 86400);
            $hours = intval($sec / 3600) % (24);
            $hms .= str_pad($days, 2, "0", STR_PAD_LEFT) . $suDays . ":";
        } else {
            $hours = intval($sec / 3600);
        }
        
        $hms .= str_pad($hours, 2, "0", STR_PAD_LEFT) . $suHours . ":";
        
        $minutes = intval(($sec / 60) % 60);
        
        $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT) . $suMins;
        
        if ($showSeconds) {
            $hms .= ":";
            $seconds = intval($sec % 60) . $suSecs;
            $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);
        }
        return $hms;
    }
    
    /**
     * Convierte un string datetime a un timestamp.
     * 
     * @param  $datetime datetime YYYY-MM-DD hh:mm:ss|DD-MM-YYY hh:mm:ss
     * @param  $lang string "eng"|"esp"
     * @return int timestamp
     */
    static public function datetimeToTimestamp($datetime, $lang = "eng")
    {
        $arrDateTime = explode(" ", trim($datetime));
        $arrDate     = explode("-", $arrDateTime[0]);
        if (!is_array($arrDate)) {
            $arrDate = explode("/", $arrDateTime[0]);
        }
        $arrTime = explode(":", $arrDateTime[1]);
        
        
        if ($lang == "eng") {
            $timestamp = mktime((int) $arrTime[0], (int) $arrTime[1], (int) $arrTime[2], (int) $arrDate[1], (int) $arrDate[2], (int) $arrDate[0]);
        } else if ($lang == "esp") {
            $timestamp = mktime((int) $arrTime[0], (int) $arrTime[1], (int) $arrTime[2], (int) $arrDate[1], (int) $arrDate[0], (int) $arrDate[2]);
        }
        return $timestamp;
        //Gamelena_Utils_Debug::write("mktime((int){$arrTime[0]},(int){$arrTime[1]},(int){$arrTime[2]},(int){$arrDate[1]},(int){$arrDate[2]},(int){$arrDate[0]})");
    }
    
    /**
     * Retorna un array de días u horas de un intervalo de tiempo dado.
     * 
     * @param  $from int timestamp - datetime desde.
     * @param  $to int timestamp - datetime hasta.
     * @param  $interval string "hours"|"days" - retornar días u horas.
     * @return array 
     */
    static public function createInterval($from, $to, $interval = "hours")
    {
        $from = (int) $from;
        $to = (int) $to;
        
        $return = array();
        if ($interval == "hours") {
            $seconds = 3600;
        } elseif ($interval == "days") {
            $seconds = 86400;
        }
        
        $j = 1;
        
        for ($i = $from; $i <= $to; $i += $seconds) {
            $return[] = $from + ($seconds * $j);
            $j++;
        }
        return $return;
    }
}
