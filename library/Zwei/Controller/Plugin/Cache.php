<?php
/**
 * Implementación de Zend_Cache para admportal como Plugin,
 * tip: para no cachear algun componente XML, usar la palabra "nocache" en el nombre de archivo, ej trafico-por-nodo-nocache.xml
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
     * Etiquetas para agrupar archivos de cache
     * @var string
     */
    public $tags;
    /**
     * Constructor: inicializar cache
     * 
     * @param  array|Zend_Config $options 
     * @return Zend_Cache_Frontend
     * @throws Exception
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Config && isset($options->resources->cacheManager->page) && !empty($options->resources->cacheManager->page->backend->options->cache_dir)) {
            $options = $options->resources->cacheManager->page->toArray();
        } else {
            //Console::debug("No hay valores para inicializar cache");
            return;
        }
        
        $options['frontend']['options']['automatic_serialization'] = true;
        
        try {
            $this->cache = Zend_Cache::factory(
                'Output',
                'File',
                $options['frontend']['options'],
                $options['backend']['options']
            );
        } catch (Zend_Cache_Exception $e) {
            $message = $e->getMessage();
            if (stristr($e->getMessage(), 'cache_dir is not writable')) {
                $path = realpath($options['backend']['options']['cache_dir']);
                $message .= ". Se necesitan permisos de escritura en '$path'";
            }
            throw new Zend_Cache_Exception($message);
        }
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
            || preg_match("/settings/", $request->getParam('p'))
            || preg_match("/nocache/", $request->getParam('p'))
        ) {
            self::$doNotCache = true;
        } else {
            $userInfo = Zend_Auth::getInstance()->getStorage()->read();
            //Dependiendo el usuario pertenece o no a grupos se debe serializar el cache por id de usuario o id de perfil.
            $cacheUserRoleHash = !empty($userInfo->groups) ? "userid{$userInfo->id}" : "roleid{$userInfo->acl_roles_id}";
            //$cacheUserRoleHash = empty($userInfo->groups) ? "roleid{$userInfo->acl_roles_id}" : "roleid{$userInfo->acl_roles_id}-groupsids" . implode("_", $userInfo->groups);
            
            if ($request->getControllerName() == "admin" && $request->getActionName() == "components") {
                $this->key = md5(BASE_URL . $request->getPathInfo() . $request->getParam("p")) . $cacheUserRoleHash;
            } else if ($request->getControllerName() == "objects" && $request->getParam('model') == "settings" && $request->getParam('format') == "json") {
                $this->key = md5(BASE_URL . "_settings"); 
            } else if ($request->getControllerName() == "admin" && $request->getActionName() == "modules") {
                $this->key = md5(BASE_URL . "_modules" . $cacheUserRoleHash); 
            } else {
                self::$doNotCache = true;
                return;
            }
            
            $this->tags[] = "userid{$userInfo->id}";
            $this->tags[] = "roleid{$userInfo->acl_roles_id}";
            $this->tags[] = "component" . Zwei_Utils_String::toVarWord($request->getParam("p"));
            
            if (false !== ($response = $this->getCache())) {
                $response->sendResponse();
                if (PHP_SAPI !== 'cli') { //exit mata silenciosamente a phpunit.
                    exit();
                }
            }
        }
    }
    
    /**
     * 
     * @return boolean
     */
    public function getCache()
    {
        if (isset($this->cache) && ($response = $this->cache->load($this->key)) != false) {
            //Debug::write("Trayendo de cache ".@$_SERVER['REQUEST_URI']);
            return $response;
        }
        return false;
    }
    
    /**
     * Borrar cache por tags
     * 
     * @param array $tags
     */
    public function cleanByTags($tags)
    {
        if (!$this->cache) { return false; 
        }
        
        return $this->cache->clean(
            Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            $tags
        );
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
            $this->cache->save($this->getResponse(), $this->key, $this->tags);
        }    
    }
}