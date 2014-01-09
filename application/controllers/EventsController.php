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
            $aclRoles = new DbTable_AclRoles();
            $currentRole = $aclRoles->find($authInfo->acl_roles_id)->current();

            $roleHasChanged = $currentRole->must_refresh == '1' ? true : false; 
            
            if ($roleHasChanged) {
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
                    echo "<script>window.parent.admportal.loadMainMenu();</script>";
                } else {
                    echo "<script>window.parent.location.href='".BASE_URL."admin';</script>";
                }
                
                echo "<script>
                        var timeOut = setTimeout(
                            function(){
                                window.parent.document.getElementById('ifrm_process').src = '".BASE_URL."events/update-role';
                                clearTimeout(timeOut);
                            },
                            10
                        );
                        
                      </script>";
            }
        }
    }
    public function updateRoleAction()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            Debug::write('update role');
            sleep(10);
            Debug::write('after sleep');
            $authInfo = $auth->getStorage()->read();
            $aclRoles = new DbTable_AclRoles();
            $currentRole = $aclRoles->find($authInfo->acl_roles_id)->current();
    
            $currentRole->must_refresh = '0';
            $currentRole->save();
        }
    }
    
}



