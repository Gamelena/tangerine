<?php

class Elements_ListFilesController extends Elements_BaseController
{

    public function indexAction()
    {
        $r = $this->getRequest();
        
        $this->view->path = $r->getParam('path');
        $this->view->publicPath = $r->getParam('publicPath', BASE_URL . $this->view->path);
        
        $this->view->files = array();
        while (false !== ($file = readdir($handle))) {
            $this->view->files[] = $file;
        }
        
        sort($this->view->files);
        
    }


}

