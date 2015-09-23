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
        $dataDojoProps = array();
        
        $this->view->i =  $r->getParam('i');
        $this->view->domId =  $r->getParam('domId');
        $this->view->target =  $r->getParam('target');
        
        $this->view->formatter = $r->getParam('formatter', false);
        $this->view->data = $r->getParam('data', false);
        $this->view->mode = $r->getParam('mode');
        $this->view->style = $r->getParam('style') ? " style=\"{$r->getParam('style')}\"" : "";
        
        //La RegExp busca constantes declaradas entre llaves en atributo xml "path"
        //ej {BASE_URL}/myupfiles
        
        if (preg_match("/^\{(.*)\}(.*)$/", $r->getParam('url'), $matches)) {
            $dataDojoProps[] = "url:'". constant($matches[1]) . $matches[2] ."'";
        } else {
            $dataDojoProps[] = "url:'{$r->getParam('url')}'";
        }
        
        if ($r->getParam("unbounded")) $dataDojoProps[] = "unbounded:{$r->getParam('unbounded')}";
        if ($r->getParam('maximum')) $dataDojoProps[] = "maximum:{$r->getParam('maximum')}";
        if ($r->getParam('value') !== '') $dataDojoProps[] = "value:'{$r->getParam('value')}'";
        
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
        
        $this->view->value = $r->getParam('value');

        Console::debug([$r->getParams(), $this->view->value]);
        
        $this->view->dataDojoProps = implode(",", $dataDojoProps);
    }
}

