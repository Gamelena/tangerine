<?php

class Elements_GamelenaFormDateTextBoxController extends Elements_BaseController
{
    public function indexAction()
    {
        $r = $this->getRequest();
        $this->view->invalidMessage = $r->getParam('invalidMessage') ? "invalidMessage=\"{$r->getParam('invalidMessage')}\"" : '';
        $this->view->promptMessage= $r->getParam('promptMessage') ? "promptMessage=\"{$r->getParam('promptMessage')}\"" : '';
        $this->view->trim = $r->getParam('trim', '') === 'true' ? "trim=\"true\"" : '';
    }
}