<?php
/**
 * Controlador para funcionalidades de testing
 *
 *
 * @package Controllers
 * @version Id:$
 * @since versión 0.5
 */


class TestsController extends Zend_Controller_Action
{
	public function init()
	{
		$this->_helper->layout()->disableLayout();
		$this->view->base_url=BASE_URL;
		 
		Zend_Dojo::enableView($this->view);
		$this->view->addHelperPath('Zend/Dojo/View/Helper/', 'Zend_Dojo_View_Helper');
		$this->view->dojo()
		->setLocalPath (PROTO.$_SERVER['HTTP_HOST'].'/dojotoolkit/dojo/dojo.js')
		->setDjConfig(array('parseOnLoad' => 'true'))
		->requireModule("dijit.layout.BorderContainer")
		->requireModule("dijit.form.Button")
		->requireModule("dijit.form.DropDownButton")
		->requireModule("dijit.Menu")
		->requireModule("dijit.MenuItem")
		->requireModule("dojo.data.ItemFileWriteStore")
		->requireModule("dijit.Tree")
		->requireModule("dojo.cookie")
		->requireModule("dojox.layout.ContentPane");
		$this->view->headStyle()->appendStyle('
			@import "/dojotoolkit/dijit/themes/claro/claro.css";
		');	
	}

	public function indexAction(){

	}

	public function modulesAction()
	{
		//Zend_Dojo::enableView($this->view);
		//Zend_Layout::getMvcInstance()->disableLayout();
		$this->_helper->ContextSwitch
		->setAutoJsonSerialization(false)
		->addActionContext('index', 'json')
		->initContext();
	  
		$this->view->collection = array();
	  
		$this->_helper->viewRenderer->setNoRender(true);
		$Tree = new AclModulesModel();
		$Tree->setApproved();
	  
		$DojoTree = new Zend_Dojo_Data('id', $Tree->getTree());
		$DojoTree->setMetadata(array('path' => '/'));
		$this->view->collection=$DojoTree->toJson();
	}

	public function aclAction()
	{
		$params = new Zend_Controller_Request_Http();
		$acl = new Zwei_Admin_Acl($params->user);
		$this->view->user = $acl->_user;
		$this->view->role = $acl->_getUserRoleName;

		foreach ($acl->listResources() as $key=>$r)
		{
			try {
				$s[$r['module'].' - '.$r['permission']] = $acl->isUserAllowed($r['module'], $r['permission']) ? '<font color="green">allowed</font>' : '<font color="red">denied</font>';
				$this->view->allowed = $s;
			} catch (Zend_Acl_Exception $e) {
				print_r ($e->getMessage());
			}
		}
	}


	public function treeAction()
	{

	}
}