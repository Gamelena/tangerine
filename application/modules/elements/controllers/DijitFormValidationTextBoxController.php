<?php

class Elements_DijitFormValidationTextBoxController extends Zend_Controller_Action
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
        
        $this->view->value =  $r->getParam('value', '');
        $this->view->readonly = $r->getParam('readonly') === 'true' || $r->getParam($r->getParam('mode')) == 'readonly' ? "readonly=\"readonly\"" : '';
        $this->view->disabled = $r->getParam('disabled') === 'true' || $r->getParam($r->getParam('mode')) == 'disabled' ? "disabled=\"disabled\"" : '';
        $this->view->required = $r->getParam('required') === 'true' ? "required=\"true\"" : '';
        $this->view->onblur = $r->getParam('onblur') ? "onblur=\"{$r->getParam('onblur')}\"" : '';
        $this->view->style = $r->getParam('onblur') ? "style=\"{$r->getParam('style')}\"" : '';
        $this->view->regExp = $r->getParam('regExp') ? "pattern=\"{$r->getParam('regExp')}\"" : '';
        $this->view->onchange = $r->getParam('onchange') ? "onchange=\"{$r->getParam('onchange')}\"" : '';
        $this->view->onclick = $r->getParam('onclick') ? "onclick=\"{$r->getParam('onclick')}\"" : '';
        $this->view->placeHolder = $r->getParam('placeHolder') ? "placeHolder=\"{$r->getParam('placeHolder')}\"" : '';
        $this->view->invalidMessage = $r->getParam('invalidMessage') ? "invalidMessage=\"{$r->getParam('invalidMessage')}\"" : '';
        $this->view->promptMessage= $r->getParam('promptMessage') ? "promptMessage=\"{$r->getParam('promptMessage')}\"" : '';
        $this->view->maxlength = $r->getParam('maxlength')? "maxLength=\"{$r->getParam('maxlength')}\"" : '';
        $this->view->trim = $r->getParam('trim', '') === 'true' ? "trim=\"true\"" : '';
    }
}

