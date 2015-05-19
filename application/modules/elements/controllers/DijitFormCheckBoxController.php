<?php

class Elements_DijitFormCheckBoxController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $r = $this->getRequest();
        $this->view->i = $r->getParam('i');
        $this->view->domId = $r->getParam('domId');
        $this->view->target = $r->getParam('target');
        
        $this->view->defaultValue = $r->getParam('defaultValue', '1');
        $this->view->uncheckedValue = $r->getParam('uncheckedValue', '0');
        $this->view->checked = $r->getParam('value') == $this->view->defaultValue ? " checked=\"checked\"" : "";
        if ($r->getParam('checked') && $r->getParam("mode") === 'add') $this->view->checked = " checked=\"checked\"";
        $this->view->readonly = $r->getParam('readonly') === 'true' || $r->getParam($r->getParam('mode')) == 'readonly' ? " readonly=\"readonly\"" : '';
        $this->view->disabled = $r->getParam('disabled') === 'true' || $r->getParam($r->getParam('mode')) == 'disabled' ? " disabled=\"disabled\"" : '';
        $this->view->onchange = $r->getParam('onchange', '');
        $this->view->onclick = $r->getParam('onclick') ? "onclick=\"{$r->getParam('onclick')}\"" : '';
    }
}

