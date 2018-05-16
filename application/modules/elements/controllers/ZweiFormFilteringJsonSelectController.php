<?php

class Elements_GamelenaFormFilteringJsonSelectController extends Elements_BaseController
{
    public function indexAction()
    {
        $r = $this->getRequest();
        $dataDojoProps = array();
        
        
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
        
        if ($r->getParam("unbounded")) { $dataDojoProps[] = "unbounded:{$r->getParam('unbounded')}"; 
        }
        if ($r->getParam('maximum')) { $dataDojoProps[] = "maximum:{$r->getParam('maximum')}"; 
        }
        if ($r->getParam('value') !== '') { $dataDojoProps[] = "value:'{$r->getParam('value')}'"; 
        }
        
        $this->view->regExp = $r->getParam('regExp') ? " regExp=\"{$r->getParam('regExp')}\"" : '';
        $this->view->label = $r->getParam('label') ? " label=\"{$r->getParam('label')}\"" : '';
        
        $formatter = (!$this->view->data && $this->view->formatter && $this->view->formatter != 'formatimage') ?
            "console.log('onchange');dijit.byId('{$this->view->domId}{$this->view->i}').textbox.value={$this->view->formatter}(dijit.byId('{$this->view->domId}{$this->view->i}').get('value'));" : '';
        
        $this->view->onchange = $r->getParam('onchange') ? $formatter.$r->getParam('onchange') : $formatter;
        
        $this->view->onkeypress = $r->getParam('onkeypress') ? " onkeypress=\"{$r->getParam('onkeypress')}\"" : '';
        $this->view->onshow = $r->getParam('onshow') ? " onShow=\"{$r->getParam('onshow')}\"" : '';
        $this->view->onload = $r->getParam('onload', '');
        
        
        $this->view->invalidMessage = $r->getParam('invalidMessage') ? " invalidMessage=\"{$r->getParam('invalidMessage')}\"" : '';
        $this->view->missingMessage = $r->getParam('missingMessage') ? "missingMessage=\"{$r->getParam('missingMessage')}\"" : '';
        $this->view->promptMessage= $r->getParam('promptMessage') ? " promptMessage=\"{$r->getParam('promptMessage')}\"" : '';
        $this->view->placeHolder = $r->getParam('placeHolder') ? " placeHolder=\"{$r->getParam('placeHolder')}\"" : '';
        
        $this->view->dataDojoProps = implode(",", $dataDojoProps);
    }
}

