<?php

class Elements_ZweiFormFilteringJsonSelectController extends Zend_Controller_Action
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
        
        $this->view->formatter = $r->getParam('formatter', false);
        $this->view->data = $r->getParam('data', false);
        $this->view->mode = $r->getParam('mode');
        $this->view->url = $r->getParam('url');
        $this->view->style = $r->getParam('style') ? " style=\"{$r->getParam('style')}\"" : "";
        
        //La RegExp busca constantes declaradas entre llaves en atributo xml "path"
        //ej {BASE_URL}/myupfiles
        if (preg_match("/^\{(.*)\}(.*)$/", $r->getParam('url'), $matches)) {
            $this->view->url = constant($matches[1]) . $matches[2];
        }
        
        $this->view->trim = $r->getParam('trim') ? "trim=\"{$r->getParam('trim')}\"" : '';
        $this->view->readonly = $r->getParam('readonly') === 'true' || $r->getParam($r->getParam('mode')) == 'readonly' ? " readonly=\"readonly\"" : '';
        $this->view->disabled = $r->getParam('disabled') === 'true' || $r->getParam($r->getParam('mode')) == 'disabled' ? " disabled=\"disabled\"" : '';
        $this->view->required = $r->getParam('required', '') === 'true' ? "required=\"true\"" : '';
        $this->view->onblur = $r->getParam('onblur') ? " onblur=\"{$r->getParam('onblur')}\"" : '';
        $this->view->regExp = $r->getParam('regExp') ? " regExp=\"{$r->getParam('regExp')}\"" : '';
        $this->view->label = $r->getParam('label') ? " label=\"{$r->getParam('label')}\"" : '';
        
        $formatter = (!$this->view->data && $this->view->formatter && $this->view->formatter != 'formatimage') ?
            "console.log('onchange');dijit.byId('{$this->view->domId}{$this->view->i}').textbox.value={$this->view->formatter}(dijit.byId('{$this->view->domId}{$this->view->i}').get('value'));" : '';
        
        $this->view->onchange = $r->getParam('onchange') ? $formatter.$r->getParam('onchange') : $formatter;
        
        $this->view->onclick = $r->getParam('onclick') ? " onclick=\"{$r->getParam('onclick')}\"" : '';
        $this->view->onkeypress = $r->getParam('onkeypress') ? " onkeypress=\"{$r->getParam('onkeypress')}\"" : '';
        $this->view->onshow = $r->getParam('onshow') ? " onShow=\"{$r->getParam('onshow')}\"" : '';
        $this->view->onload = $r->getParam('onload', '');
        
        
        $this->view->invalidMessage = $r->getParam('invalidMessage') ? " invalidMessage=\"{$r->getParam('invalidMessage')}\"" : '';
        $this->view->missingMessage = $r->getParam('missingMessage') ? "missingMessage=\"{$r->getParam('missingMessage')}\"" : '';
        $this->view->promptMessage= $r->getParam('promptMessage') ? " promptMessage=\"{$r->getParam('promptMessage')}\"" : '';
        $this->view->placeHolder = $r->getParam('placeHolder') ? " placeHolder=\"{$r->getParam('placeHolder')}\"" : '';
        
        if ($r->getParam('data') && in_array($r->getParam('mode'), array('add', null)) && $r->getParam('defaultValue') !== false) {
            $this->view->value =  $r->getParam('defaultValue');
        } else if ($r->getParam('data') && $r->getParam('value') !== false) {
            $this->view->value = $r->getParam('value');
        } else {
            $this->view->value =  $r->getParam('defaultValue') && !$r->getParam('defaultText') ? $r->getParam('defaultValue') : '';
        }
    }
}

