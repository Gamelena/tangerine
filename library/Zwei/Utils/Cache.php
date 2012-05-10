<?php
/**
 * Implementación de Zend_Cache para admportal,
 * tip: para no cachear graficos, usar la palabra "grafico" en el nombre del componente
 *
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
	
    public function __construct($backendOpt, $frontendOpt) 
    {
    	$this->_backendOpt = $backendOpt;
    	$this->_frontendOpt = $frontendOpt;
    }

    public function start() 
    {
    	Debug::write("start");
		if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] == "/components" 
		  && Zend_Auth::getInstance()->hasIdentity() 
		  && !preg_match("/grafico/", @$_REQUEST['p'])) {
		  	Debug::write($_SERVER['PATH_INFO']);
		  	$this->_check = true;
		    $userInfo = Zend_Auth::getInstance()->getStorage()->read();
		    $cacheid = md5( $userInfo->acl_roles_id.@$_REQUEST['p'] ); 
		    // make object
		    $this->_cache = Zend_Cache::factory('Output',
		                                 'File',
		                                 $this->_frontendOpt,
		                                 $this->_backendOpt);
		
		    $this->_isStarted = $this->_cache->start($cacheid);
		} 
    	
    }
    
    public function check()
    {
    	return $this->_check;
    }
    
    public function isStarted()
    {
    	return $this->_isStarted;
    }
    
    public function end()
    {
    	$this->_cache->end();
    }
}
