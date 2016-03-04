<?php
class Zwei_Test_ControllerTestCase extends Zend_Test_PHPUnit_ControllerTestCase
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
    
        if ($result->isValid()) {
            Zwei_Admin_Auth::initUserInfo($authAdapter);
        } else {
            echo "Usuario '$username' o Password '$password' incorrectos.";
        }
    }
}