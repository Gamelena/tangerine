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
        $config = Zwei_Controller_Config::getOptions();
        $confLayout = $config->zwei->layout;
        
        $userInfo = Zend_Auth::getInstance()->getStorage()->read();
        if ($userInfo) $this->_acl = new Zwei_Admin_Acl();
        
        $this->view->base_url = BASE_URL;
        $this->baseDojoFolder = isset($config->zwei->js->dojo->baseUrl) ? $config->zwei->js->dojo->baseUrl : '/dojotoolkit';
        if (isset($config->resources->dojo->cdnbase)) $this->baseDojoFolder = $config->resources->dojo->cdnbase . '/' . $config->resources->dojo->cdnversion;
        $this->view->noCache = isset($config->zwei->resources) ? $config->zwei->resources->noCache : ''; 
        
        if (!empty($confLayout->dojoTheme)) $this->_dojoTheme = $confLayout->dojoTheme;
        if (!empty($confLayout->template)) $this->_dojoTheme = $confLayout->layout->template;
        
        if (!empty($this->_request->theme)) $this->_dojoTheme = $this->_request->theme;
        if (!empty($this->_request->template)) $this->_template = $this->_request->template;
        
        $this->view->headStyle()->appendStyle('
            @import "'.$this->baseDojoFolder.'/dijit/themes/'.$this->_dojoTheme.'/'.$this->_dojoTheme.'.css";
        ');

        $settings = new SettingsModel();
        
        try {
            $this->_sitename = $settings->find('titulo_adm')->current()->value;
            $this->view->adminTitle = $this->_sitename;
            $this->view->urlLogoOper = $settings->find('url_logo_oper')->current()->value;
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
                   font-family: Arial,Verdana, Helvetica,sans-serif;
                   font-size: 0.75em;
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
        ->requireModule("dijit.form.Select")
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
        ->requireModule("zwei.Form")
        ->requireModule("zwei.Utils")
        ->requireModule("zwei.Admportal")
        ;
    
        $this->view->headStyle()->appendStyle('
            @import "'.$this->baseDojoFolder.'/dojox/grid/resources/Grid.css";
            @import "'.$this->baseDojoFolder.'/dojox/grid/resources/'.$this->_dojoTheme.'Grid.css";
            @import "'.$this->baseDojoFolder.'/dojox/grid/enhanced/resources/'.$this->_dojoTheme.'/EnhancedGrid.css";
            @import "'.$this->baseDojoFolder.'/dojox/grid/enhanced/resources/EnhancedGrid_rtl.css";
            @import "'.$this->baseDojoFolder.'/dojox/form/resources/CheckedMultiSelect.css";
            @import "'.$this->baseDojoFolder.'/dojox/layout/resources/ExpandoPane.css";
            @import "'.$this->baseDojoFolder.'/dojox/form/resources/FileInput.css";
            @import "'.$this->baseDojoFolder.'/dojox/widget/Toaster/Toaster.css";
            @import "'.BASE_URL.'css/admin.css?version='.$this->view->noCache.'";
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
        $this->view->layout = isset($config->zwei->layout->mainPane) ? "'".$config->zwei->layout->mainPane."'" : 'undefined';//Para backward compatibility, TODO deprecar
        $this->view->multiForm = isset($config->zwei->form->multiple) && !empty($config->zwei->form->multiple) ? 'true' : 'false';//Para backward compatibility, TODO deprecar

        if (!empty($this->_template)) {
            $this->_helper->viewRenderer("index-$this->_template");
        }
    }


    public function componentsAction()
    {
        if ($this->getRequest()->getParam('p')) {
            $component = $this->getRequest()->getParam('p');
            
            if (!Zwei_Admin_Auth::getInstance()->hasIdentity()) {
                $this->_redirect('admin/login');
            }
            
            if ($this->_acl->isUserAllowed($component, "LIST") || $this->_acl->isUserAllowed($component, "EDIT") || $this->_acl->isUserAllowed($component, "ADD")) {
                $file = Zwei_Admin_Xml::getFullPath($component);
                
                try {
                    $xml = new Zwei_Admin_Xml($file, LIBXML_NOWARNING, 1);
                } catch (Exception $e) {
                    $message = "Error al intentar parsear $file";
                    Debug::write($message);
                    exit("<h2>$message<h2><center><img src=\"".BASE_URL."images/exception-xml.jpg\"/></center>");
                }
                
                if (stristr($xml->getAttribute('type'), '.')) {
                    list($controller, $action) = explode('.', $xml->getAttribute('type'));
                } else if (stristr($xml->getAttribute('type'), '/')) {
                    list($controller, $action) = explode('/', $xml->getAttribute('type'));
                } else {
                    $action = 'index';
                    $controller = $xml->getAttribute('type');
                }
                
                $this->view->content =  $this->view->action($action, $controller, 'components', $this->getRequest()->getParams());
            } else {
                $this->view->content = "<h2>Acceso denegado a módulo $component</h2><center><img src=\"".BASE_URL."images/access-denied.jpg\"/ alt=\"\"></center>";
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

        $tree = $modules->getTree();

        $treeObj = new Zend_Dojo_Data('id', $tree);
        $treeObj->setLabel('label');

        $this->view->content = $treeObj->toJson();
    }
    
    public function loginAction()
    {
        $this->view->headStyle()->appendStyle('
            @import "'.BASE_URL.'css/admin.css";
        ');
    
        if (Zwei_Admin_Auth::getInstance()->hasIdentity()) {
            //$this->_redirect(BASE_URL. 'admin');
        }
        
        $this->view->bodyClass = $this->_dojoTheme;
        
        $request = $this->getRequest();
        $loginForm = $this->getLoginForm();
    
        $errorMessage = "";
    
        if ($request->isPost()) {
            if ($loginForm->isValid($request->getPost())) {
                $authAdapter = Zwei_Admin_Auth::getInstance()->getAuthAdapter();
    
                $username = $loginForm->getValue('username');
                $password = $loginForm->getValue('password');
    
                $auth = Zend_Auth::getInstance();
                
                $authAdapter->setIdentity($username)
                ->setCredential($password);
                $result = $auth->authenticate($authAdapter);
    
                if($result->isValid())
                {
                    // Obtener toda la info de usuario, excepto la password
                    Zwei_Admin_Auth::initUserInfo($authAdapter);
                    
                    $this->_redirect(BASE_URL.'admin');
                } else {
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
     * login form
     *
     * @return object
     */
    protected function getLoginForm()
    {
        $this->view->dojo()->requireModule("dijit.form.Form");
        $this->view->dojo()->requireModule("dijit.form.ValidationTextBox");
        
        $username = new Zend_Dojo_Form_Element_ValidationTextBox('username');
        $username->setAttrib("class", "input");
        $username->setAttrib("dojoType", "dijit.form.ValidationTextBox");
        $username->setAttrib("invalidMessage", "Ingrese Usuario");
        $username->setAttrib("placeHolder", "Usuario");
        $username->setAttrib("required", "true");
        $username->setRequired(true);
    
        $password = new Zend_Dojo_Form_Element_PasswordTextBox('password');
        $password->setAttrib("dojoType", "dijit.form.ValidationTextBox");
        $password->setAttrib("class", "input");
        $password->setAttrib("placeHolder", "Contraseña");
        $password->setAttrib("invalidMessage", "Ingrese Contrase&ntilde;a");
        $password->setAttrib("required", "true");
        $password->setRequired(true);
    
        $submit = new Zend_Form_Element_Submit('login');
        $submit->setLabel('Login');
        $submit->setAttrib("class", "button");
    
        $loginForm = new Zend_Dojo_Form();
        $loginForm->setAction($this->_request->getBaseUrl().'/admin/login/')
        ->setTranslator(new Zend_Translate('array',array("Value is required and can't be empty"=>"Este valor no puede ir vacío"),'es'))

        ->setMethod('post')
        ->addElement($username)
        ->addElement($password)
        ->addElement($submit);
    
        return $loginForm;
    }
    
    public function iframeAction()
    {
        $this->view->src = $this->getRequest()->getParam('p');
    }
}