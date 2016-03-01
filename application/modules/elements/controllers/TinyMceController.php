<?php
class Elements_TinyMceController extends Elements_BaseController
{
    public function indexAction()
    {
        $this->view->tinyMceInit =  $r->getParam('tinyMceInit', false);
        $this->view->tinyMceTemplate =  $r->getParam('tinyMceTemplate', 'simple');
    }
}

