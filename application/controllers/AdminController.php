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
    private $_dojoTheme = 'claro';
    
    /**
     *
     * @var string
     */
    private $_template = 'default';
    
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
     * 
     * @var Zend_Config
     */
    private $_config;
    
    /**
     * Post Constructor
     * @return void
     * @see Zend_Controller_Action::init()
     */
    public function init()
    {
        $this->_config = Zwei_Controller_Config::getOptions();
        $confLayout = $this->_config->zwei->layout;
        $r = $this->getRequest();
        
        $userInfo = Zend_Auth::getInstance()->getStorage()->read();
        if ($userInfo) $this->_acl = new Zwei_Admin_Acl();
        
        $this->baseDojoFolder = isset($this->_config->zwei->js->dojo->baseUrl) ? $this->_config->zwei->js->dojo->baseUrl : '/dojotoolkit';
        if (isset($this->_config->resources->dojo->cdnbase)) $this->baseDojoFolder = $this->_config->resources->dojo->cdnbase . '/' . $this->_config->resources->dojo->cdnversion;
        
        $this->view->noCache = isset($this->_config->zwei->resources) ? $this->_config->zwei->resources->noCache : '';
        
        $this->view->isObsoleteBrowser = false;
        
        $ieMinVersion = isset($this->_config->zwei->browser->ieMinVersion) ? $this->_config->zwei->browser->ieMinVersion : null;
        
        if ($ieMinVersion) {
            $userAgent = new Zwei_UserAgent();
            if ($userAgent->getBrowser() === Zwei_UserAgent::BROWSER_IE && $userAgent->getVersion() < $ieMinVersion) {
                $this->view->isObsoleteBrowser = true;
                $this->view->ieMinVersion = $ieMinVersion;
            }
        }
        
        
        if ($confLayout->dojoTheme) $this->_dojoTheme = $confLayout->dojoTheme;
        if ($r->getParam('theme')) $this->_dojoTheme = $r->getParam('theme');
        
        $this->_template = $confLayout->template ? $confLayout->template : "default";
        $this->_template = $r->getParam('template', $this->_template);
        $this->view->template = $this->_template;
        
        $this->view->headStyle()->appendStyle('
            @import "'.$this->baseDojoFolder.'/dijit/themes/'.$this->_dojoTheme.'/'.$this->_dojoTheme.'.css";
        ');

        $settings = new SettingsModel();
        
        try {
            $this->view->adminTitle = $settings->find('titulo_adm')->current()->value;
            $this->view->urlLogoOper = $settings->find('url_logo_oper')->current()->value;
        } catch (Zend_Db_Exception $e){
            Console::warn($e->getMessage(), APPLICATION_ENV !== 'production');
        }

        if ($this->_template != '' && $this->_template != 'dojo') {
            $this->view->headStyle()->appendStyle('
                @import "'.BASE_URL.'css/'.$this->_template.'.css";
            ');         
        }
        
        $color = $this->_dojoTheme === 'flat' ? '#02456D' : '#131313';
        
        $this->view->headStyle()->appendStyle('
            .'.$this->_dojoTheme.'{
               color: '.$color.';
               font-family: Arial,Verdana, Helvetica,sans-serif;
               font-size: 0.75em;
            }
        ');
        
    }

    /**
     * Agregar los temas de dojo y estilos css necesarios al head.
     * @return void
     */
    private function enableDojo()
    {
        $this->view->bodyClass = $this->_dojoTheme;
        
        $this->view->dojo()
        ->requireModule("dojox.widget.Standby")
        ->requireModule("dijit.form.Form")
        ->requireModule("zwei.Utils")
        ->requireModule("zwei.Admportal")
        ->requireModule("zwei.Form")
        ->requireModule("dojox.grid.enhanced.plugins.Pagination")
        ->requireModule("dojox.form.CheckedMultiSelect")
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
            @import "'.$this->baseDojoFolder.'/dojo/resources/dnd.css";
            @import "'.BASE_URL.'css/admin.css?version='.$this->view->noCache.'";
        ');
    }
    
    /**
     * Carga librerías javascripts configuradas.
     * @return void
     */
    private function enableJavascriptLibs()
    {
        $this->view->javascriptLibs = isset($this->_config->zwei->javascript->libs) ? $this->_config->zwei->javascript->libs->toArray() : array();
    }
    
    
    /**
     * Acción layout pantalla principal}
     * @return void
     */
    public function indexAction()
    {
        $this->enableJavascriptLibs();
        $this->enableDojo();
        
        if (!Zwei_Admin_Auth::getInstance()->hasIdentity()) {
            $this->_redirect('admin/login');
        } else {
            $userInfo = Zend_Auth::getInstance()->getStorage()->read();
            $this->view->user_name = $userInfo->user_name;
            $this->view->first_names = $userInfo->first_names;
            $this->view->last_names = $userInfo->last_names;
            $this->view->user_id = $userInfo->id;
            $this->view->layout = isset($this->_config->zwei->layout->mainPane) ? "'".$this->_config->zwei->layout->mainPane."'" : 'undefined';//Para backward compatibility, TODO deprecar
            $this->view->multiForm = isset($this->_config->zwei->form->multiple) && !empty($this->_config->zwei->form->multiple) ? 'true' : 'false';//Para backward compatibility, TODO deprecar
    
            if ($this->_template != 'default') {
                $this->_helper->viewRenderer("index-$this->_template");
            }
        }
    }

    /**
     * Acción parseadora de componentes XML
     * @return void
     */
    public function componentsAction()
    {
        if ($this->getRequest()->getParam('p')) {
            $component = $this->getRequest()->getParam('p');
            
            if (!Zwei_Admin_Auth::getInstance()->hasIdentity()) {
                $this->_redirect('admin/login');
            } else {
                $file = Zwei_Admin_Xml::getFullPath($component);
                if (!file_exists($file) && pathinfo($file, PATHINFO_EXTENSION) === 'xml') {
                    throw new Zwei_Exception("No existe archivo $file.");
                }
                
                try {
                    $xml = new Zwei_Admin_Xml($file, LIBXML_NOWARNING, 1);
                } catch (Exception $e) {
                    $message = "Error al intentar parsear $file";
                    Console::error($message);
                    $this->view->content = "<h2>$message<h2><center><img src=\"".BASE_URL."images/exception-xml.jpg\"/></center>";
                    $this->render('index');
                }
                
                $aclComponent = $xml->getAttribute('aclComponent') ? $xml->getAttribute('aclComponent') : $component;
                
                if ($this->_acl->isUserAllowed($aclComponent, "LIST") || $this->_acl->isUserAllowed($aclComponent, "EDIT") || $this->_acl->isUserAllowed($aclComponent, "ADD")) {
                    
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
                    $this->view->content = "<h2>Acceso denegado a módulo $aclComponent</h2><center><img src=\"".BASE_URL."images/access-denied.jpg\"/ alt=\"\"></center>";
                }
            }
        } else {
            $this->view->component = "index";
        }
    }
    
    /**
     * Accion módulos autorizados en json
     * @return void
     */
    public function modulesAction()
    {
        if (!Zwei_Admin_Auth::getInstance()->hasIdentity()) {
            $this->_redirect('admin/login');
        } else {
            
            Zend_Dojo::enableView($this->view);
            
            $this->_helper->ContextSwitch
            ->setAutoJsonSerialization(false)
            ->addActionContext('index', 'json')
            ->initContext();
            
            //$this->_helper->viewRenderer->setNoRender(true);
            $modules = new AclModulesModel();
            
            $this->view->tree = $modules->getTree();
            
        }
    }
    
    /**
     * Accion menú principal
     * @return void
     */
    public function menuAction()
    {
        if (!Zwei_Admin_Auth::getInstance()->hasIdentity()) {
            $this->_redirect('admin/login');
        } else {
        
            Zend_Dojo::enableView($this->view);
            //$this->_helper->viewRenderer->setNoRender(true);
            $modules = new AclModulesModel();
        
            $this->view->tree = $modules->getTree();
        }
    }
    
    /**
     * Accion pantalla login.
     * @return void
     */
    public function loginAction()
    {
        $this->view->headStyle()->appendStyle('
            @import "'.BASE_URL.'css/admin.css";
        ');
    
        if (Zwei_Admin_Auth::getInstance()->hasIdentity()) {
            //$this->_redirect(BASE_URL. 'admin');
        }
        
        $this->view->bodyClass = $this->_dojoTheme;
        
        $r = $this->getRequest();
        $loginForm = $this->getLoginForm();
    
        $errorMessage = "";
    
        if ($r->isPost()) {
            if ($loginForm->isValid($r->getPost())) {
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
                    
                    $params = array();
                    $r = $this->getRequest();
                    if ($r->getParam('template')) $params['template'] = $r->getParam('template');
                    if ($r->getParam('theme')) $params['theme'] = $r->getParam('theme');
                    
                    
                    $this->_helper->redirector('index', 'admin', 'default', $params);
                } else {
                    $errorMessage = "Usuario o Password incorrectos.";
                }
            }
        }
        $this->view->errorMessage = $errorMessage;
        $this->view->loginForm = $loginForm;
    }
    
    /**
     * Accion logout.
     * @return void
     */
    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_redirect(BASE_URL.'admin/login');
    }
    
    
    /**
     * login form
     *
     * @return Zend_Dojo_Form
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
        
        $theme = new Zend_Form_Element_Hidden('theme');
        $theme->setValue($this->getRequest()->getParam('theme'));
        $theme->removeDecorator('HtmlTag');
        $theme->removeDecorator('Label');
        
        $template = new Zend_Form_Element_Hidden('template');
        $template->setValue($this->getRequest()->getParam('template'));
        $template->removeDecorator('HtmlTag');
        $template->removeDecorator('Label');
        
        $submit = new Zend_Form_Element_Submit('login');
        $submit->setLabel('Login');
        $submit->setAttrib("class", "button");
        
    
        $loginForm = new Zend_Dojo_Form();
        $loginForm->setAction($this->_request->getBaseUrl() . '/admin/login/')
        ->setTranslator(new Zend_Translate('array', array("Value is required and can't be empty"=>"Este valor no puede ir vacío"), 'es'))

        ->setMethod('post')
        ->setAttrib('id', 'loginForm')
        //->setAttrib('onsubmit', "this['password'].value=hex_md5(this['password'].value)")
        ->addElement($username)
        ->addElement($password)
        ->addElement($theme)
        ->addElement($template)
        ->addElement($submit);
    
        return $loginForm;
    }
    
    /**
     * Acción iframe
     * @return void
     */
    public function iframeAction()
    {
        $this->view->src = $this->getRequest()->getParam('p');
        $modules = new AclModulesModel();
        $findModule = $modules->findModule($this->view->src, 'id');
        $this->view->moduleId = $findModule ? $findModule->id : '0';
    }
}
