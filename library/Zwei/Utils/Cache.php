<?php
/**
 * Implementación de Zend_Cache para admportal,
 * tip: para no cachear graficos, usar la palabra "grafico" en el nombre del componente XML
 *
 * @package Zwei_Utils
 * @version $Id:$
 * @since 0.1
 * @deprecated se recomienda usar Zwei_Controller_Plugin_Cache() en su lugar.
 */
final class Zwei_Utils_Cache
{
    /**
     * 
     * @var array
     */
    protected $_backendOpt;
    /**
     * 
     * @var array
     */
    protected $_frontendOpt;
    
    /**
     * 
     * @var Zend_Cache_Output
     */
    protected $_cache;
    /**
     * 
     * @var boolean
     */
    protected $_isStarted = false;
    /**
     * 
     * @var boolean
     */
    protected $_check = false;
    
    /**
     * 
     * @param $backendOpt array - opciones de BackEnd para Zend_Cache::factory 
     * @param $frontendOpt array - opciones de FrontEnd para Zend_Cache::factory 
     * @return void
     */
    public function __construct($backendOpt, $frontendOpt) 
    {
        $this->_backendOpt = $backendOpt;
        $this->_frontendOpt = $frontendOpt;
    }

    /**
     * Inicializa el output de caché.
     * @return void
     */
    public function start() 
    {
        if (Zend_Auth::getInstance()->hasIdentity() 
            && !isset($_REQUEST['action']) 
            && !preg_match("/grafico/", @$_REQUEST['p'])
            && !preg_match("/settings/", @$_REQUEST['p'])
            && !preg_match("/nocache/", @$_REQUEST['p'])
            ) {
            $userInfo = Zend_Auth::getInstance()->getStorage()->read();
            $pathInfo = isset($_SERVER['PATH_INFO']) ? isset($_SERVER['PATH_INFO']) : '';
            if ($pathInfo == "/components" && !preg_match("/grafico/", @$_REQUEST['p']))
            {
                $this->_check = true;
                $userInfo = Zend_Auth::getInstance()->getStorage()->read();
                $cacheid = md5($userInfo->acl_roles_id.@$_SERVER['PATH_INFO'].@$_REQUEST['p']); 
                // make object
                $this->_cache = Zend_Cache::factory('Output', 'File', $this->_frontendOpt, $this->_backendOpt);
                $this->_isStarted = $this->_cache->start($cacheid);
            } else if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] == "model=settings&format=json") {
                $cacheid = $userInfo->acl_roles_id."_settings"; 
                
                $this->_check = true;
                $this->_cache = Zend_Cache::factory('Output', 'File', $this->_frontendOpt, $this->_backendOpt);
                $this->_isStarted = $this->_cache->start($cacheid);
            } else if ($pathInfo == "/modules") {
                $cacheid = md5($userInfo->acl_roles_id."_modules"); 
                
                $this->_check = true;
                $this->_cache = Zend_Cache::factory('Output', 'File', $this->_frontendOpt, $this->_backendOpt);
                $this->_isStarted = $this->_cache->start($cacheid);             
            }
        } 
        
    }
    
    /**
     * Checkea si debe inicializar el caché según los parámetros del constructor.
     * @return boolean
     */
    public function check()
    {
        return $this->_check;
    }

    /**
     * Checkea si el output del caché está inicializado.
     * @return boolean
     */    
    public function isStarted()
    {
        return $this->_isStarted;
    }
    
    /**
     * Finaliza la escritura en caché.
     * @return void
     */
    public function end()
    {
        $this->_cache->end();
    }
}
