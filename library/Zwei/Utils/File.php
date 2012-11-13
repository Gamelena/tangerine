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
                clearRecursive($file);
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
    public function isNotEmptyFolder($folder) { 
        if (! is_dir($folder)) 
            return false; // not a dir 
    
        $files = opendir($folder); 
        while ($file = readdir($files)) { 
            if ($file != '.' && $file != '..') 
            return true; // not empty 
        } 
    } 
}
