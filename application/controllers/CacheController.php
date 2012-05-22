<?php

class CacheController extends Zend_Controller_Action
{

    public function init()
    {
        if (!Zend_Auth::getInstance()->hasIdentity()) $this->_redirect('index/login');
        $this->_helper->layout()->disableLayout();
    }

    public function indexAction()
    {
        // action body
    }

    public function clearAction()
    {
    	Zwei_Utils_File::clearRecursive(ROOT_DIR ."/cache");
    }
    

}

