<?php
class Gamelena_Db_Mongo implements Gamelena_Admin_ModelInterface
{
    
    /**
     * 
     * @var MongoClient
     */
    private $_client;
    /**
     * Initialize object
     *
     * Called from {@link __construct()} as final step of object instantiation.
     *
     * @return void
     */
    public function init()
    {
        $this->_client = new MongoClient();
    }
    
    
    public function __construct()
    {
        $this->init();
    }
    
    /**
     * Sets the default Zend_Db_Adapter_Abstract for all Zend_Db_Table objects.
     *
     * @param  mixed $db Either an Adapter object, or a string naming a Registry key
     * @return void
     */
    public static function setDefaultAdapter($db = null)
    {
        self::$_defaultDb = self::_setupAdapter($db);
    }
    
    
    public function select() 
    {
        
    }
    
    public function insert(array $data)
    {
        
    }
    
    public function update(array $data, $where)
    {
        
    }
    
    public function fetchAll()
    {
        
    }
    
    public function fetchRow()
    {
        
    }
}
