<?php

class CacheController extends Zend_Controller_Action
{

    public function init()
    {
        if (!Gamelena_Admin_Auth::getInstance()->hasIdentity()) { $this->_redirect('admin/login'); 
        }
        $this->_helper->layout()->disableLayout();
    }

    public function indexAction()
    {
        // action body
    }

    public function clearAction()
    {
        $listTags = $this->getRequest()->getParam('tags', false);
        $response = array('status' => 'FAIL');
        
        if (!$listTags) {
            if (Gamelena_Utils_File::clearRecursive(ROOT_DIR ."/cache")) {
                $response['status'] = 'OK';
                $response['message'] = "Cache borrado";
            }
        } else {
            $tags = explode(',', $listTags);
            $cache = new Gamelena_Controller_Plugin_Cache(Gamelena_Controller_Config::getOptions());
            if ($cache->cleanByTags($tags)) {
                $response['status'] = 'OK';
                $response['message'] = "Cache borrado tags ($listTags)";
            }
        }
        
        $this->view->response = Zend_Json::encode($response);
        
    }
    

}

