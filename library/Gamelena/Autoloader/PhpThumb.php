<?php
class Gamelena_Autoloader_PhpThumb implements Zend_Loader_Autoloader_Interface
{
    static protected $php_thumb_classes = array(
          'PhpThumb'        => 'PhpThumb.inc.php',
          'ThumbBase'       => 'ThumbBase.inc.php',
          'PhpThumbFactory' => 'ThumbLib.inc.php',
          'GdThumb'         => 'GdThumb.inc.php',
          'GdReflectionLib' => 'thumb_plugins/gd_reflection.inc.php',
       );

    /**
   * Autoload a class
   *
   * @param  string $class
   * @return mixed
   *          False [if unable to load $class]
   *          get_class($class) [if $class is successfully loaded]
   */
    public function autoload($class)
    {
        $file = TANGERINE_APPLICATION_PATH . '/../library/PhpThumb/' . self::$php_thumb_classes[$class];
        if (is_file($file)) {
            include_once $file;
            return $class;
        }
        return false;
    }
}