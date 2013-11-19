<?php
/**
 * Funcionalidades de configuracion Zend_Config del sitio
 * 
 *
 */
class Zwei_Controller_Config
{
    /**
     * Obtiene la configuracion cargada via bootstrap
     * @return Zend_Config
     */
    public static function get(){
        $configParams = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getApplication()->getOptions();
        return new Zend_Config($configParams);
    }
}