<?php

class Elements_ListFilesController extends Zend_Controller_Action
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
        
        $this->view->path = $r->getParam('path');
        $this->view->publicPath = $r->getParam('publicPath', BASE_URL . $this->view->path);
        
        $this->view->files = array();
        while (false !== ($file = readdir($handle))) {
            Debug::write($file);
            $this->view->files[] = $file;
        }
        
        sort($this->view->files);
        
    }


}

