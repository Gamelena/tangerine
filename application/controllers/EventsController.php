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
        
        if (!Zwei_Admin_Auth::getInstance()->hasIdentity()) {
            $this->view->response['status'] = 'AUTH_FAILED';
            $this->render();
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
                } else {
                    $this->view->response['status'] = 'AUTH_FAILED';
                    $this->render();
                }
            }
        }
    }
    /**
     * Actualiza los permisos del perfil en uso.
     */
    public function updateRoleAction()
    {
        $auth = Zend_Auth::getInstance();
        $this->view->response = array('status' => 'UPDATE_FAILED');
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
            if ($currentRole->save()) {
                $this->view->response['status'] = 'UPDATE_OK';
            }
        } else {
            $this->view->response['status'] = 'AUTH_FAILED';
        }
    }
    
}



