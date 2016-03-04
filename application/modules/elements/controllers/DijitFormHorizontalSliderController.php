<?php

class Elements_DijitFormHorizontalSliderController extends Elements_BaseController
{
    public function indexAction()
    {
        $r = $this->getRequest();
        
        $this->view->value =  $r->getParam('value', 0);
        $this->view->minimum = (int) $r->getParam('minimum', 0);
        $this->view->maximum = (int) $r->getParam('maximum', 10);
        $this->view->promptMessage = $r->getParam('promptMessage') ? " promptMessage=\"{$r->getParam('promptMessage')}\"" : '';
        $this->view->invalidMessage = $r->getParam('invalidMessage') ? " invalidMessage=\"{$r->getParam('invalidMessage')}\"" : '';
        
        $this->view->discreteValues = abs($this->view->maximum - $this->view->minimum) + 1;
    }
}

