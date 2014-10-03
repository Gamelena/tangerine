<?php 
/**
 * Salida de mensajes a archivos de log y/o consola de javascript.
 * Se requiere firePHP para firefox o equivalente para consola javascript.
 * 
 * Observación: Esta clase tiene mucha duplicación de código, la razón de esto es que NO podemos usar un método base
 * que permitiría la reutilización de código, ya que debug_backtrace() retornaría siempre ese contexto base 
 * en lugar de retornar el contexto que queremos depurar o dejar registrado. 
 *  
 */
class Console
{
    /**
     * Escribe el reporte en un archivo de log del sistema.
     * Muestra salida por consola javascript si $showOutput=true.
     * 
     * @param string $message - texto a escribir en archivo.
     * @param boolean $showOutput
     * @param boolean $write
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
     * Escribe el reporte en un archivo de log del sistema.
     * Muestra salida por consola javascript.
     * 
     * @param string $message - texto a escribir en archivo.
     * @param boolean $showOutput
     * @param boolean $write
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
     * Muestra salida por consola javascript.
     * Escribe el reporte de error en un archivo de texto plano llamado debug si $write=true.
     *  
     * @param string $message - texto a escribir en archivo.
     * @param boolean $showOutput
     * @param boolean $write
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
     * Muestra salida por consola javascript.
     * Escribe el reporte en un archivo de error del sistema.
     * 
     * @param string $message - texto a escribir en archivo.
     * @param boolean $showOutput
     * @param boolean $write
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
     * Escribe el reporte en un archivo de error del sistema.
     * Muestra salida por consola javascript si $showOutput = true.
     * 
     * @param string $message - texto a escribir en archivo.
     * @param boolean $showOutput
     * @param boolean $write
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

