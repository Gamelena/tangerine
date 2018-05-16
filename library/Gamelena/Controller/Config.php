<?php
/**
 * Funcionalidades de configuracion Zend_Config del sitio
 */
class Gamelena_Controller_Config
{
    /**
     * Bootstrap
     * 
     * @return Zend_Application_Bootstrap_Bootstrap
     */
    public static function getBootstrap()
    {
        return Zend_Controller_Front::getInstance()->getParam("bootstrap");
    }
    /**
     * Obtiene la configuracion cargada via bootstrap
     * @return Zend_Config
     */
    public static function getOptions()
    {
        $configParams = self::getBootstrap()->getApplication()->getOptions();
        return new Zend_Config($configParams);
    }
    
    /**
     * @param string $resource
     * @return Zend_Application_Resource
     */
    public static function getResource($resource)
    {
        return Zend_Controller_Front::getInstance()->getParam("bootstrap")->getResource($resource);
    }
    
    /**
     * @return Zend_Application_Resource_Multidb
     */
    public static function getResourceMultiDb()
    {
        return self::getResource('multidb');
    }
    
    /**
     * @return Zend_Application_Resource_Db
     */
    public static function getResourceDb()
    {
        return self::getResource('db');
    }
    
    /**
     * @return Zend_Application_Resource_Dojo
     */
    public static function getResourceDojo()
    {
        return self::getResource('dojo');
    }
    
    /**
     * @return Zend_Application_Resource_Mail
     */
    public static function getResourceMail()
    {
        return self::getResource('mail');
    }
    
    /**
     * @return Zend_Application_Resource_Session
     */
    public static function getResourceSession()
    {
        return self::getResource('session');
    }
}