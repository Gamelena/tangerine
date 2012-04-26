<?php
class Zwei_Utils_File
{
    public function clearRecursive($dir, $remove_dir = false) {
        foreach (glob($dir . '/*') as $file) {
            if(is_dir($file))
                clearRecursive($file);
            else
                unlink($file);
        }
        if ($remove_dir) rmdir($dir);
    }
}
