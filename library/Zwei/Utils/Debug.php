<?php
/**
 * Almacenar mensajes en archivos de texto plano para log, debugging y seguimiento de errores
 * 
 * @category Zwei
 * @package Zwei_Utils
 * @version $Id:$
 * @since 0.1
 * 
 * @example: Zwei_Utils_Debug::write($mensaje)
 *
 *
 */
class Zwei_Utils_Debug
{
	
  /**
   * Escribe el reporte de error en un archivo de texto plano llamado debug
   * @param string $message - texto a escribir en archivo. 
   * @param $file - ruta relativa del archivo a escribir.
   */	
    function write($message = null, $file = "../log/debug")
    {
        $trace = debug_backtrace() ;
	    if  ($message !== null ){
	        $message = $trace[0]['file'].'['.$trace[0]['line'].']['.strftime('%Y-%m-%d %H:%M:%S').']: '.print_r($message, 1);
	    } else {
	        $message = "[".strftime('%Y-%m-%d %H:%M:%S')."]\n".print_r($trace, 1);
	    }
	    $ff = fopen($file, "a");
	    fwrite($ff,"$message\r\n");
	    fclose($ff);
	}
	
	/**
	 * Escribe mensaje en archivo de log, dependiendo de valores de tabla de configuración global (web_settings). 
	 *  
	 * @param string $message - texto a escribir en archivo.
	 * @param string $settings_id - PK de web_settings.
	 * @param string $settings_value - valor de 'value' esperado de web_settings para escribir en archivo. 
	 * @param string $file - ruta relativa del archivo a escribir.
	 * @return void
	 */
	function writeBySettings($message, $settings_id, $settings_value='SI', $file= "../log/debug")
	{
        $oSettings = new SettingsModel();
        try {
            $oSettingsSelect = $oSettings->select()->where('id = ?', $settings_id);
            $aSettings = $oSettings->fetchRow($oSettingsSelect);
            if (isset($aSettings) && $aSettings->value == $settings_value) {
                $trace = debug_backtrace();
            	if ($message !==null ) {
	               $message = $trace[0]['file'].'['.$trace[0]['line'].']['.strftime('%Y-%m-%d %H:%M:%S').']: '.print_r($message, 1);
		        } else {
		           $message = "[".strftime('%Y-%m-%d %H:%M:%S')."]\n".print_r($trace, 1);
		        }
		        $ff = fopen($file, "a");
		        fwrite($ff, "$message\r\n");
		        fclose($ff);
            }
        } catch (Zend_Db_Exception $e) {
            Zwei_Utils_Debug::write("Error {$e->getCode()} {$e->getMessage()}");
        } 
	}	
}
