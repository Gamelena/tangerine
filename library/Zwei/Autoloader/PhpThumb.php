<?php
class Zwei_Autoloader_PhpThumb {
    static protected $_phpThumbClasses = array(
            'PhpThumb'        => 'PhpThumb.inc.php',
            'ThumbBase'       => 'ThumbBase.inc.php',
            'PhpThumbFactory' => 'ThumbLib.inc.php',
            'GdThumb'         => 'GdThumb.inc.php',
            'GdReflectionLib' => 'thumb_plugins/gd_reflection.inc.php',
    );
    
    /**
     * Autoload a class
     *
     * @param   string $class
     * @return  mixed
     *          False [if unable to load $class]
     *          get_class($class) [if $class is successfully loaded]
    */
    public function autoload($class) {
        $file = APPLICATION_PATH . '/../library/PhpThumb/' . self::$_phpThumbClasses[$class];
        if (is_file($file)) {
            require_once($file);
            return $class;
        }
        return false;
    }
}