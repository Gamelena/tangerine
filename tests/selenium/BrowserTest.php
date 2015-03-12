<?php
class BrowserTest extends PHPUnit_Extensions_Selenium2TestCase
{
    protected function setUp()
    {
        $this->bootstrap = new Zend_Application(
            APPLICATION_ENV,
            $_ENV['APPLICATION_CONFIG']
        );
        $options = $this->bootstrap->getBootstrap()->getOptions();
       
        defined('PHPUNIT_BASE_URL') 
            || define('PHPUNIT_BASE_URL', $options['zwei']['uTesting']['httpHost']);
        
        $this->setBrowser('opera');
        $this->setBrowserUrl(PHPUNIT_BASE_URL);
    }
    
    public function initUserInfo()
    {
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
    
    
    public function testLogin()
    {
        $this->url('admin');
        $model = new SettingsModel();
        $row = $model->find('titulo_adm')->current();
        
        
        $this->assertEquals($row->value, htmlentities($this->title()));
        
        $this->byId('username')->value(PHPUNIT_USERNAME);
        $this->byId('password')->value(PHPUNIT_PASSWORD);
        $this->byId('login')->click();
        $this->byId('loginForm')->submit();
        
        sleep(PHPUNIT_WAITSECONDS);
        
        $modules = new AclModulesModel();
        $this->initUserInfo();
        $this->iterateTree($modules->getTree());
        
        sleep(PHPUNIT_WAITSECONDS*5);
    }
    
    public function iterateTree($tree)
    {
        $openLater = array();
        foreach ($tree as $branch) {
            try {
                $item = $this->byId('dijitEditorMenuModule'.$branch['id']);
                $item->click();
                sleep(PHPUNIT_WAITSECONDS/5);
            } catch (PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
                echo "No pude abrir item dijitEditorMenuModule{$branch['id']}, parent {$branch['parent_id']}\n";
                return true;
            }
            if (isset($branch['children'])) {
                var_dump($branch);
                $this->iterateTree($branch['children']);
            }
        }
    }
}