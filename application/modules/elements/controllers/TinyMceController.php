<?php
class Elements_TinyMceController extends Zend_Controller_Action
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
        $this->view->tinyMceInit =  $r->getParam('tinyMceInit', false);
        $this->view->tinyMceTemplate =  $r->getParam('tinyMceTemplate', 'simple');
        
        $this->view->value =  $r->getParam('value', '');
    }
}

