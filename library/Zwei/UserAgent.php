<?php
/**
 * 
 * Información de agente usuario.
 *
 */
class Zwei_UserAgent
{
    /**
     * Verifica si usuario está usando un dispositivo móvil.
     * 
     * @return number
     */
    public static function isMobile()
    {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }
    
    /**
     * Verifica si si usuario está usando Internet Explorer.
     * 
     * @return number
     */
    public static function isInternetExplorer()
    {
        return preg_match('~MSIE|Trident\/7.0; rv:11\.0|Internet Explorer~i', $_SERVER['HTTP_USER_AGENT']);
    }
}
