<?php

class Elements_DijitEditorController extends Zend_Controller_Action
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
        
        $this->view->value =  $r->getParam('value', $r->getParam('defaultValue', ''));
        $this->view->style =  $r->getParam('style', $r->getParam('style', ''));
        
        $dataDojoProps = array();
        if ($r->getParam('plugins')) $dataDojoProps[] = "plugins:{$r->getParam('plugins')}";
   
         
        $this->view->dataDojoProps = implode(',', $dataDojoProps);
    }


}



