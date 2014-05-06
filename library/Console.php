<?php 
/**
 * Salida de mensajes a log ya consola de javascript.
 * Se require firePHP para firefox o equivalente.
 * 
 * Observación: la razón de multiplicación del código de las funciones con diferentes parámetros es porque 
 * debug_backtrace() retorna el contexto desde donde se invoca el método, si tuvieramos un método base 
 * el contexto sería ese método base por lo que el traceo carecería de utilidad. 
 *  
 */
class Console
{
    /**
     * Escribe el reporte de error en un archivo de texto plano llamado debug
     * @param string $message - texto a escribir en archivo.
     * @param string $file - ruta del archivo a escribir.
     */
    static function log($message = null, $showOutput = false, $write = true, $file = 'php://stdout')
    {
        $trace = debug_backtrace() ;
        if  ($message !== null ){
            $message = $trace[0]['file'].'['.$trace[0]['line'].']['.strftime('%Y-%m-%d %H:%M:%S').']: '.print_r($message, 1);
        } else {
            $message = $trace[0]['file'].'['.$trace[0]['line'].']['.strftime('%Y-%m-%d %H:%M:%S').'] El grillo dijo "cri cri" (acá no hay nada).';
        }
        
        if ($write) {
            $ff = fopen($file, "a");
            fwrite($ff, "[ADMPORTAL LOG]: $message\r\n");
            fclose($ff);
        }
        if ($showOutput) {
            $logger = new Zend_Log();
            $writer = new Zend_Log_Writer_Firebug();
            $logger->addWriter($writer);
            $logger->log($message, Zend_Log::NOTICE);
        }
    }
    
    /**
     * Escribe el reporte de error en un archivo de texto plano llamado debug
     * @param string $message - texto a escribir en archivo.
     * @param string $file - ruta del archivo a escribir.
     */
    static function info($message = null, $showOutput = true, $write = true, $file = 'php://stdout')
    {
        $logger = new Zend_Log();
        $writer = new Zend_Log_Writer_Firebug();
        $logger->addWriter($writer);
        
        $trace = debug_backtrace() ;
        if  ($message !== null ){
            $message = $trace[0]['file'].'['.$trace[0]['line'].']['.strftime('%Y-%m-%d %H:%M:%S').']: '.print_r($message, 1);
        } else {
            $message = $trace[0]['file'].'['.$trace[0]['line'].']['.strftime('%Y-%m-%d %H:%M:%S').'] El grillo dijo "cri cri" (acá no hay nada).';
        }
        if ($write) {
            $ff = fopen($file, "a");
            fwrite($ff, "[ADMPORTAL INFO]: $message\r\n");
            fclose($ff);
        }
        
        if ($showOutput) {
            $logger = new Zend_Log();
            $writer = new Zend_Log_Writer_Firebug();
            $logger->addWriter($writer);
            $logger->log($message, Zend_Log::INFO);
        }
    }
    
    /**
     * Escribe el reporte de error en un archivo de texto plano llamado debug
     * @param string $message - texto a escribir en archivo.
     * @param string $file - ruta del archivo a escribir.
     */
    static function debug($message = null, $showOutput = true, $write = false, $file = null)
    {
        if ($file == null) {
            $file = ROOT_DIR."/log/debug";
        } else {
            $write = true;
        }
        $trace = debug_backtrace() ;
        if  ($message !== null ){
            $message = $trace[0]['file'].'['.$trace[0]['line'].']['.strftime('%Y-%m-%d %H:%M:%S').']: '.print_r($message, 1);
        } else {
            $message = $trace[0]['file'].'['.$trace[0]['line'].']['.strftime('%Y-%m-%d %H:%M:%S').'] El grillo dijo "cri cri" (acá no hay nada).';
        }
        if ($write) {
            $ff = fopen($file, "a");
            fwrite($ff, "$message\r\n");
            fclose($ff);
        }
        if ($showOutput) {
            $logger = new Zend_Log();
            $writer = new Zend_Log_Writer_Firebug();
            $logger->addWriter($writer);
            $logger->log($message, Zend_Log::DEBUG);
        }
    }
    
    /**
     * Escribe el reporte de error en un archivo de texto plano llamado debug
     * @param string $message - texto a escribir en archivo.
     * @param string $file - ruta del archivo a escribir.
     */
    static function warn($message = null, $showOutput = true, $write = true,$file = 'php://stderr')
    {
        $trace = debug_backtrace() ;
        if  ($message !== null ){
            $message = $trace[0]['file'].'['.$trace[0]['line'].']['.strftime('%Y-%m-%d %H:%M:%S').']: '.print_r($message, 1);
        } else {
            $message = $trace[0]['file'].'['.$trace[0]['line'].']['.strftime('%Y-%m-%d %H:%M:%S').'] El grillo dijo "cri cri" (acá no hay nada).';
        }
        if ($write) {
            $ff = fopen($file, "a");
            fwrite($ff, "[ADMPORTAL WARNING]: $message\r\n");
            fclose($ff);
        }
        
        if ($showOutput) {
            $logger = new Zend_Log();
            $writer = new Zend_Log_Writer_Firebug();
            $logger->addWriter($writer);
            $logger->log($message, Zend_Log::WARN);
        }
    }
    
    
    /**
     * Escribe el reporte de error en un archivo de texto plano llamado debug
     * @param string $message - texto a escribir en archivo.
     * @param string $file - ruta del archivo a escribir.
     */
    static function error($message = null, $showOutput = false, $write = true, $file = 'php://stderr')
    {

        
        $trace = debug_backtrace() ;
        if  ($message !== null ){
            $message = $trace[0]['file'].'['.$trace[0]['line'].']['.strftime('%Y-%m-%d %H:%M:%S').']: '.print_r($message, 1);
        } else {
            $message = $trace[0]['file'].'['.$trace[0]['line'].']['.strftime('%Y-%m-%d %H:%M:%S').'] El grillo dijo "cri cri" (acá no hay nada).';
        }
        if ($write) {
            $ff = fopen($file, "a");
            fwrite($ff, "[ADMPORTAL ERROR]: $message\r\n");
            fclose($ff);
        }
        
        if ($showOutput) {
            $logger = new Zend_Log();
            $writer = new Zend_Log_Writer_Firebug();
            $logger->addWriter($writer);
            $logger->log($message, Zend_Log::ERR);
        }
    }
    

}

