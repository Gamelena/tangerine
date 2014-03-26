<?php
/**
 * 
 * Información de agente usuario.
 *
 */
class Zwei_UserAgent
{
    /**
     * Verifica si usuario está usando un dispositivo mobil.
     * 
     * @return number
     */
    static function isMobile()
    {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }
}