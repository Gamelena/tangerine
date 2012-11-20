<?php
/**
 * 
 * Plugin para cachear ciertas páginas estáticas de AdmPortal.
 *
 */
final class Zwei_Controller_Plugin_Cache extends Zend_Controller_Plugin_Abstract
{
    /**
     *  @var boolean
     */
    public static $doNotCache = false;
    
    /**
     * @var Zend_Cache_Frontend
     */
    public $cache;
    
    /**
     * @var string Cache key
     */
    public $key;
    
    /**
     * Constructor: inicializar cache
     * 
     * @param  array|Zend_Config $options 
     * @return void
     * @throws Exception
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Config && isset($options->resources->cacheManager->page) && !empty($options->resources->cacheManager->page->backend->options->cache_dir)) {
            $options = $options->resources->cacheManager->page->toArray();
        } else {
            Debug::write("No hay valores para inicializar cache");
            return;
        }
        
        $options['frontend']['options']['automatic_serialization'] = true;
        
        $this->cache = Zend_Cache::factory(
            'Output',
            'File',
            $options['frontend']['options'],
            $options['backend']['options']
        );
    }
    
    /**
     * Start caching
     *
     * Determinar si corresponde o no usar cache y que llave de cache usar.
     * 
     * @param  Zend_Controller_Request_Abstract $request 
     * @return void
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        if (!Zend_Auth::getInstance()->hasIdentity() 
            || isset($_REQUEST['action']) 
            || preg_match("/grafico/", $request->getParam('p'))
            || preg_match("/settings/", $request->getParam('p'))
            || preg_match("/nocache/", $request->getParam('p'))
            ) {
            self::$doNotCache = true;
        } else {
            $userInfo = Zend_Auth::getInstance()->getStorage()->read();
            
            if ($request->getControllerName() == "index" && $request->getActionName() == "components") {
                $this->key = md5($request->getPathInfo().$request->getParam("p")) . "acl_roles_id_".$userInfo->acl_roles_id;
            } else if ($request->getControllerName() == "objects" && $request->getParam('model') == "settings" && $request->getParam('format') == "json") {
                $this->key = $userInfo->acl_roles_id."_settings"; 
            } else if ($request->getControllerName() == "index" && $request->getActionName() == "modules") {
                $this->key = md5($userInfo->acl_roles_id."_modules"); 
            } else {
                self::$doNotCache = true;
                return;
            }
            
            if (false !== ($response = $this->getCache())) {
                $response->sendResponse();
                exit;
            }
        }    
    }
    
    
    public function getCache()
    {
        if (isset($this->cache) && ($response = $this->cache->load($this->key)) != false) {
            Debug::write("Trayendo de cache ".@$_SERVER['REQUEST_URI']);
            return $response;
        }
        return false;
    }
    
    /**
     * Guardar cache
     * 
     * @return void
     */
    public function dispatchLoopShutdown()
    {
        if (self::$doNotCache
            || $this->getResponse()->isRedirect()
            || (null === $this->key)
        ) {
            
            return;
        } else if ($this->cache) {
            $this->cache->save($this->getResponse(), $this->key);
        }    
    }
}