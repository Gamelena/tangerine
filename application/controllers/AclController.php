<?php

class AclController extends Zend_Controller_Action
{

    /**
     * @var Zwei_Admin_Acl
     */
    private $_acl = null;

    /**
     * @var string
     */
    private $_module = null;

    /**
     * @var string
     */
    private $_permission = null;

    /**
     * @var string
     */
    private $_itemId = null;

    public function init()
    {
        $this->_acl = new Zwei_Admin_Acl();
        $this->_module = $this->getRequest()->getParam('p', null);//$_REQUEST['module'] esta reservado por ZF
        $this->_permission = $this->getRequest()->getParam('permission', null);
        $this->_itemId = $this->getRequest()->getParam('itemId', null);
    }

    public function indexAction()
    {
        // action body
    }

    public function isUserAllowedAction()
    {
        $response = $this->_acl->isUserAllowed($this->_module, $this->_permission, $this->_itemId);
        $this->view->response = Zend_Json::encode(array('granted' => $response));
    }

    public function userHasRoleAllowedAction()
    {
        $response = $this->_acl->userHasRoleAllowed($this->_module, $this->_permission);
        $this->view->response = Zend_Json::encode(array('granted' => $response));
    }

    public function userHasGroupsAllowedAction()
    {
        $response = $this->_acl->userHasGroupsAllowed($this->_module, $this->_permission, $this->_itemId);
        $this->view->response = Zend_Json::encode(array('granted' => $response));
    }

    public function getUserInfoAction()
    {
        $response = Zend_Auth::getInstance()->getStorage()->read();
        $this->view->response = Zend_Json::encode($response);
    }
}
