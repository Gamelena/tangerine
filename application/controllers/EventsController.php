<?php

class EventsController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        if (!Zwei_Admin_Auth::getInstance()->hasIdentity())
        {
            echo "<script>window.parent.location.href='".BASE_URL."/admin';</script>";
        } else {
            $auth = Zend_Auth::getInstance();
            $authInfo = $auth->getStorage()->read();
            $aclRoles = new AclRolesModel();
            
            
            if (false) {
                $authAdapter = Zwei_Admin_Auth::getInstance()->getAuthAdapter(false);
                $userModel = new DbTable_AclUsers();
                $currentUser = $userModel->find($authInfo->id)->current();
                $username = $currentUser->user_name;
                $password = $currentUser->password;
                
                $authAdapter->setIdentity($username)
                ->setCredential($password);

                $result = $auth->authenticate($authAdapter);
                
                if($result->isValid())
                {
                    Zwei_Admin_Auth::initUserInfo($authAdapter);
                    $acl=new Zwei_Admin_Acl();
                } else {
                    echo "<script>window.parent.location.href='".BASE_URL."/admin';</script>";
                }
            }
        }
    }
}

