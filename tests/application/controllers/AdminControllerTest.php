<?php


class AdminControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{

    public function setUp()
    {
        $this->bootstrap = $this->bootstrap = new Zend_Application(
            APPLICATION_ENV,
            $_ENV['APPLICATION_CONFIG']
        );
        $options = $this->bootstrap->getBootstrap()->getOptions();
        $_SERVER['HTTP_HOST'] = $options['gamelena']['uTesting']['httpHost'];
        parent::setUp();
    }

    public function initUserInfo()
    {
        $this->getFrontController()->setParam('bootstrap', $this->bootstrap->getBootstrap());
        $authAdapter = Gamelena_Admin_Auth::getInstance()->getAuthAdapter();
        
        $username = PHPUNIT_USERNAME;
        $password = PHPUNIT_PASSWORD;
        $auth = Zend_Auth::getInstance();
        
        $authAdapter->setIdentity($username)
        ->setCredential($password);
        $result = $auth->authenticate($authAdapter);
        
        if ($result->isValid())
        {
            Gamelena_Admin_Auth::initUserInfo($authAdapter);
        } else {
            echo "Usuario '$username' o Password '$password' incorrectos.";
        }
    }

    
    public function testIndexNotAuthenticatedAction()
    {
        $params = array('action' => 'index', 'controller' => 'Admin', 'module' => 'default');
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        $this->dispatch($url);
    
        // assertions
        $this->assertModule($urlParams['module']);
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
        $this->assertRedirectTo('/admin/login');
    }
    
    
    public function testIndexAction()
    {
        $this->initUserInfo();
        $params = array('action' => 'index', 'controller' => 'Admin', 'module' => 'default');
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        $this->dispatch($url);
        
        // assertions
        $this->assertModule($urlParams['module']);
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
        $this->assertQueryCount('div#borderContainer', 1);
    }

    public function testLoginAction()
    {
        
        $params = array('action' => 'login', 'controller' => 'Admin', 'module' => 'default');
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        $this->dispatch($url);
        $this->assertQueryCount('form#loginForm', 1);
        //$this->assertQueryContentContains('.login-form .header h1', 'Suscripciones');
        
    }


    
    
    public function testLogoutAction()
    {
        
        $params = array('action' => 'logout', 'controller' => 'Admin', 'module' => 'default');
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        $this->dispatch($url);
        
        // assertions
        $this->assertModule($urlParams['module']);
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
        
        $this->assertRedirect('/admin/login');
    }

    public function testComponentsAction()
    {
        
        $params = array('action' => 'components', 'controller' => 'Admin', 'module' => 'default');
        
        $model = new DbTable_AclModules();
        $select = $model->select()->where("approved='1'");
        $modules = $model->fetchAll($select);
        
        foreach ($modules as $module) {
            $params['p'] = $module->module;
            $urlParams = $this->urlizeOptions($params);
            $url = $this->url($urlParams);
            $this->dispatch($url);
            
            // assertions
            $this->assertModule($urlParams['module']);
            $this->assertController($urlParams['controller']);
            $this->assertAction($urlParams['action']);
        }
        /*
        $this->assertQueryContentContains(
            'div#view-content p',
            'View script for controller <b>' . $params['controller'] . '</b> and script/action name <b>' . $params['action'] . '</b>'
            );
            */
    }

    public function testModulesAction()
    {
        
        $this->testLoginAction();
        $params = array('action' => 'modules', 'controller' => 'Admin', 'module' => 'default');
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        $this->dispatch($url);
        
        // assertions
        $this->assertModule($urlParams['module']);
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
        /*
        $this->assertQueryContentContains(
            'div#view-content p',
            'View script for controller <b>' . $params['controller'] . '</b> and script/action name <b>' . $params['action'] . '</b>'
            );
            */
    }

    public function testIframeAction()
    {
        
        $params = array('action' => 'iframe', 'controller' => 'Admin', 'module' => 'default');
        $params['p'] = 'http://192.168.11.66:8080/gda-workbench/org.kie.workbench.KIEWebapp/KIEWebapp.html';
        
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        $this->dispatch($url);
        
        // assertions
        $this->assertModule($urlParams['module']);
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
        /*
        $this->assertQueryContentContains(
            'div#view-content p',
            'View script for controller <b>' . $params['controller'] . '</b> and script/action name <b>' . $params['action'] . '</b>'
            );
            */
    }


}