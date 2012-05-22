<?php
/**
 * Controlador de index
 *
 * Controlador principal para lógica de despliegue HTML 
 *
 * @package Controllers
 * @version $Id:$
 * @since 0.1
 */

class IndexController extends Zend_Controller_Action
{
	/**
	 * 
	 * @var string
	 */
	private $_dojo_style = 'tundra';
	/**
	 * 
	 * @var string
	 */
	private $_sitename;
	/**
	 * 
	 * @var string
	 */
	private $_template = TEMPLATE;
	/**
	 * 
	 * @var int
	 */
	private $_version = 4;
	/**
	 * 
	 * @var Zwei_Db_Table
	 */
	private $_model;

	public function init()
	{
		$this->_helper->layout()->disableLayout();
		$this->view->base_url = BASE_URL;
		$this->base_dojo_folder = '/dojotoolkit';
		if (!empty($this->_request->style)) $this->_dojo_style=$this->_request->style;
		if (!empty($this->_request->template)) $this->_template=$this->_request->template;

		Zend_Dojo::enableView($this->view);
		$this->view->addHelperPath('Zend/Dojo/View/Helper/', 'Zend_Dojo_View_Helper');
		$this->view->dojo()
		->setLocalPath (PROTO.$_SERVER['HTTP_HOST'].$this->base_dojo_folder.'/dojo/dojo.js')
		->setDjConfig(array('parseOnLoad' => 'true', 'isDebug'=> 'false', 'locale'=>'es'));

		$this->view->headStyle()->appendStyle('
            @import "'.$this->base_dojo_folder.'/dijit/themes/'.$this->_dojo_style.'/'.$this->_dojo_style.'.css";
        ');

		$this->view->body_class = $this->_dojo_style;

		
		try {
			$Settings = new SettingsModel();
			 
			$Select = $Settings->select()->where('id = ?','titulo_adm');
			$result = $Settings->fetchAll($Select);
			$this->_sitename = $result[0]['value'];
			$this->view->adminTitle = $result[0]['value'];
			 
		 
		} catch (Zend_Db_Exception $e){}
        

		$this->view->dojo()
		->requireModule("dijit.layout.BorderContainer")
		->requireModule("dijit.form.Button")
		->requireModule("dijit.form.DropDownButton")
		->requireModule("dijit.Menu")
		->requireModule("dijit.MenuItem")
		->requireModule("dijit.dijit")
		->requireModule("dijit.Calendar")
		->requireModule("dojo.data.ItemFileWriteStore")
		->requireModule("dojo.date.locale")
		->requireModule("dijit.Tree")
		->requireModule("dojo.cookie")
		->requireModule("dojox.layout.ContentPane")
		->requireModule("dijit.layout.TabContainer")
		->requireModule("dijit.Dialog")
		->requireModule("dijit.form.Form")
		->requireModule("dijit.form.TextBox")
		->requireModule("dijit.form.Textarea")
		->requireModule("dijit.form.SimpleTextarea")
		->requireModule("dijit.form.Button")
		->requireModule("dijit.form.DateTextBox")
		->requireModule("dijit.form.FilteringSelect")
		->requireModule("dojox.grid.DataGrid")
		->requireModule("dojox.grid.EnhancedGrid")
		->requireModule("dojox.grid.enhanced.plugins.Pagination")
		->requireModule("dojox.form.CheckedMultiSelect")
		->requireModule("dojox.form.FileInput")
		->requireModule("dojox.encoding.digests.MD5")
		->requireModule("dojo.data.ItemFileWriteStore")
		->requireModule("dojo.data.ItemFileReadStore")
		->requireModule("dojo.date.locale")
		;

        $this->view->headStyle()->appendStyle('
            @import "'.$this->base_dojo_folder.'/dojox/grid/resources/Grid.css";
            @import "'.$this->base_dojo_folder.'/dojox/grid/resources/'.$this->_dojo_style.'Grid.css";
            @import "'.$this->base_dojo_folder.'/dojox/grid/enhanced/resources/'.$this->_dojo_style.'/EnhancedGrid.css";
            @import "'.$this->base_dojo_folder.'/dojox/grid/enhanced/resources/EnhancedGrid_rtl.css";
            @import "'.$this->base_dojo_folder.'/dojox/form/resources/CheckedMultiSelect.css";
            @import "'.$this->base_dojo_folder.'/dojox/form/resources/FileInput.css";
            @import "'.BASE_URL.'css/admin.css?version='.$this->_version.'";
        ');         
		        

		if ($this->_template != '' && $this->_template != 'dojo') {
			$this->view->headStyle()->appendStyle('
                @import "'.BASE_URL.'css/'.$this->_template.'.css";
            ');         
		} else {
			$this->view->headStyle()->appendStyle('
            .claro, .tundra, .nihilo, .soria{
               color: #131313;
               font-family: Verdana,Arial,Helvetica,sans-serif;
               font-size: 0.688em;
               background: url("'.BASE_URL.'css/i/bodyBg.gif") repeat-x scroll left top #FFFFFF;
            }
            ');
		}
	}


	public function indexAction()
	{
		// action body
		if (!Zend_Auth::getInstance()->hasIdentity()) $this->_redirect('index/login');
		$userInfo = Zend_Auth::getInstance()->getStorage()->read();
		$this->view->user_name = $userInfo->user_name;
		$this->view->first_names = $userInfo->first_names;
		$this->view->last_names = $userInfo->last_names;
		$this->view->user_id = $userInfo->id;

		if (!empty($this->_template)) {
			$this->_helper->viewRenderer("index-$this->_template");
			if ($this->_template != 'dojo') {
				$Modules = new AclModulesModel();
				$Modules->setApproved();
				$tree = $Modules->getTree();
				$Menu = new Zwei_Utils_Menu($tree);
				$this->view->list=$Menu->display();
			}
		}

		$this->view->content = "<center><img width=\"960\" src=\"".BASE_URL."images/satelite.jpg\"/></center>";
	}

	public function componentsAction()
	{
		if (!Zend_Auth::getInstance()->hasIdentity()) $this->_redirect('index/login');
		$userInfo = Zend_Auth::getInstance()->getStorage()->read();
		/*
		 * Esto aplica sólo en el caso de no usar template dojo por defecto.
		 */
		if (!empty($this->_template)) {
			$this->_helper->viewRenderer("index-$this->_template");
			if ($this->_template!='dojo') {
				$Modules = new AclModulesModel();
				$Modules->setApproved();
				$tree = $Modules->getTree();
				$Menu = new Zwei_Utils_Menu($tree);
				$this->view->list=$Menu->display();
			}
		}

		$Xml = new Zwei_Admin_Xml();

		if (isset($this->_request->p)) {
			if (Zwei_Admin_Acl::isUserAllowed($this->_request->p, "LIST") || Zwei_Admin_Acl::isUserAllowed($this->_request->p, "EDIT") || Zwei_Admin_Acl::isUserAllowed($this->_request->p, "ADD")) {
				
				if (preg_match('/(.*).php/', $this->_request->p)) {
				    $file = BASE_URL ."components/".$this->_request->p;
				} else {
				    $file = COMPONENTS_ADMIN_PATH."/".$this->_request->p;
				}
				$Xml->parse($file);
				
				//parche para que cargue tabla html no dojo, si el layout no es dojo
				if (TEMPLATE == 'urban' && $Xml->elements[0]['TYPE'] == 'table_dojo') {
					$Xml->elements[0]['TYPE'] = 'table';
				}	
				$ComponentClass = "Zwei_Admin_Components_".Zwei_Utils_String::toClassWord($Xml->elements[0]['TYPE']);
				//Se pasa como parámetro $this->view para hacer posible la inclusión de librerías js y css auxiliares 
				$View = new $ComponentClass($this->_request->p, $this->view);
				$content = $View->display();
			} else {
				$content = "Acceso denegado a módulo";
			}

		} else {
			$content = "portada";
		}
		if (isset($this->_request->ajax)) {
			$this->_helper->viewRenderer('ajax');
		}
		$this->view->content = $content;
	}

	
    public function dynaComponentsAction()
    {
        //DEPRECATED
    }	
	
	
	/**
	 * Acá se asocia a un Zend Controller un objeto Zwei_Admin_Components_Helpers_EditTabsDojo()
	 * ya que debe cargar dentro de una nueva URL la cual por convención ZF debe manejarse por un objeto Zend_Controller_Action
	 * @return HTML
	 */
	public function tabsAction()
	{
		if(!Zend_Auth::getInstance()->hasIdentity()) $this->_redirect('index/login');
		$userInfo = Zend_Auth::getInstance()->getStorage()->read();

		$Xml=new Zwei_Admin_Xml();
		$Form=new Zwei_Utils_Form();

		if (isset($this->_request->p)) {
			if (Zwei_Admin_Acl::isUserAllowed($this->_request->p, "LIST") || Zwei_Admin_Acl::isUserAllowed($this->_request->p, "EDIT") || Zwei_Admin_Acl::isUserAllowed($this->_request->p, "ADD")) {
				
			    if (preg_match('/(.*).php/', $this->_request->p)) {
	                $file = BASE_URL ."/components/".$this->_request->p;
                } else {
                    $file = COMPONENTS_ADMIN_PATH."/".$this->_request->p;
                }	
                			
				$Xml->parse($file);
				$model = Zwei_Utils_String::toClassWord($Xml->elements[0]['TARGET']).'Model';
				$this->_model = new $model;
				$primary = $this->_model->getPrimary() ? $this->_model->getPrimary() : "id";
				
				//ComponentClass="Zwei_Admin_Components_".Zwei_Utils_String::toClassWord($Xml->elements[0]['TYPE']);
				//Se pasa como parámetro $this->view para hacer posible la inclusión de librerías js y css auxiliares mediante un objeto Zend_View
				$View = new Zwei_Admin_Components_Helpers_EditTabs($this->_request->p, $this->_request->$primary, $this->view);
				$content = $View->display($Form->action);
			} else {
				$content = "Acceso denegado a módulo";
			}
			$this->view->content = $content;
		}
	}


	/**
	 * Acá se asocia a un Zend Controller un objeto Zwei_Admin_Components_Helpers_EditTabsDojo()
	 * ya que debe cargar dentro de una nueva URL la cual por convención ZF debe manejarse por un objeto Zend_Controller_Action
	 * @return HTML
	 */
	public function tabsDojoAction()
	{
		if (!Zend_Auth::getInstance()->hasIdentity()) $this->_redirect('index/login');
		$userInfo = Zend_Auth::getInstance()->getStorage()->read();

		$Xml = new Zwei_Admin_Xml();
		$Form = new Zwei_Utils_Form();

		if(isset($this->_request->p))
		{
			if (Zwei_Admin_Acl::isUserAllowed($this->_request->p, "LIST") || Zwei_Admin_Acl::isUserAllowed($this->_request->p, "EDIT") || Zwei_Admin_Acl::isUserAllowed($this->_request->p, "ADD")) {
			    if (preg_match('/(.*).php/', $this->_request->p)) {
                    $file = BASE_URL ."/components/".$this->_request->p;
                } else {
                    $file = COMPONENTS_ADMIN_PATH."/".$this->_request->p;
                }				
				$Xml->parse($file);
				//ComponentClass="Zwei_Admin_Components_".Zwei_Utils_String::toClassWord($Xml->elements[0]['TYPE']);
				//Se pasa como parámetro $this->view para hacer posible la inclusión de librerías js y css auxiliares mediante un objeto Zend_View
				$View = new Zwei_Admin_Components_Helpers_EditTabsDojo($this->_request->p, $this->_request->id, $this->view);
				$content = $View->display($Form->action);
			} else {
				$content = "Acceso denegado a módulo";
			}
			$this->view->content=$content;
		}
	}



	public function modulesAction()
	{
		if (!Zend_Auth::getInstance()->hasIdentity()) $this->_redirect('index/login');
		$userInfo = Zend_Auth::getInstance()->getStorage()->read();


		Zend_Dojo::enableView($this->view);

		$this->_helper->ContextSwitch
		->setAutoJsonSerialization(false)
		->addActionContext('index', 'json')
		->initContext();

		$this->_helper->viewRenderer->setNoRender(true);
		$Modules = new AclModulesModel();
		$Modules->setApproved();

		$tree=$Modules->getTree();

		$treeObj = new Zend_Dojo_Data('id', $tree);
		$treeObj->setLabel('label');

		echo Zend_Json::prettyPrint($treeObj->toJson());
	}

	public function loginAction()
	{

		$this->view->headStyle()->appendStyle('
            @import "'.BASE_URL.'css/admin.css";
        '); 

		if(Zend_Auth::getInstance()->hasIdentity())
		{
			$this->_redirect(BASE_URL. 'index');
		}

		$request = $this->getRequest();
		$loginForm = $this->getLoginForm();

		$errorMessage = "";

		if($request->isPost())
		{
			if($loginForm->isValid($request->getPost()))
			{

				$authAdapter = $this->getAuthAdapter();

				$username = $loginForm->getValue('username');
				$password = $loginForm->getValue('password');

				$authAdapter->setIdentity($username)
				->setCredential($password);

				$auth = Zend_Auth::getInstance();
				$result = $auth->authenticate($authAdapter);

				if($result->isValid())
				{

					// Obtenter toda la info de usuario, excepto la password
					$userInfo = $authAdapter->getResultRowObject(null, 'password');

					$authSession = new Zend_Session_Namespace('Zend_Auth');
					$authSession->setExpirationSeconds(3600);

					// El storage por defecto es una session con namespace Zend_Auth
					$authStorage = $auth->getStorage();
					$authStorage->write($userInfo);

					$this->_redirect(BASE_URL.'index');
				}
				else
				{
					$errorMessage = "Usuario o Password incorrectos.";
				}
			}
		}
		$this->view->errorMessage = $errorMessage;
		$this->view->loginForm = $loginForm;
	}

	public function logoutAction()
	{
		Zend_Auth::getInstance()->clearIdentity();
		$this->_redirect(BASE_URL.'index/login');
	}

	public function legacyAction()
	{
		$this->view->content = include($this->_request->p); 
	}
	
    public function iframeAction()
    {
    	$this->view->content = $this->_request->p;
        // action body
    }
	
	

	/**
	 * Gets the adapter for authentication against a database table
	 *
	 * @return object
	 */
	protected function getAuthAdapter()
	{
		$config = new Zend_Config_Ini(ROOT_DIR.'/application/configs/application.ini', APPLICATION_ENV);
		$dbAdapter = Zend_Db::factory($config->resources->multidb->auth);
		//$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
        $authUsersTable = 'acl_users';
        $authUserName = 'user_name';
        $authPassword = 'password';
		
		
		$authAdapter->setTableName($authUsersTable)
		->setIdentityColumn($authUserName)
		->setCredentialColumn($authPassword)
		->setCredentialTreatment('MD5(?) and approved="1"');

		return $authAdapter;
	}

	/**
	 * login form
	 *
	 * @return object
	 */
	protected function getLoginForm()
	{
		$username = new Zend_Dojo_Form_Element_ValidationTextBox('username');
		$username->setLabel('Usuario:')
		->setRequired(true);

		$password = new Zend_Dojo_Form_Element_PasswordTextBox('password');
		$password->setLabel('Contraseña:')
		->setRequired(true);

		$submit = new Zend_Form_Element_Submit('login');
		$submit->setLabel('Login');

		$loginForm = new Zend_Dojo_Form();
		$loginForm->setTranslator();
		$loginForm->setAction($this->_request->getBaseUrl().'/index/login/')
		->setTranslator(new Zend_Translate('array',array("Value is required and can't be empty"=>"Este valor no puede ir vacío"),'es'))
		->setMethod('post')
		->addElement($username)
		->addElement($password)
		->addElement($submit);

		return $loginForm;
	}
}

