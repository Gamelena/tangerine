<?php

class Elements_DijitFormTextareaController extends Elements_BaseController
{
    public function indexAction()
    {
        $r = $this->getRequest();
        
        $this->view->autocomplete = $r->getParam('autocomplete') ? " autocomplete=\"{$r->getParam('autocomplete')}\"" : '';
        $this->view->autocorrect = $r->getParam('autocorrect') ? " autocorrect=\"{$r->getParam('autocorrect')}\"" : '';
        $this->view->autocapitalize = $r->getParam('autocapitalize') ? " autocapitalize=\"{$r->getParam('autocapitalize')}\"" : '';
        $this->view->spellcheck = $r->getParam('spellcheck') ? " spellcheck=\"{$r->getParam('spellcheck')}\"" : '';
        
        $this->view->style = $r->getParam('style') ? "style=\"{$r->getParam('style')}\"" : '';
    }
}

