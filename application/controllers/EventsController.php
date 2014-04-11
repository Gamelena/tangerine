<?php
/**
 * Controlador que valida estatus de sesion en curso.
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
            //$aclRoles = new DbTable_AclRoles();
            //$currentRole = $aclRoles->find($authInfo->acl_roles_id)->current();
            
            $userModel = new DbTable_AclSession();
            //$userFind = $aclUsers->find($authInfo->id);//<- @FIMXE - find() esto falla silenciosamente ¡?
            
            //WORKAROUND FIXME
            $select = $userModel->select(false)
                ->from($userModel->info(Zend_Db_Table::NAME, array('acl_users_id')))
                ->where($userModel->getAdapter()->quoteInto('acl_users_id = ?', $authInfo->id));
            
            $userFind = $userModel->fetchAll($select);
            //END WORKAROUND
            
            if ($userFind->count() <= 0) {
                $this->view->response['status'] = 'AUTH_FAILED';
                $this->render();
            } else {
                $currentUser = $userFind->current();
            }
            
            $roleHasChanged = $currentUser->must_refresh == '1' ? true : false;
            
            if ($roleHasChanged) {
                //WORKAROUND
                $userModel = new DbTable_AclUsers();
                $select = $userModel->select(false)
                ->from($userModel->info(Zend_Db_Table::NAME, array('user_name', 'password')))
                ->where($userModel->getAdapter()->quoteInto('id = ?', $authInfo->id));
                
                $authAdapter = Zwei_Admin_Auth::getInstance()->getAuthAdapter(false);
                $userFind = $userModel->fetchAll($select);
                //END WORKAROUND
                
                if ($userFind->count() <= 0) {
                    $this->view->response['status'] = 'AUTH_FAILED';
                    $this->render();
                }
                
                $currentUser = $userFind->current();
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
            $aclUsersId = $authInfo->id;
            
            if ($this->getRequest()->getParam('acl_users_id')) {
                $acl = new Zwei_Admin_Acl();
                if ($acl->isUserAllowed('users.xml', 'edit')) {
                    $aclUsersId = $this->getRequest()->getParam('acl_users_id');
                }
            }
            
            sleep(10);

            $userModel = new DbTable_AclSession();
            //$currentUser = $userModel->find($aclUsersId)->current();//<- @FIMXE - find() falla miserable y silenciosamente ¡?
            //WORKAROUND FIXME
            $select = $userModel->select(false)
                ->from($userModel->info(Zend_Db_Table::NAME, array('acl_users_id')))
                ->where($userModel->getAdapter()->quoteInto('acl_users_id = ?', $authInfo->id));
            
            $currentUser = $userModel->fetchRow($select);
            Debug::write($currentUser->toArray());
            //END WORKAROUND
            
            $currentUser->must_refresh = '0';
            if ($currentUser->save()) {
                $this->view->response['status'] = 'UPDATE_OK';
            }
        } else {
            $this->view->response['status'] = 'AUTH_FAILED';
        }
    }
}
