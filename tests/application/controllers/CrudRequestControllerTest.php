<?php

class CrudRequestControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
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

    public function testIndexAction()
    {
        
        $params = array('action' => 'index', 'controller' => 'CrudRequest', 'module' => 'default');
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



