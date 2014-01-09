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
            echo "<script>window.parent.location.href='".BASE_URL."/admin/login';</script>";
        } else {
            $auth = Zend_Auth::getInstance();
            $authInfo = $auth->getStorage()->read();
            $aclRoles = new DbTable_AclRoles();
            $currentRole = $aclRoles->find($authInfo->acl_roles_id)->current();
            
            $roleHasChanged = $currentRole->must_refresh == '1' ? true : false; 
            
            if ($roleHasChanged) {
                $authAdapter = Zwei_Admin_Auth::getInstance()->getAuthAdapter(false);
                $userModel = new DbTable_AclUsers();
                $userFind = $userModel->find($authInfo->id);
                if ($userFind->count() <= 0) {
                    exit("<script>window.parent.location.href='".BASE_URL."/admin/login';</script>");
                } else {
                    $currentUser = $userModel->find($authInfo->id)->current();
                }
                
                
                $username = $currentUser->user_name;
                $password = $currentUser->password;
                
                $authAdapter->setIdentity($username)
                ->setCredential($password);

                $result = $auth->authenticate($authAdapter);
                Debug::write($result);
                
                if ($result->isValid()) {
                    Zwei_Admin_Auth::initUserInfo($authAdapter);
                    $acl=new Zwei_Admin_Acl();
                    echo "<script>window.parent.admportal.loadMainMenu();</script>";
                } else {
                    echo "<script>window.parent.location.href='".BASE_URL."admin';</script>";
                }
                
                echo "<script>
                   window.parent.document.getElementById('ifrm_process').src = '".BASE_URL."events/update-role';
                   clearTimeout(timeOut);
                      </script>";
            }
        }
    }
    public function updateRoleAction()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            sleep(10);
            $authInfo = $auth->getStorage()->read();
            $aclRoles = new DbTable_AclRoles();
            $currentRole = $aclRoles->find($authInfo->acl_roles_id)->current();
    
            $currentRole->must_refresh = '0';
            $currentRole->save();
        }
    }
    
}



