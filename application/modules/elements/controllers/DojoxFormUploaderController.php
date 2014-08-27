<?php

class Elements_DojoxFormUploaderController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $r = $this->getRequest();

        $this->view->i =  $r->getParam('i');
        $this->view->domId =  $r->getParam('domId');
        $this->view->target =  $r->getParam('target');
        $this->view->formatter =  $r->getParam('formatter', '');
        $this->view->thumbs =  $r->getParam('thumbs', array());
        
        $this->view->value =  $r->getParam('value', '');
        $this->view->readonly = $r->getParam('readonly', '') === 'true' ? "readonly=\"readonly\"" : '';
        $this->view->disabled = $r->getParam('disabled', '') === 'true' ? "disabled=\"disabled\"" : '';
        $this->view->required = $r->getParam('required', '') === 'true' ? "required=\"true\"" : '';
        $this->view->url = $r->getParam('url') ? "url=\"{$r->getParam('url')}\"" : 'url=""';
        $this->view->baseUrlPath = $r->getParam('url') ? $r->getParam('url') : BASE_URL . '/upfiles/';
        
        if (preg_match("/^\{BASE_URL\}(.*)$/", $r->getParam('url'), $matches)) {
            $this->view->url = "url=\"".BASE_URL . $matches[1]."\"";
            $this->view->baseUrlPath = BASE_URL . $matches[1];
        }
        
        $this->view->path = $r->getParam('path');
        
        if (preg_match("/^\{ROOT_DIR\}(.*)$/", $this->view->path, $matches)) {
            $this->view->path = ROOT_DIR . $matches[1];
        } else if (preg_match("/^\{APPLICATION_PATH\}(.*)$/", $this->view->path, $matches)) {
            $this->view->path = APPLICATION_PATH . $matches[1];
        } 
        
        $this->view->onblur = $r->getParam('onblur') ? "onblur=\"{$r->getParam('onblur')}\"" : '';
        $this->view->invalidMessage = $r->getParam('invalidMessage') ? "invalidMessage=\"{$r->getParam('invalidMessage')}\"" : '';
        $this->view->promptMessage= $r->getParam('promptMessage') ? "promptMessage=\"{$r->getParam('promptMessage')}\"" : '';
    }
}

