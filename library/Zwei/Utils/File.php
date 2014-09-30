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
     * @return boolean
     */
    public function clearRecursive($dir, $remove_dir = false, $exclude = array()) {
        $exclude = (array) $exclude;
        $return = false;
        foreach (glob($dir . '/*') as $file) {
            if(is_dir($file)) {
                $return = Zwei_Utils_File::clearRecursive($file);
            } else if (!in_array($file, $exclude)) {
                $return = unlink($file);
            }
        }
        if ($remove_dir) $return = rmdir($dir);
        return $return;
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
    
    public static function getEncoding($filename) {
        return mb_detect_encoding(file_get_contents($filename));
    }
    
    /**
     * 
     * @param string $filename
     * @param int $lines
     * @return string 
     */
    public function getSeparator($filename, $linesToReview = 5)
    {
        $handle = fopen($filename, 'r');
        $tabs = array();
        $commas = array();
        $semicolons = array();
        $separator = ',';
        
        $i = 0;
        while ($line = fgets($handle)) {
            $tabs[] = count(explode("\t", $line));
            $semicolons[] = count(explode(";", $line));
            $i++;
            if ($i >= $linesToReview) break;
        }
        
        if ($tabs[0] > 1) {
            $i = 0;
            foreach ($tabs as $v) {
                if ($i == 0) {
                    $char = $v;
                } else {
                    if ($v != $char) {
                        break;
                    } else {
                        $char = $v;
                        $separator = "\t";
                    }
                }
                $i++;
            }
        }
        
        if ($semicolons[0] > 1) {
            $i = 0;
            foreach ($semicolons as $v) {
                if ($i == 0) {
                    $char = $v;
                } else {
                    if ($v != $char) {
                        break;
                    } else {
                        $char = $v;
                        $separator = ";";
                    }
                }
                $i++;
            }   
        }
        
        return $separator;
    }
    
    /**
     * Obtener extensión de nombre de archivo
     * @param string $filename
     */
    public function getExtension($filename)
    {
        $fp = explode(".", $filename);
        return $fp[count($fp) - 1];
    }
    
    /**
     * Invocar URL via CURL
     * 
     * @param string $url
     * @param string $params
     * @param string $username
     * @param string $password
     * @param string $method GET|POST|DELETE|HEAD
     */
    static public function getResponseFromService($url, $params = null, $username = null, $password = null, $method = 'GET')
    {
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        if ($params) {
            if ($method !== 'POST') {
                $url = $url."?".$params;
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            }
        } 
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($username) {
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
