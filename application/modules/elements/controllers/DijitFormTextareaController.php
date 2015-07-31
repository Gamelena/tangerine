<?php

class Elements_DijitFormTextareaController extends Zend_Controller_Action
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
        $this->view->readonly = $r->getParam('readonly', '') === 'true' ? " readonly=\"readonly\"" : '';
        $this->view->disabled = $r->getParam('disabled', '') === 'true' ? " disabled=\"disabled\"" : '';
        
        $this->view->autocomplete = $r->getParam('autocomplete') ? " autocomplete=\"{$r->getParam('autocomplete')}\"" : '';
        $this->view->autocorrect = $r->getParam('autocorrect') ? " autocorrect=\"{$r->getParam('autocorrect')}\"" : '';
		$this->view->autocapitalize = $r->getParam('autocapitalize') ? " autocapitalize=\"{$r->getParam('autocapitalize')}\"" : '';
        $this->view->spellcheck = $r->getParam('spellcheck') ? " spellcheck=\"{$r->getParam('spellcheck')}\"" : '';
        
        $this->view->onblur = $r->getParam('onblur') ? "onblur=\"{$r->getParam('onblur')}\"" : '';
        $this->view->style = $r->getParam('style') ? "style=\"{$r->getParam('style')}\"" : '';
    }
}

