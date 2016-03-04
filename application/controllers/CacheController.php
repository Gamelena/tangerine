<?php

class CacheController extends Zend_Controller_Action
{

    public function init()
    {
        if (!Zwei_Admin_Auth::getInstance()->hasIdentity()) { $this->_redirect('admin/login'); 
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
            if (Zwei_Utils_File::clearRecursive(ROOT_DIR ."/cache")) {
                $response['status'] = 'OK';
                $response['message'] = "Cache borrado";
            }
        } else {
            $tags = explode(',', $listTags);
            $cache = new Zwei_Controller_Plugin_Cache(Zwei_Controller_Config::getOptions());
            if ($cache->cleanByTags($tags)) {
                $response['status'] = 'OK';
                $response['message'] = "Cache borrado tags ($listTags)";
            }
        }
        
        $this->view->response = Zend_Json::encode($response);
        
    }
    

}

