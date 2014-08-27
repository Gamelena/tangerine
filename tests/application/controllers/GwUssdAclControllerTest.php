<?php
class GwUssdAclControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{

    public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        $options = $this->bootstrap->getOptions(); $_SERVER['HTTP_HOST'] = $options['zwei']['uTesting']['httpHost'];
        parent::setUp();
    }
    
    
    public function testIndexAction()
    {
        $params = array('action' => 'index', 'controller' => 'GwUssdAcl', 'module' => 'default');
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        $this->dispatch($url);
    
        // assertions
        $this->assertModule($urlParams['module']);
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
    }
}