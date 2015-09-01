<?php
/**
 * Ruta de instalación por defecto de adaptador MonetDB en Debian y Ubuntu.
 * @link https://www.monetdb.org/downloads/deb/
 */
require_once '/usr/share/php/monetdb/php_monetdb.php';

/**
 * 
 * Adaptador de tabla MonetDB
 *
 */
abstract class Zwei_Db_TableMonet implements Zwei_Admin_ModelInterface
{
    /**
     * The table name.
     *
     * @var string
     */
    protected $_name = null;
    
    /**
     * The primary key column or columns.
     * A compound key should be declared as an array.
     * You may declare a single-column primary key
     * as a string.
     *
     * @var mixed
     */
    protected $_primary = null;
    
    /**
     * 
     * @var Zend_Config
     */
    protected $_params;
    
    /**
     * @var array
     */
    protected $_adapter;
    
    /**
     * 
     * @var monetdb_connect resource
     */
    protected $_connection;
    /**
     * 
     */
    protected $_isFiltered = true;
    /**
     * Hash que indica si deben ser validados los permisos modulo-usuario-accion.
     *
     * @var array
     */
    protected $_validateXmlAcl = array('EDIT' => false, 'ADD' => false, 'DELETE' => false, 'LIST' => false);
    
    
    public function __construct($adapter = null)
    {
        $options = Zwei_Controller_Config::getOptions();
        if ($adapter === null) {
            if (isset($options->zwei->monetdb)) {
                $this->_params = $options->zwei->monetdb->params->toArray(); 
            }
        } else {
            $this->_params = $options->zwei->monet_multidb->{$adapter}->toArray();
        }

        if (!isset($this->_params['dialect'])) $this->_params['dialect'] = 'sql';
        if (!isset($this->_params['host'])) $this->_params['host'] = '127.0.0.1';
        if (!isset($this->_params['port'])) $this->_params['port'] = '50000';
        if (!isset($this->_params['username'])) $this->_params['username'] = 'monetdb';
        if (!isset($this->_params['password'])) $this->_params['password'] = 'monetdb';
        if (!isset($this->_params['database'])) $this->_params['database'] = 'test';
        if (!isset($this->_params['hashfunc'])) $this->_params['hashfunc'] = '';
        
        $this->connect();
        $this->init();
    }
    
    /**
     * Initialize object
     *
     * Called from {@link __construct()} as final step of object instantiation.
     *
     * @return void
     */
    public function init()
    {
    }
    
    /**
     * Configura el adaptador de base de datos segun valores de atributo de configuración.
     * resources.multidb.{$adapter}.*
     *
     * en archivo /application/configs/application.ini
     *
     *
     * @param $adapter
     * @return void
     */
    public function connect($params = array())
    {
        $this->_connection = monetdb_connect(
            isset($params['dialect']) ? $params['dialect'] : $this->_params['dialect'], 
            isset($params['host']) ? $params['host'] : $this->_params['host'], 
            isset($params['port']) ? $params['port'] : $this->_params['port'],  
            isset($params['username']) ? $params['username'] : $this->_params['username'],  
            isset($params['password']) ? $params['password'] : $this->_params['password'],
            isset($params['database']) ? $params['database'] : $this->_params['database'],
            isset($params['hashfunc']) ? $params['hashfunc'] : $this->_params['hashfunc'] 
        );
    }
    
    
    public function select()
    {
        return "SELECT * from {$this->_name}";
    }
    
    public function fetchAll($select = null)
    {
        $data = array();
        
        if (!$select) {
            $select = $this->select();
        }
        
        $result = monetdb_query($this->_connection, $select)
            or trigger_error(monetdb_last_error(), E_USER_ERROR);
        
        while ($row =  monetdb_fetch_object($result)) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    
    public function fetchRow()
    {
    
    }
    
    public function isFiltered()
    {
        return $this->_isFiltered;
    }
    
    public function info($key)
    {
        if ($key === 'primary') {
            return $this->_primary;
        }
    }
}