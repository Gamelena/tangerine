<?php

/**
 * Funcionalidades de Strings
 * 
 * @package Zwei_Utils 
 * @version $Id:$
 * @since 0.1
 *
 */

class Zwei_Utils_String {
	
	public static function mask($haystack, $char_list,$cut_chars=false) {
		$haystack = str_split($haystack);
		$newstr = array();
		foreach($haystack as $letter) 
			if((boolean)strpos($char_list,$letter)!==(boolean) $cut_chars) 
				$newstr[] = $letter;
		return implode('',$newstr);
	}
	
	/**
	 * Reemplaza caracteres especiales por estandar internacionales. 
	 * @param $string string
	 * @param $nospaces boolean, reemplazar espacios por underscores.
	 * @return string
	 */
	
	public static function stripAccents($string, $nospaces=false)
	{
	    $string = ereg_replace("[äáàâãª]","a",$string);
	    $string = ereg_replace("[ÁÀÂÃÄ]","A",$string);
	    $string = ereg_replace("[ÍÌÎÏ]","I",$string);
	    $string = ereg_replace("[íìîï]","i",$string);
	    $string = ereg_replace("[éèêë]","e",$string);
	    $string = ereg_replace("[ÉÈÊË]","E",$string);
	    $string = ereg_replace("[óòôõöº]","o",$string);
	    $string = ereg_replace("[ÓÒÔÕÖ]","O",$string);
	    $string = ereg_replace("[úùûü]","u",$string);
	    $string = ereg_replace("[ÚÙÛÜ]","U",$string);
	    $string = ereg_replace("[^´`¨~]","",$string);
	    $string = str_replace("ç","c",$string);
	    $string = str_replace("Ç","C",$string);
	    $string = str_replace("ñ","n",$string);
	    $string = str_replace("Ñ","N",$string);
	    $string = str_replace("Ý","Y",$string);
	    $string = str_replace("ý","y",$string);
	    if($nospaces) $string = str_replace(" ","_",$string);
	    return $string;
	}	
	
	/**
	 * Transforma una variable a un string que sigue las convenciones Zend
	 * de nombres de clases (no incluye namespaces virtuales)
	 * @param $string
	 * @param $limiter
	 * @return string
	 */
	
	public static function toClassWord($string, $limiter="_"){
		$string=explode($limiter, $string);
		
		if(is_array($string)){
			$return="";
			foreach($string as $s){
				$return.=ucfirst($s); 
			}
		}else{
			$return=ucfirst($string);
		}
		return $return;		
	}
	
	/**
	 * Transforma una variable a un string que sigue las convenciones Zend
	 * de nombres de métodos
	 * @param $string
	 * @param $limiter
	 * @return string
	 */
	
	public static function toFunctionWord($string, $limiter="_"){
		$string=explode($limiter, $string);
		
		if(is_array($string)){
			$return="";
			$i=0;
			foreach($string as $s){
				$return.=($i==0)?$s:ucfirst($s);
				$i++; 
			}
		}else{
			$return=$string;
		}
		return $return;		
	}
	
	/**
	 * Transforma una variable a un string a nombres de variables simple 
	 * compatible con identificadores Dom HTML y variables Javascript
	 * @param $string
	 * @param $limiter
	 * @return string
	 */	
	
	public static function toVarWord($string){
		$string = preg_replace("/(?<=[a-zA-Z])(?=[A-Z])/", "_", $string);
		$string = preg_replace("/[\.-]/", "_", $string);
		return $string;
	}
	
	
	
}
