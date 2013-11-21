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
   * @param string $file - ruta del archivo a escribir.
   */    
    static function write($message = null, $file = null )
    {
        if ($file == null) $file = ROOT_DIR."/log/debug";
        $trace = debug_backtrace() ;
        if  ($message !== null ){
            $message = $trace[0]['file'].'['.$trace[0]['line'].']['.strftime('%Y-%m-%d %H:%M:%S').']: '.print_r($message, 1);
        } else {
            $message = $trace[0]['file'].'['.$trace[0]['line'].']['.strftime('%Y-%m-%d %H:%M:%S').'] Variable vacia.';
        }
        $ff = fopen($file, "a");
        fwrite($ff, "$message\r\n");
        fclose($ff);
    }
    
    /**
     * Escribe mensaje en archivo de log, dependiendo de valores de tabla de configuración global (web_settings). 
     *  
     * @param string $message - texto a escribir en archivo.
     * @param string $settingsId - PK de web_settings.
     * @param string $settingsValue - valor de 'value' esperado de web_settings para escribir en archivo. 
     * @param string $file - ruta del archivo a escribir.
     * @return void
     */
    static function writeBySettings($message, $settingsId, $settingsValue = '1', $file = null)
    {
        if ($file == "") $file = ROOT_DIR."/log/debug";
        $oSettings = new SettingsModel();
        try {
            $oSettingsSelect = $oSettings->select()->where('id = ?', $settingsId);
            $aSettings = $oSettings->fetchRow($oSettingsSelect);
            if (isset($aSettings) && $aSettings->value == $settingsValue) {
                /**
                 * @fixme
                 * Acá se duplica código en lugar de llamar a self::write($message, $file) 
                 * porque debug_backtrace() retornaría como contexto la clase Debug, en lugar de el contexto donde Debug es instanciado
                 * lo que dificulta el seguimiento. Tomarlo en cuenta si se refactoriza.
                 */  
                $trace = debug_backtrace();
                if ($message !==null ) {
                   $message = $trace[0]['file'].'['.$trace[0]['line'].']['.strftime('%Y-%m-%d %H:%M:%S').']: '.print_r($message, 1);
                } else {
                   $message = $trace[0]['file'].'['.$trace[0]['line'].']['.strftime('%Y-%m-%d %H:%M:%S').'] Variable vacia.';
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
