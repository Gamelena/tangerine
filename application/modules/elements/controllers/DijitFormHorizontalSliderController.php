<?php

class Elements_DijitFormHorizontalSliderController extends Zend_Controller_Action
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
        $this->view->value =  $r->getParam('value', 0);
        $this->view->minimum = (int) $r->getParam('minimum', 0);
        $this->view->maximum = (int) $r->getParam('maximum', 10);
        
        $this->view->discreteValues = abs($this->view->maximum - $this->view->minimum) + 1;
    }
}

