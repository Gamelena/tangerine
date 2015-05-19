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
        $this->view->baseUrlPath = $r->getParam('url') ? $r->getParam('url') : BASE_URL . '/upfiles/';
        
        //La RegExp busca constantes declaradas entre llaves en atributo xml "path"
        //ej {BASE_URL}/myupfiles
        if (preg_match("/^\{(.*)\}(.*)$/", $r->getParam('url'), $matches)) {
            $this->view->baseUrlPath = constant($matches[1]) . $matches[2];
        }
        
        $this->view->path = $r->getParam('path');
        
        if (preg_match("/^\{(.*)\}(.*)$/", $this->view->path, $matches)) {
            $this->view->path = constant($matches[1]) . $matches[2];
        }
        
        $this->view->onblur = $r->getParam('onblur') ? "onblur=\"{$r->getParam('onblur')}\"" : '';
        $this->view->invalidMessage = $r->getParam('invalidMessage') ? "invalidMessage=\"{$r->getParam('invalidMessage')}\"" : '';
        $this->view->promptMessage= $r->getParam('promptMessage') ? "promptMessage=\"{$r->getParam('promptMessage')}\"" : '';
    }
}

