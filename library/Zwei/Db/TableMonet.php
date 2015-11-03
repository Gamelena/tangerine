<?php

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
     * parámetros de conexión por defecto de monetdb_connect()
     * @var array
     */
    protected $_params = array(
        'dialect' => 'sql',
        'host' => '127.0.0.1',
        'port' => '50000',
        'username' => 'monetdb',
        'password' => 'monetdb',
        'database' => 'demo',
        'hashfunc' => ''
    );
    
    /**
     * @var string
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
    protected $_isFiltered = false;
    
    /**
     * 
     * @var int
     */
    protected $_count = 0;
    
    /**
     * Hash que indica si deben ser validados los permisos modulo-usuario-accion en api rest.
     *
     * @var array
     */
    protected $_validateXmlAcl = array('EDIT' => false, 'ADD' => false, 'DELETE' => false, 'LIST' => false);
    
    /**
     * Se inicializa y carga adaptador declarado
     * 
     * @param string $adapter - indice hash zwei.monet-multidb.{$adapter} de archivo configuración de arranque, usualmente (db.ini o application.ini).
     */
    public function __construct()
    {
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
        $options = Zwei_Controller_Config::getOptions();
        
        if (isset($options->zwei->monetdb) && isset($options->zwei->monetdb->driverFile)) {
            require_once $options->zwei->monetdb->driverFile;
        } else {
            /**
             * Ruta de instalación por defecto de adaptador MonetDB en Debian y Ubuntu.
             * @link https://www.monetdb.org/downloads/deb/
             */
            require_once '/usr/share/php/monetdb/php_monetdb.php';
        }
        
        
        $params = array();
        if ($this->_adapter === null) {
            if (isset($options->zwei->monetdb)) {
                $params = $options->zwei->monetdb->params->toArray();
            }
        } else {
            $params = $options->zwei->monet_multidb->{$this->_adapter}->toArray();
        }
        
        foreach ($params as $i => $param)
        {
            $this->_params[$i] = $param;
        }
        
        $this->connect();
    }
    
    /**
     * 
     * @return Zwei_Db_TableMonet_Adapter
     */
    public function getAdapter()
    {
        return new Zwei_Db_TableMonet_Adapter();
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
        ) or trigger_error(monetdb_last_error(), E_USER_ERROR);
    }
    
    
    public function select()
    {
        /**
         * @fixme estamos iniciando el generador de querys con adaptador SQL por defecto, usualmente MySQL, 
         * aunque la query es parchada en Zwei_Db_TableMonet_Select::__toString() para compatibilizarla con Monet DB, no deja de ser un parche.
         * 
         * @todo esto debiera inicializarse con new Zwei_Db_TableMonet_Select($this->getAdapter()), $this->getAdapter debiera retornar un Zwei_Db_TableMonet_Adapter.
         */
        $select = new Zwei_Db_TableMonet_Select(Zend_Db_Table::getDefaultAdapter());
        return $select->from($this->_name);
    }
    
    /**
     * @param $select Zwei_Db_Table_Monet
     * @see Zwei_Admin_ModelInterface::fetchAll()
     */
    public function fetchAll($select = null)
    {
        $data = array();
        
        if (!$select) {
            $select = $this->select()->__toString();
        } else if ($select instanceof Zend_Db_Select) {
            $select = $select->__toString();
        }
        $result = monetdb_query($this->_connection, $select)
            or trigger_error(monetdb_last_error(), E_USER_ERROR);
        
        $this->_count = monetdb_num_rows($result);
        while ($row =  monetdb_fetch_object($result)) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    public function count()
    {
        return $this->_count;
    }
    
    /**
     * Retorna atributo resources.multidb.{$_adapter}.
     * Lleva prefijo Zw para distinguirlo de método nativo Zend_Db_Table_Abstract::getAdapter()
     *
     *
     * @return string
     */
    public function getZwAdapter()
    {
        return $this->_adapter;
    }
    
    
    public function fetchRow($select = null)
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