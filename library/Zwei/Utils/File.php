<?php
/**
 * Utilidades de archivos.
 * 
 * @category Zwei
 *
 */
class Zwei_Utils_File
{
    /**
     * 
     * @param string - ruta 
     * @param boolean
     * @return void
     */
    public function clearRecursive($dir, $remove_dir = false) {
        foreach (glob($dir . '/*') as $file) {
            if(is_dir($file))
                Zwei_Utils_File::clearRecursive($file);
            else
                unlink($file);
        }
        if ($remove_dir) rmdir($dir);
    }
    
    /**
     * 
     * @param $folder string - carpeta
     * @return boolean
     */
    public function isNotEmptyFolder($folder)
    { 
        if (! is_dir($folder)) 
            return false; // not a dir 
    
        $files = opendir($folder); 
        while ($file = readdir($files)) { 
            if ($file != '.' && $file != '..') 
            return true; // not empty 
        } 
    } 
    
    public static function getEncoding($file) {
        return mb_detect_encoding(file_get_contents($file));
    }
    
    /**
     * @return string 
     */
    public function getSeparator($file, $lines = 5)
    {
        
        return (string) $char;
    }
    
    /**
     * 
     */
    public function getResponseFromService($url, $params='', $username=false, $password=false, $log=false, $typeOfResponse=false)
    {
        
        $ch = curl_init();
        if($params!=''){
            $url=$url."?".$params;
        }
        Zwei_Utils_Debug::write($url);
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if($username){
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        }   
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        
        return array(
            "response"=>$response,
            "info"=>$info
        );      
    }   
    
    
    
    public function getRemoteFile($url)
    {
        // get the host name and url path
        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'];
        if (isset($parsedUrl['path'])) {
          $path = $parsedUrl['path'];
        } else {
          // the url is pointing to the host like http://www.mysite.com
          $path = '/';
        }
    
        if (isset($parsedUrl['query'])) {
            $path .= '?' . $parsedUrl['query'];
        }
    
        if (isset($parsedUrl['port'])) {
            $port = $parsedUrl['port'];
        } else {
          // most sites use port 80
            $port = '80';
        }
    
        $timeout = 10;
        $response = '';
    
        // connect to the remote server
        $fp = @fsockopen($host, '80', $errno, $errstr, $timeout );
    
        if( !$fp ) {
            echo "Cannot retrieve $url";
        } else {
          // send the necessary headers to get the file
            fputs($fp, "GET $path HTTP/1.0\r\n" .
                     "Host: $host\r\n" .
                     "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.3) Gecko/20060426 Firefox/1.5.0.3\r\n" .
                     "Accept: */*\r\n" .
                     "Accept-Language: en-us,en;q=0.5\r\n" .
                     "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\n" .
                     "Keep-Alive: 300\r\n" .
                     "Connection: keep-alive\r\n" .
                     "Referer: http://$host\r\n\r\n");
    
          // retrieve the response from the remote server
            while ( $line = fread( $fp, 4096 ) ) {
                $response .= $line;
            }
    
            fclose( $fp );
            // strip the headers
            $pos = strpos($response, "\r\n\r\n");
            $response = substr($response, $pos + 4);
        }
       // return the file content
       return $response;
    }
}
