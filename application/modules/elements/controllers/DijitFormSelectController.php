<?php

class Elements_DijitFormSelectController extends Elements_BaseController
{
    public function indexAction()
    {
        $r = $this->getRequest();
        
        $this->view->data = $r->getParam('data', false);
        $this->view->mode = $r->getParam('mode');
        $this->view->trim = $r->getParam('trim') ? "trim=\"{$r->getParam('trim')}\"" : '';
        $this->view->regExp = $r->getParam('regExp') ? "regExp=\"{$r->getParam('regExp')}\"" : '';
        $this->view->label = $r->getParam('label') ? "label=\"{$r->getParam('label')}\"" : '';
        $this->view->onload = $r->getParam('onload', '');
        
        if (preg_match("/^\{BASE_URL\}(.*)$/", $r->getParam('label'), $matches)) {
            $this->view->label = "url=\"".BASE_URL . $matches[1]."\"";
        }
        
        $this->view->invalidMessage = $r->getParam('invalidMessage') ? "invalidMessage=\"{$r->getParam('invalidMessage')}\"" : '';
        $this->view->promptMessage = $r->getParam('promptMessage') ? "promptMessage=\"{$r->getParam('promptMessage')}\"" : '';
        $this->view->placeHolder = $r->getParam('placeHolder') ? "placeHolder=\"{$r->getParam('placeHolder')}\"" : '';
        
        if ($r->getParam('data') && $r->getParam('mode') == 'add' && $r->getParam('defaultValue') !== false) {
            $this->view->value =  $r->getParam('defaultValue');
        } else if ($r->getParam('data') && $r->getParam('value') !== false) {
            $this->view->value = $r->getParam('value');
        } else {
            $this->view->value =  $r->getParam('defaultValue') && !$r->getParam('defaultText') ? $r->getParam('defaultValue') : '';
        }
        
        $this->view->options = !$this->view->data ? $this->options() : false;
    }
}

