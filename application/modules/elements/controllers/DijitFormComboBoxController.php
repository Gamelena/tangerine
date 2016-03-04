<?php

class Elements_DijitFormComboBoxController extends Elements_BaseController
{
    public function indexAction()
    {
        $r = $this->getRequest();
        
        $this->view->formatter = $r->getParam('formatter', false);
        $this->view->data = $r->getParam('data', false);
        $this->view->mode = $r->getParam('mode');
        $this->view->label = $r->getParam('label') ? "label=\"{$r->getParam('label')}\"" : '';
        
        $formatter = (!$this->view->data && $this->view->formatter && $this->view->formatter != 'formatimage') ?
            "console.log('onchange');dijit.byId('{$this->view->domId}{$this->view->i}').textbox.value={$this->view->formatter}(dijit.byId('{$this->view->domId}{$this->view->i}').get('value'));" : '';
        
        $this->view->onchange = $r->getParam('onchange') ? $formatter.$r->getParam('onchange') : $formatter;
        $this->view->onshow = $r->getParam('onshow') ? "onShow=\"{$r->getParam('onshow')}\"" : '';
        $this->view->onload = $r->getParam('onload', '');
        
        if (preg_match("/^\{BASE_URL\}(.*)$/", $r->getParam('label'), $matches)) {
            $this->view->label = "url=\"".BASE_URL . $matches[1]."\"";
        }
        
        if ($r->getParam('data') && $r->getParam('mode') == 'add' && $r->getParam('defaultValue') !== false) {
            $this->view->value =  $r->getParam('defaultValue');
        } else if ($r->getParam('value') !== false) {
            $this->view->value = $r->getParam('value');
        } else {
            $this->view->value =  $r->getParam('defaultValue') && !$r->getParam('defaultText') ? $r->getParam('defaultValue') : '';
        }
        
        $this->view->value=str_replace("'", "\'", $this->view->value);
        
        $this->view->options = !$this->view->data ? $this->options() : false;
    }
}

