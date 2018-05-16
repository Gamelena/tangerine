<?php

/**
 * Adaptador de tabla MonetDB
 */
abstract class Gamelena_Db_TableMonet implements Gamelena_Admin_ModelInterface
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
     * @var string
     */
    protected $_schema = null;
    
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
     * parámetros enviados por $_REQUEST
     * @var Gamelena_Utils_Form
     */
    protected $_form;
    
    /**
     * @var string
     */
    protected $_adapter;
    
    /**
     * 
     * @var monetdb_connect resource
     */
    protected $_connection;
    
    const SELECT_WITH_FROM_PART    = true;
    const SELECT_WITHOUT_FROM_PART = false;
    
    /**
     * 
     * @var int
     */
    protected $_count = 0;
    
    
    protected $_is_filtered;
    
    /**
     * Hash que indica si deben ser validados los permisos modulo-usuario-accion en api rest.
     *
     * @var array
     */
    protected $_validateXmlAcl = array('EDIT' => false, 'ADD' => false, 'DELETE' => false, 'LIST' => false);
    
    /**
     * Se inicializa y carga adaptador declarado
     * 
     * @param string $adapter - indice hash gamelena.monet-multidb.{$adapter} de archivo configuración de arranque, usualmente (db.ini o application.ini).
     */
    public function __construct()
    {
        $this->init();
    }
    
    
    /**
     * Flag para especificar que ignore filtros en Gamelena_Db_Object
     * y aplique filtros en modelo
     * @return boolean
     */
    public function isFiltered()
    {
        return $this->_is_filtered;
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
        $options = Gamelena_Controller_Config::getOptions();
        
        if (isset($options->gamelena->monetdb) && isset($options->gamelena->monetdb->driverFile)) {
            require_once $options->gamelena->monetdb->driverFile;
        } else {
            /**
             * Ruta de instalación por defecto de adaptador MonetDB en Debian y Ubuntu.
             * @link https://www.monetdb.org/downloads/deb/
             */
            require_once '/usr/share/php/monetdb/php_monetdb.php';
        }
        
        
        $params = array();
        if ($this->_adapter === null) {
            if (isset($options->gamelena->monetdb)) {
                $params = $options->gamelena->monetdb->params->toArray();
            }
        } else {
            $params = $options->gamelena->monet_multidb->{$this->_adapter}->toArray();
        }
        
        foreach ($params as $i => $param)
        {
            $this->_params[$i] = $param;
        }
        $this->_form = new Gamelena_Utils_Form();
        $this->connect();
    }
    
    /**
     * 
     * @return Gamelena_Db_TableMonet_Adapter
     */
    public function getAdapter()
    {
        return new Gamelena_Db_TableMonet_Adapter();
    }
    
    
    /**
     * Configura el adaptador de base de datos segun valores de atributo de configuración.
     * resources.multidb.{$adapter}.*
     *
     * en archivo /application/configs/application.ini
     *
     * @param  $adapter
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
    
    
    public function select($withFromPart = self::SELECT_WITH_FROM_PART)
    {
        /**
         * @fixme estamos iniciando el generador de querys con adaptador SQL por defecto, usualmente MySQL, 
         * aunque la query es parchada en Gamelena_Db_TableMonet_Select::__toString() para compatibilizarla con Monet DB, no deja de ser un parche.
         * 
         * @todo esto debiera inicializarse con new Gamelena_Db_TableMonet_Select($this->getAdapter()), $this->getAdapter debiera retornar un Gamelena_Db_TableMonet_Adapter.
         */
        
        $select = new Gamelena_Db_TableMonet_Select(Zend_Db_Table::getDefaultAdapter());
        
        if ($withFromPart === self::SELECT_WITH_FROM_PART) {
            $select->from($this->_name, "*", $this->_schema);
        }
        
        return $select;
    }
    
    /**
     * Query a raw SQL string.
     * 
     * @param  string $query
     * @return unknown
     */
    public function query($query)
    {
        $result = monetdb_query($this->_connection, monetdb_escape_string($query)) or trigger_error(monetdb_last_error());
        return $result;
    }
    
    
    /**
     * @param $select Gamelena_Db_Table_Monet
     * @see Gamelena_Admin_ModelInterface::fetchAll()
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
     * @return string
     */
    public function getZwAdapter()
    {
        return $this->_adapter;
    }
    
    
    public function fetchRow($select = null)
    {
        
    }
    
    public function info($key)
    {
        if ($key === 'primary') {
            return $this->_primary;
        }
    }
}