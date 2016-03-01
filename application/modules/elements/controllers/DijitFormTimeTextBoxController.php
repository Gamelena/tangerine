<?php

class Elements_DijitFormTimeTextBoxController extends Elements_BaseController
{
    public function indexAction()
    {
        $r = $this->getRequest();
        
        $this->view->invalidMessage = $r->getParam('invalidMessage') ? "invalidMessage=\"{$r->getParam('invalidMessage')}\"" : '';
        $this->view->promptMessage= $r->getParam('promptMessage') ? "promptMessage=\"{$r->getParam('promptMessage')}\"" : '';
    }


}

