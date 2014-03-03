<?php
/**
 * Controlador que valida estatus de sesion en curso.
 * 
 * @todo cambiar los echo de javascript por una respuesta json que sea recogida por el listener js que invoca a esta url.
 *
 */
class EventsController extends Zend_Controller_Action
{

    public function indexAction()
    {
        $this->view->response = array('status' => 'OK');
        
        if (!Zwei_Admin_Auth::getInstance()->hasIdentity())
        {
            $this->view->response['status'] = 'AUTH_FAILED';
            $this->render();
            //echo "<script>window.parent.location.href='".BASE_URL."admin/login';</script>";
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
                    $this->view->response['status'] = 'AUTH_FAILED';
                    $this->render();
                    //exit("<script>window.parent.location.href='".BASE_URL."admin/login';</script>");
                } else {
                    $currentUser = $userFind->current();
                }
                
                $username = $currentUser->user_name;
                $password = $currentUser->password;
                
                $authAdapter->setIdentity($username)
                ->setCredential($password);

                $result = $auth->authenticate($authAdapter);
                
                if ($result->isValid()) {
                    Zwei_Admin_Auth::initUserInfo($authAdapter);
                    $acl = new Zwei_Admin_Acl();
                    $this->view->response['status'] = 'ROLE_HAS_CHANGED';
                    //echo "<script>window.parent.admportal.loadMainMenu();</script>";
                } else {
                    $this->view->response['status'] = 'AUTH_FAILED';
                    //echo "<script>window.parent.location.href='".BASE_URL."admin';</script>";
                    $this->render();
                }
                /*
                echo "<script>
                   window.parent.document.getElementById('ifrm_process').src = '".BASE_URL."events/update-role';
                      </script>";
                */
            }
        }
    }
    /**
     * Actualiza los permisos del perfil en uso.
     */
    public function updateRoleAction()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $authInfo = $auth->getStorage()->read();
            $aclRolesId = $authInfo->acl_roles_id;
            
            if ($this->getRequest()->getParam('acl_roles_id')) {
                $acl = new Zwei_Admin_Acl();
                if ($acl->isUserAllowed('roles.xml', 'edit')) {
                    $aclRolesId = $this->getRequest()->getParam('acl_roles_id');
                }
            }
            
            sleep(10);

            $aclRoles = new DbTable_AclRoles();
            $currentRole = $aclRoles->find($aclRolesId)->current();
    
            $currentRole->must_refresh = '0';
            $currentRole->save();
        }
    }
    
}



