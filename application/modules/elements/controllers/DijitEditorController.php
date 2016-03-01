<?php

class Elements_DijitEditorController extends Elements_BaseController
{
    public function indexAction()
    {
        $r = $this->getRequest();
        $this->view->style =  $r->getParam('style', $r->getParam('style', ''));
        
        $dataDojoProps = array();
        
        if ($r->getParam('plugins')) {
            $dataDojoProps[] = "plugins:{$r->getParam('plugins')}";
        }
         
        $this->view->dataDojoProps = implode(',', $dataDojoProps);
    }
}



