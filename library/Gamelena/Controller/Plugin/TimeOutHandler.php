<?php
/**
 * Plugin para controlar el timeout de sesión
 */

class Gamelena_Controller_Plugin_TimeOutHandler extends Zend_Controller_Plugin_Abstract
{
    /**
     * 
     * @var Zend_Config
     */
    private $_timeout = 2400;
    
    /**
     * 
     * @param Zend_Config $config
     */
    public function __construct($config = null)
    {
        if ($config && isset($config->gamelena->session->timeout)) {
            $this->_timeout = $config->gamelena->session->timeout;
        }
    }
    
    /**
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     * @see Zend_Controller_Plugin_Abstract::preDispatch()
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request) 
    {
        $authNamespace = new Zend_Session_Namespace('Zend_Auth');
        
        // clear the identity of a user who has not accessed a controller for
        // longer than a timeout period.
        if (isset($authNamespace->timeout) && time() > $authNamespace->timeout && $request->getActionName() != "login") {
            Zend_Auth::getInstance()->clearIdentity();
        } else if ($request->getControllerName() != 'events' || $request->getModuleName() != 'default') {
            // User is still active - update the timeout time.
            $authNamespace->timeout = time() + $this->_timeout;
            // Store the request URI so that an authentication after a timeout
            // can be directed back to the pre-timeout display.  The base URL needs to
            // be stripped off of the request URI to function properly.
            $authNamespace->requestUri = substr(
                $this->_request->getRequestUri(),
                strlen(Zend_Controller_Front::getInstance()->getBaseUrl())
            );
        }
    }   
}
