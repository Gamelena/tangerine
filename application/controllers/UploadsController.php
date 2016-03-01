<?php

/**
 * Controlador para uploads, estructura base de ejemplo para ser clonada.
 * @package Controllers
 * @version Id:$
 * @since versión 0.5
 */
class UploadsController extends Zend_Controller_Action
{
    public function init()
    {
        if (!Zwei_Admin_Auth::getInstance()->hasIdentity()) {
            $this->_redirect('index/login');
        }
        $this->_helper->layout()->disableLayout();
        $this->view->base_url = BASE_URL;
    }
}
