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
     * @param $dir string - ruta 
     * @param $remove_dir
     * @return unknown_type
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
