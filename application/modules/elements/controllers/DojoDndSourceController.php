<?php

class Elements_DojoDndSourceController extends Zend_Controller_Action
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
        
        $this->view->rsPossible = array('Colo Colo');
        $this->view->rsWrong = array('Galvarino', 'Tucapel', 'Rengo', 'Caupolicán');
        $this->view->rsRight = array('Lautaro');
    }


}

