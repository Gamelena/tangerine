<?php
/**
 * Muestra información del servidor mediante phpinfo() para desarrollo y soporte.
 *
 * Ejemplo:
 * <code>
 * <section name="Configuraci&amp;oacute; del Sistema" type="php_info" />
 * </code>
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Components
 * @version $Id:$
 * @since 0.1
 *
 */

class Zwei_Admin_Components_PhpInfo implements Zwei_Admin_ComponentsInterface
{
    public $page;
    
    function __construct($page) 
    {
    	$this->page=$page;
    }
    
    
    function display()
    {
        $out = "<h2>Configuraci&oacute;n del Servidor</h2>\r\n";
        
        ob_start();
        phpinfo(INFO_GENERAL);
        $phpinfo = preg_replace('#<!DOCTYPE.+?<body>#is','',ob_get_clean());
        $out .= str_replace('</body></html>','',$phpinfo);
        
        ob_start();
        phpinfo(INFO_CONFIGURATION);
        $phpinfo = preg_replace('#<!DOCTYPE.+?<body>#is','',ob_get_clean());
        $out .= str_replace('</body></html>','',$phpinfo);
        return $out;
    }
}
