<?php

class Components_GroupsModulesController extends Zend_Controller_Action
{

    /**
     * @var int
     *
     *
     */
    private $_aclModulesId = null;

    /**
     * @var int
     *
     *
     */
    private $_aclModulesItemId = null;

    /**
     * Post Constructor
     *
     * @see Zend_Controller_Action::init()
     *
     *
     */
    public function init()
    {
        if (!Zwei_Admin_Auth::getInstance()->hasIdentity()) $this->_redirect('admin/login');
        $this->view->aclModulesId = $this->_aclModulesId = $this->getRequest()->getParam('acl_modules_id');
        $this->view->aclModulesItemId = $this->_aclModulesItemId = $this->getRequest()->getParam('acl_modules_item_id');
    }

    public function indexAction()
    {
        
        
    }

    public function getModules($aclModulesId = null, $title = null)
    {
        $i = 0;
        $modules[$i] = array();
        
        $actions = array();
        $modulesModel = new DbTable_AclModules();
        $parent = $modulesModel->find($aclModulesId)->current();
        $modules[$i]['id'] = $parent->id;
        $modules[$i]['title'] = $title != null ? $title : $parent->title;
        /**
         * TODO esto debe ser parametrizable
         */
        //$modules[$i]['title'] = "Campaña";
       
        $actions = $parent->findDependentRowset('DbTable_AclModulesActions');
        $modules[$i]['actions'] = $actions->toArray();
        
        $modules[$i] = (object) $modules[$i];
        
        $j = 0;
        foreach ($actions as $action) {
            $modules[$i]->actions[$j]['title'] = $action->findParentRow('DbTable_AclActions')->title;
            $modules[$i]->actions[$j] = (object) $modules[$i]->actions[$j];
            $j++;
        }
        
        $childrens = $parent->findDependentRowset('DbTable_AclModules');
        
        if ($childrens->count()) {
            foreach ($childrens as $child) {
                $modules[] = $this->getModules($child->id)[0];
            }
        }
        
        
        return $modules;
    }

    public function editAction()
    {
        $this->view->mode = 'edit';
        $title = 'Campaña';
        $this->view->modules = $this->getModules($this->_aclModulesId, $title);
    }

    public function addAction()
    {
        $this->view->mode = 'add';
        $title = 'Campaña';
        $this->view->modules = $this->getModules($this->_aclModulesId, $title);
        $this->render('edit');
    }


}





