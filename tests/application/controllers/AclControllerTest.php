<?php

class AclControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{

    public function setUp()
    {
        $this->bootstrap = $this->bootstrap = new Zend_Application(
            APPLICATION_ENV,
            $_ENV['APPLICATION_CONFIG']
        );
        $options = $this->bootstrap->getBootstrap()->getOptions();
        $_SERVER['HTTP_HOST'] = $options['zwei']['uTesting']['httpHost'];
        parent::setUp();
    }
    
    public function initUserInfo()
    {
        $this->getFrontController()->setParam('bootstrap', $this->bootstrap->getBootstrap());
        $authAdapter = Zwei_Admin_Auth::getInstance()->getAuthAdapter();
    
        $username = PHPUNIT_USERNAME;
        $password = PHPUNIT_PASSWORD;
        $auth = Zend_Auth::getInstance();
    
        $authAdapter->setIdentity($username)
        ->setCredential($password);
        $result = $auth->authenticate($authAdapter);
    
        if ($result->isValid())
        {
            Zwei_Admin_Auth::initUserInfo($authAdapter);
        } else {
            echo "Usuario '$username' o Password '$password' incorrectos.";
        }
    }
    
    public function testIndexAction()
    {
        $this->initUserInfo();
        $params = array('action' => 'index', 'controller' => 'Acl', 'module' => 'default');
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        $this->dispatch($url);
        
        // assertions
        $this->assertModule($urlParams['module']);
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
//         $this->assertQueryContentContains(
//             'div#view-content p',
//             'View script for controller <b>' . $params['controller'] . '</b> and script/action name <b>' . $params['action'] . '</b>'
//             );
    }

    public function testIsUserAllowedAction()
    {
        $this->initUserInfo();
        $params = array('action' => 'isUserAllowed', 'controller' => 'Acl', 'module' => 'default', 'itemId' => '1');

        
        // assertions
        $model = new DbTable_AclModules();
        $modules = $model->fetchAll("approved='1' AND type='xml' AND module IS NOT NULL");
        
        foreach ($modules as $module) {
            $params['p'] = $module->module;
            $urlParams = $this->urlizeOptions($params);
            $url = $this->url($urlParams);
            $this->dispatch($url);
            
            $this->assertModule($urlParams['module']);
            $this->assertController($urlParams['controller']);
            $this->assertAction($urlParams['action']);
        }
//         $this->assertQueryContentContains(
//             'div#view-content p',
//             'View script for controller <b>' . $params['controller'] . '</b> and script/action name <b>' . $params['action'] . '</b>'
//             );
    }

    public function testUserHasRoleAllowedAction()
    {
        $this->initUserInfo();
        $params = array('action' => 'userHasRoleAllowed', 'controller' => 'Acl', 'module' => 'default', 'itemId' => '1');
        
        // assertions
        $model = new DbTable_AclModules();
        $modules = $model->fetchAll("approved='1' AND type='xml' AND module IS NOT NULL");
        
        foreach ($modules as $module) {
            $params['p'] = $module->module;
            $urlParams = $this->urlizeOptions($params);
            $url = $this->url($urlParams);
            $this->dispatch($url);
            
            $this->assertModule($urlParams['module']);
            $this->assertController($urlParams['controller']);
            $this->assertAction($urlParams['action']);
        }
//         $this->assertQueryContentContains(
//             'div#view-content p',
//             'View script for controller <b>' . $params['controller'] . '</b> and script/action name <b>' . $params['action'] . '</b>'
//             );
    }

    public function testUserHasGroupsAllowedAction()
    {
        $this->initUserInfo();
        $params = array('action' => 'userHasGroupsAllowed', 'controller' => 'Acl', 'module' => 'default');
            
        // assertions
        $model = new DbTable_AclModules();
        $modules = $model->fetchAll("approved='1' AND type='xml' AND module IS NOT NULL");
        
        foreach ($modules as $module) {
            $params['p'] = $module->module;
            $urlParams = $this->urlizeOptions($params);
            $url = $this->url($urlParams);
            $this->dispatch($url);
            
            $this->assertModule($urlParams['module']);
            $this->assertController($urlParams['controller']);
            $this->assertAction($urlParams['action']);
        }
//         $this->assertQueryContentContains(
//             'div#view-content p',
//             'View script for controller <b>' . $params['controller'] . '</b> and script/action name <b>' . $params['action'] . '</b>'
//             );
    }

    public function testGetUserInfoAction()
    {
        $this->initUserInfo();
        $params = array('action' => 'getUserInfo', 'controller' => 'Acl', 'module' => 'default');
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        $this->dispatch($url);
        
        // assertions
        $this->assertModule($urlParams['module']);
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
//         $this->assertQueryContentContains(
//             'div#view-content p',
//             'View script for controller <b>' . $params['controller'] . '</b> and script/action name <b>' . $params['action'] . '</b>'
//             );
    }

}