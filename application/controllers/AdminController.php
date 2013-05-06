<?php
/**
 * Controlador de backoffice
 *
 *
 * @package Controllers
 * @version $Id:$
 * @since 1.0
 */
class AdminController extends Zend_Controller_Action
{
    /**
     *
     * @var string
     */
    private $_dojoTheme = 'tundra';
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
    private $_version = 10;
    /**
     *
     * @var Zwei_Db_Table
     */
    private $_model;
    /**
     * 
     * @var Zwei_Admin_Acl
     */
    private $_acl;
    
    
    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action::init()
     */
    public function init()
    {
        $this->_helper->layout()->disableLayout();
        $this->view->base_url = BASE_URL;
        $this->baseDojoFolder = '/dojotoolkit';
        
        $config = new Zend_Config($this->getInvokeArg('bootstrap')->getOptions());
        $confLayout = $config->zwei->layout;
        
        if (!empty($confLayout->dojoTheme)) $this->_dojoTheme = $confLayout->dojoTheme;
        if (!empty($confLayout->template)) $this->_dojoTheme = $confLayout->layout->template;
        $this->view->jsLoadModuleFunction = isset($confLayout->mainPane) && $confLayout->mainPane == 'dijitTabs' ? 'loadModuleTab' : 'cargarPanelCentral';
        
        if (!empty($this->_request->theme)) $this->_dojoTheme = $this->_request->theme;
        if (!empty($this->_request->template)) $this->_template = $this->_request->template;
                
        Zend_Dojo::enableView($this->view);
        $this->view->addHelperPath('Zend/Dojo/View/Helper/', 'Zend_Dojo_View_Helper');
        $this->view->dojo()
        ->setLocalPath (PROTO.$_SERVER['HTTP_HOST'].$this->baseDojoFolder.'/dojo/dojo.js')
        ->setDjConfig(
            array(
                'parseOnLoad' => 'true', 
                'isDebug' => 'false', 
                'locale' => 'es',
                'packages' => array(
                     array('name' => 'dojo', 'location' => $this->baseDojoFolder . '/dojo'),
                     array('name' => 'dijit', 'location' => $this->baseDojoFolder . '/dijit'),
                     array('name' => 'dojox', 'location' => $this->baseDojoFolder . '/dojox'),
                     array('name' => 'zwei', 'location' => BASE_URL.'/js/libs/zwei'),
                )
            )
        );

        $this->view->headStyle()->appendStyle('
            @import "'.$this->baseDojoFolder.'/dijit/themes/'.$this->_dojoTheme.'/'.$this->_dojoTheme.'.css";
        ');    

        try {
            $settings = new SettingsModel();
            $result = $settings->find('titulo_adm')->current();
            $this->_sitename = $result->value;
            $this->view->adminTitle = $result->value;
        } catch (Zend_Db_Exception $e){
            Debug::write($e->getMessage());
        }

        if ($this->_template != '' && $this->_template != 'dojo') {
            $this->view->headStyle()->appendStyle('
                @import "'.BASE_URL.'css/'.$this->_template.'.css";
            ');         
        } else {
            $this->view->headStyle()->appendStyle('
                .'.$this->_dojoTheme.'{
                   color: #131313;
                   font-family: Verdana,Arial,Helvetica,sans-serif;
                   font-size: 0.688em;
                }
            ');
        }
    }

    private function enableDojo()
    {
        $this->view->bodyClass = $this->_dojoTheme;
        
        $this->view->dojo()
        ->requireModule("dojox.widget.Standby")
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
        ->requireModule("dojox.layout.ExpandoPane")
        ->requireModule("dijit.layout.TabContainer")
        ->requireModule("dijit.Dialog")
        ->requireModule("dijit.form.Form")
        ->requireModule("dijit.form.TextBox")
        ->requireModule("dijit.form.Textarea")
        ->requireModule("dijit.form.SimpleTextarea")
        ->requireModule("dijit.form.Button")
        ->requireModule("dijit.form.DateTextBox")
        ->requireModule("dijit.form.FilteringSelect")
        ->requireModule("dijit.form.TimeTextBox")
        ->requireModule("dijit.form.ComboBox")
        ->requireModule("dojox.grid.DataGrid")
        ->requireModule("dojox.grid.EnhancedGrid")
        ->requireModule("dojox.grid.enhanced.plugins.Pagination")
        ->requireModule("dojox.form.CheckedMultiSelect")
        ->requireModule("dojox.form.FileInput")
        ->requireModule("dojox.form.Uploader")
        ->requireModule("dojox.form.uploader.plugins.IFrame")
        ->requireModule("dojox.encoding.digests.MD5")
        ->requireModule("dojo.data.ItemFileWriteStore")
        ->requireModule("dojo.data.ItemFileReadStore")
        ->requireModule("dojox.data.QueryReadStore")
        ->requireModule("dojo.date.locale")
        ->requireModule("dojox.widget.DialogSimple")
        ->requireModule("dojox.widget.Toaster")
        ->requireModule("zwei.form.ValidationTextarea")
        ;
    
        $this->view->headStyle()->appendStyle('
            @import "'.$this->baseDojoFolder.'/dojox/grid/resources/Grid.css";
            @import "'.$this->baseDojoFolder.'/dojox/grid/resources/'.$this->_dojoTheme.'Grid.css";
            @import "'.$this->baseDojoFolder.'/dojox/grid/enhanced/resources/'.$this->_dojoTheme.'/EnhancedGrid.css";
            @import "'.$this->baseDojoFolder.'/dojox/grid/enhanced/resources/EnhancedGrid_rtl.css";
            @import "'.$this->baseDojoFolder.'/dojox/form/resources/CheckedMultiSelect.css";
            @import "'.$this->baseDojoFolder.'/dojox/layout/resources/ExpandoPane.css";
            @import "'.$this->baseDojoFolder.'/dojox/form/resources/FileInput.css";
            @import "'.BASE_URL.'css/admin.css?version='.$this->_version.'";
        ');
    }

    public function indexAction()
    {
        $this->enableDojo();
        if (!Zwei_Admin_Auth::getInstance()->hasIdentity()) $this->_redirect('admin/login');
        $userInfo = Zend_Auth::getInstance()->getStorage()->read();
        $this->view->user_name = $userInfo->user_name;
        $this->view->first_names = $userInfo->first_names;
        $this->view->last_names = $userInfo->last_names;
        $this->view->user_id = $userInfo->id;
        $config = new Zend_Config($this->getInvokeArg('bootstrap')->getOptions());
        $this->view->layout = isset($config->zwei->layout->mainPane) ? "'".$config->zwei->layout->mainPane."'" : 'undefined';

        if (!empty($this->_template)) {
            $this->_helper->viewRenderer("index-$this->_template");
            if ($this->_template != 'dojo') { //en fase experimental
                $modules = new AclModulesModel();
                $modules->setApproved();
                $tree = $modules->getTree();
                $menu = new Zwei_Utils_Menu($tree);
                $this->view->list=$menu->display();
                $this->view->content = "<center><img width=\"960\" src=\"".BASE_URL."images/satelite.jpg\"/></center>";
            }
        }
    }


    public function componentsAction()
    {
        $logger = new Zend_Log();
        $writer = new Zend_Log_Writer_Stream('php://output');
        $logger->addWriter($writer);
        
        if ($this->getRequest()->getParam('p')) {
            $component = $this->getRequest()->getParam('p');

            if (Zwei_Admin_Acl::isUserAllowed($component, "LIST") || Zwei_Admin_Acl::isUserAllowed($component, "EDIT") || Zwei_Admin_Acl::isUserAllowed($component, "ADD")) {
                $file = Zwei_Admin_Xml::getFullPath($component);
                
                try {
                    $xml = new Zwei_Admin_Xml($file, 0, 1);
                } catch (Exception $e) {
                    $logger->log($e->getCode()."-".$e->getMessage(), Zend_Log::ERR);
                    $this->view->content = "Error al parsear $file";
                    $this->render();
                }

                if (stristr($xml->getAttribute('type'), '.')) {
                    list($controller, $action) = explode('.', $xml->getAttribute('type'));
                } else {
                    $action = 'index';
                    $controller = $xml->getAttribute('type');
                }
                $this->view->content =  $this->view->action($action, $controller, 'components', $this->getRequest()->getParams());
            } else {
                $this->view->content = "Acceso denegado a módulo $component";
            }
        } else {
            $this->view->component = "index";
        }
    }

    public function modulesAction()
    {
        if (!Zwei_Admin_Auth::getInstance()->hasIdentity()) $this->_redirect('index/login');

        Zend_Dojo::enableView($this->view);

        $this->_helper->ContextSwitch
        ->setAutoJsonSerialization(false)
        ->addActionContext('index', 'json')
        ->initContext();

        //$this->_helper->viewRenderer->setNoRender(true);
        $modules = new AclModulesModel();
        $modules->setApproved();

        $tree = $modules->getTree();

        $treeObj = new Zend_Dojo_Data('id', $tree);
        $treeObj->setLabel('label');

        $this->view->content = Zend_Json::prettyPrint($treeObj->toJson());
    }
    
    public function loginAction()
    {
        $this->view->headStyle()->appendStyle('
            @import "'.BASE_URL.'css/admin.css";
        ');
    
        if (Zwei_Admin_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect(BASE_URL. 'admin');
        }
        
        $this->view->bodyClass = $this->_dojoTheme;
        
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
                    // Obtener toda la info de usuario, excepto la password
                    $userInfo = $authAdapter->getResultRowObject(null, 'password');
    
                    $config = new Zend_Config($this->getInvokeArg('bootstrap')->getOptions());
    
                    if (isset($config->zwei->session->namespace)) $userInfo->sessionNamespace = $config->zwei->session->namespace;
    
                    $authStorage = $auth->getStorage();
                    $authStorage->write($userInfo);
    
                    $this->_redirect(BASE_URL.'admin');
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
        $this->_redirect(BASE_URL.'admin/login');
    }
    
    /**
     * Gets the adapter for authentication against a database table
     *
     * @return object
     */
    protected function getAuthAdapter()
    {
        $resource = $this->getInvokeArg('bootstrap')->getResource("multidb");
        
        $dbAdapter = isset($resource) && $resource->getDb("auth") ?
        $resource->getDb("auth") :
        Zend_Db_Table::getDefaultAdapter();
    
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
        $loginForm->setAction($this->_request->getBaseUrl().'/admin/login/')
        ->setTranslator(new Zend_Translate('array',array("Value is required and can't be empty"=>"Este valor no puede ir vacío"),'es'))
        ->setMethod('post')
        ->addElement($username)
        ->addElement($password)
        ->addElement($submit);
    
        return $loginForm;
    }
}





