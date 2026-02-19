<?php
/**
 * Controlador que valida estatus de sesion en curso.
 */
class EventsController extends Zend_Controller_Action
{
    /**
     * Verificar status de usuario en sesión.
     * @return void
     */
    public function indexAction()
    {
        $this->view->response = array('status' => 'OK');

        if (!Gamelena_Admin_Auth::getInstance()->hasIdentity()) {
            $this->view->response['status'] = 'AUTH_FAILED';
            $this->render();
        } else {
            $auth = Zend_Auth::getInstance();
            $authInfo = $auth->getStorage()->read();

            $userModel = new AclSessionModel();
            $userFind = $userModel->findByAclUsersId($authInfo->id);

            if ($userFind->count() <= 0) {
                $this->view->response['status'] = 'AUTH_FAILED';
                $this->render();
            } else {
                $currentUser = $userFind->current();
            }

            $roleHasChanged = $currentUser->must_refresh == '1' ? true : false;

            if ($roleHasChanged) {
                $userModel = new DbTable_AclUsers();
                $userFind = $userModel->find($authInfo->id);

                if ($userFind->count() <= 0) {
                    $this->view->response['status'] = 'AUTH_FAILED';
                    $this->render();
                }

                $currentUser = $userFind->current();
                $username = $currentUser->user_name;
                $password = $currentUser->password;

                $authAdapter = Gamelena_Admin_Auth::getInstance()->getAuthAdapter(false);
                $authAdapter->setIdentity($username)
                    ->setCredential($password);

                $result = $auth->authenticate($authAdapter);

                if ($result->isValid()) {
                    Gamelena_Admin_Auth::initUserInfo($authAdapter);
                    $acl = new Gamelena_Admin_Acl();
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
     * @return void
     */
    public function updateRoleAction()
    {
        $auth = Zend_Auth::getInstance();
        $this->view->response = array('status' => 'UPDATE_FAILED');
        if ($auth->hasIdentity()) {
            $authInfo = $auth->getStorage()->read();
            $aclUsersId = $authInfo->id;

            if ($this->getRequest()->getParam('acl_users_id')) {
                $acl = new Gamelena_Admin_Acl();
                if ($acl->isUserAllowed('users.xml', 'edit')) {
                    $aclUsersId = $this->getRequest()->getParam('acl_users_id');
                }
            }

            sleep(10);

            $userModel = new AclSessionModel();
            $currentUser = $userFind = $userModel->findByAclUsersId($authInfo->id)->current();
            $currentUser->must_refresh = '0';

            if ($currentUser->save()) {
                $this->view->response['status'] = 'UPDATE_OK';
            }
        } else {
            $this->view->response['status'] = 'AUTH_FAILED';
        }
    }
}
