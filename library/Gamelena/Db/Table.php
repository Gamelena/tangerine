<?php
/**
 * Extiende la funcionalidad de Zend_Db_Table con funcionalidades comunes para Gamelena Admin
 *
 * @package Gamelena_Db
 * @version $Id:$
 * @since   0.1
 */
class Gamelena_Db_Table extends Zend_Db_Table_Abstract
{
    /**
     * Campo etiqueta o título, util cuando se usa el modelo para graficar en 2 dimensiones
     * ya que especifica cual es el índice de las etiquetas del eje X.
     * 
     * (*) Es obligatorio inicializar $this->_label o $this->_labels para graficar en 2D.
     * 
     * @var string | false
     */
    protected $_label = false;
    /**
     * Array de etiquetas o títulos, util cuando se usa el modelo para graficar en 2 dimensiones.
     * ya que especifica cuales son las etiquetas del eje X.
     * 
     * (*) Es obligatorio inicializar $this->_label o $this->_labels para graficar en 2D.
     * 
     * @var array | false
     */
    protected $_labels = false;
    /**
     * Tipo de bitacora.
     * @var boolean | array
     */
    static protected $_defaultLogMode = false;
    
    /**
     * Campos sobre los cuales se puede realizar la búsqueda por defecto, esto tambien se puede hacer vía XML.
     *
     * @deprecated usar XML en su lugar.
     * 
     * @var array
     */
    protected $_search_fields = false;
    /**
     * Permisos de usuario en sesión.
     * @var Gamelena_Admin_Acl
     */
    protected $_acl;
    /**
     * Datos de usuario en sesión.
     * @var Zend_Auth_Storage_Interface
     */
    protected $_user_info;
    /**
     * Mensaje a desplegar en Gamelena_Admin_Components_Helpers_EditTableDojo.
     * @var string
     */
    protected $_message;
    /**
     * Devuelve respuesta para ejecutar javascript segun valor en Gamelena_Admin_Components_Helpers_EditTableDojo().
     * @var string
     */
    protected $_ajax_todo;
    /**
     * indica si tendra filtros personalizados, ignorando los filtros de Gamelena_Db_Object para metodo select.
     * @var boolean
     */
    protected $_is_filtered = false;
    /**
    * Adaptador de Base de datos. 
    * Debe estar declarado en .ini o xml como resources.multidb.{$_adapter}.
    * @var string 
    */
    protected $_adapter;
    /**
     * parámetros enviados por $_REQUEST
     * @var Gamelena_Utils_Form
     */
    protected $_form;
    /**
     * Se asocia un título al recordset modelo, el cual puede ir en metadata de archivo json. 
     * @var string
     */
    protected $_title = false;
    
    /**
     * Variable auxiliar para retornar cualquier dato(s) que necesitemos durante la operación de un modelo.
     * Si está inicializada, esta variable es retornada como parte de la respuesta Json en Gamelena_Admin_Components_Helpers_EditTableDojo.
     * @var array|false
     */
    protected $_more = null;
    
    /**
     * Hash que indica si deben ser validados los permisos modulo-usuario-accion.
     *
     * @var array
     */
    protected $_validateXmlAcl = array('EDIT' => false, 'ADD' => false, 'DELETE' => false, 'LIST' => false);
    
    /**
     * Post Constructor.
     * Inicializa atributos de usuario en sesion, permisos y adaptador de DB.
     */
    public function init()
    {
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $this->_user_info = Zend_Auth::getInstance()->getStorage()->read();
        } 
        
        if (!empty($this->_adapter)) {     
            $this->setAdapter($this->_adapter);  
        }
        
        $this->_form = new Gamelena_Utils_Form();
        parent::init();
    }
    
    /**
     * __call() es lanzado al invocar un método inaccesible en un contexto de objeto.
     *
     * @link http://php.net/manual/es/language.oop5.overloading.php
     * 
     * Permite usar self::findBy$field($value) sin tener que escribir los finders manualmente.
     * 
     * Por defecto convierte $field de UpperCamelCase a Underscore (*). Ej: 'IdClasePlataforma' ==> 'id_clase_plataforma'
     * 
     * @param string $function
     * @param string $args[0]  - $value (valor para $field)
     * @param string $args[1]  - (*) si es true, NO hace conversion de UpperCamelCase a undercore.
     */
    public function __call($method, $args)
    {
        $value = $args[0];
        $camelCase = isset($args[1]) ? $args[1] : false;
                
        if (substr($method, 0, 6) == 'findBy') {
            $criteria = substr($method, 6);
            
            if (!$camelCase) { 
                $criteria = strtolower(Gamelena_Utils_String::toVarWord($criteria)); 
            }
 
            $select = $this->select()
                ->from($this->_name)
                ->where($criteria . ' = ?', $value);
            
            return $this->fetchAll($select);
        }
    }
    
    /**
     * Hash que indica si deben ser validados los permisos modulo-usuario-accion.
     * @return array:
     */
    public function getValidateXmlAcl()
    {
        return $this->_validateXmlAcl;
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
    public function setAdapter($adapter) 
    {
        $resource = Gamelena_Controller_Config::getResourceMultiDb();
        $db = $resource->getDb($adapter);
        $this->_setAdapter($db);
    }

    /**
     * Inserts a new row.
     *
     * @param  array $data Column-value pairs.
     * @return mixed         The primary key of the row inserted.
     */    
    public function insert(array $data)
    {
        if (class_exists("SettingsModel")) {
            $userName = (isset($this->_user_info->user_name)) ? $this->_user_info->user_name : "NN"; 
            $ip = $_SERVER['REMOTE_ADDR'];

            $strData = print_r($data, true);
            $logMessage = "[$userName $ip] INSERT INTO " . $this->info(Zend_Db_Table::NAME) . " VALUES ($strData) ";
            Debug::writeBySettings($logMessage, 'transactions_log', '1', "../log/transactions");
        } else {
            Console::log("No existe SettingsModel");
        }
        
        return parent::insert($data);
    }
    
    /**
     * clonate existing row.
     * 
     * @param  array|string                                                                    $where An SQL WHERE clause, or an array of SQL WHERE clauses.
     * @param  array  array columna valor para forzar guardar tales valores en tales columnas.
     * @return int|array|false  last inserted Id(s)
     */
    public function clonate($where, $overdata = null)
    {
        $aWhere = self::whereToArray($where);
        $select = parent::select();
        $clonated = array();
        
        if (!is_array($where)) {
            $select->where($where);
        } else {
            foreach ($where as $w) {
                $select->where($w);
            }
        }
        
        $rowset = $this->fetchAll($select);
        
        $count = $rowset->count();
        
        if (!$count) {
            $clonated = false;
        } else {
            foreach ($rowset as $row) {
                $data = array();
                foreach ($this->info(Zend_Db_Table::COLS) as $col) {
                    if (!in_array($col, array_keys($aWhere))) {
                        $data[$col] = $row->{$col};
                    }
                }
                if ($overdata) { $data = array_merge($data, $overdata); 
                }
                $inserted = $this->insert($data);
                if ($count == 1) {
                    $clonated = $inserted;  
                } else {
                    $clonated[] = $inserted;
                }
            }
        }
        return $clonated;
    }
    
    /**
     * Updates existing rows.
     *
     * @param  array        $data  Column-value pairs.
     * @param  array|string $where An SQL WHERE clause, or an array of SQL WHERE clauses.
     * @return int          The number of rows updated.
     */
    public function update(array $data, $where)
    {
        $rowOrig = false;
        $logMessage = '';
        $differences = '{Sin Cambios}';
        $select = parent::select();
        
        try {
            if (!is_array($where)) {
                $select->where($where);
            } else {
                foreach ($where as $w) {
                    $select->where($w);
                }
            }
            Debug::writeBySettings($select->__toString(), 'query_log');
            $rowOrig = $this->fetchRow($select);
        } catch (Zend_Db_Exception $e) {
            $differences = '{Ocurrió un error al obtener los datos originales.}';
            Console::log($e->getCode() . " " . $e->getMessage());
        }
        
        $update = parent::update($data, $where);
        
        if ($update && class_exists("SettingsModel")) {
            if ($rowOrig) {
                try {
                    $select = new Zend_Db_Table_Select($this);
                    $rowNew = $this->fetchRow($select);
                    if ($rowNew) {
                        $differences = Gamelena_Utils_Array::getDifferences($rowOrig->toArray(), $rowNew->toArray());
                        $differences = print_r($differences, true);
                    } else {
                        Console::log('FIXME, modificaron mi identidad y perdí el seguimiento');
                    }
                } catch (Zend_Db_Exception $e) {
                    Console::log($e->getCode() . " " . $e->getMessage());
                }
            }
            $userName = (isset($this->_user_info->user_name)) ? $this->_user_info->user_name : "NN"; 
            $ip = $_SERVER['REMOTE_ADDR'];
            
            $strData = print_r($data, true);
            $strWhere = print_r($where, true);
            
            $logMessage = "[$userName $ip] UPDATE " . $this->info(Zend_Db_Table::NAME) . " SET (".$strData.") WHERE (".$strWhere.")";
            Debug::writeBySettings($logMessage . "\nValores Originales antes de Modificar : " . $differences, 'transactions_log', '1', "../log/transactions");
        }
        
        if ($update === 0) {
            $this->setMessage("Sin Cambios");
        }
        return $update;
    }

    /**
     * Deletes existing rows.
     *
     * @param  array|string $where SQL WHERE clause(s).
     * @return int          The number of rows deleted.
     */
    public function delete($where)
    {
        if (class_exists("SettingsModel")) {
            $userName = (isset($this->_user_info->user_name)) ? $this->_user_info->user_name : "NN"; 
            $ip = $_SERVER['REMOTE_ADDR'];
            
            $strWhere = print_r($where, true);
            $logMessage = "[$userName $ip] DELETE FROM " . $this->info(Zend_Db_Table::NAME) . " WHERE ($strWhere) ";
            //Debug::writeBySettings($this->getAdapter()->getConfig(), 'transactions_log', '1', "../log/transactions");
            Debug::writeBySettings($logMessage, 'transactions_log', '1', "../log/transactions");
        } else {
            Console::log('No existe SettingsModel');
        }

        return parent::delete($where);
    }

    /**
     * Se setea attributo $this->_label el cual puede ser usado (por ejemplo) para almacenes de datos js como dojo.data.ItemFileReadStore
     * 
     * @param  string $value
     * @return void
     */
    public function setLabel($value)
    {
        $this->_label = $value;
    }

    /**
     * Retorna los campos según los cuales se realiza la búsqueda por defecto 
     * @return array()
     */
    public function getSearchFields()
    {
        return $this->_search_fields;
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


    /**
     * Devuelve la descripción de la respuesta de un modelo sobre el cual
     * se ha realizado una operación CRUD, por ejemplo un mensaje personalizado de error 
     * @return array('result'=>$valor,'description'=>$valor)
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * Setear un mensaje de retorno
     * @param string $message
     */
    protected function setMessage($message)
    {
        $this->_message = $message;
    }


    /**
     * Retorna el nombre de la tabla principal
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Retorna el índice del label del datastore dojo
     * @return string
     */

    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * Retorna el array de labels que etiquetan al recorset (ejemplo típico, un intervalo de fechas).
     * @return array
     */

    public function getLabels()
    {
        return $this->_labels;
    }
    /**
     * Retorna un título para el recordset de ser requerido.
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }
    
    
    /**
     * Agregar string javascript para añadir validación adicional para la busqueda,
     * sobrescribiendo metodo en Modelo a usar
     * @return string
     */
    public function getSearchValidation()
    {
        return '';
    }
    
    /**
     * 
     * @return string
     */
    public function getAjaxTodo()
    {
        return $this->_ajax_todo;
    }
    
    /**
     * Cuentame más: retorno de información auxiliar que puede ser util despues de ejecutar una operación crud.
     * @return array
     */
    public function getMore()
    {
        return $this->_more;
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
     * Activar o desactivar funcionalidad de usar parametros de búsqueda en XML de forma automática
     * @param boolean $value
     * @return void
     */
    public function disableAutoSearch($value = true)
    {
        $this->_is_filtered = $value;
    }
    
    /**
     * Convierte un string sql where "$campo=$valor" a un array($campo=>$valor)
     * @param $string
     * @return array
     */

    static public function whereToArray($where)
    {
        $where = (array) $where;
        $return = array();
        
        foreach ($where as $string) {
            if (stristr($string, '=')) {
                $array = explode('=', $string);
                if (stristr($array[0], ".")) {
                    $aux = explode(".", $array[0]);
                    $array[0] = str_replace("`", "", $aux[1]);
                }
                
                $return[trim(str_replace("`", "", $array[0]))] = trim(str_replace("'", "", $array[1]));
            }
        }
        return $return;
    }
    
    /**
     * Valida los permisos de usuario en sesion en modelo $form->model para ejecutar $form->action 
     * según permisos sobre archivo xml $form->p.
     * 
     * @param  Gamelena_Utils_Form<model, p, action> $form
     * @param  Gamelena_Admin_Xml                    $xml
     * @return boolean
     */
    public function validateXmlAcl($form, $xml = null)
    {
        $action = isset($form->action) ? strtoupper($form->action) : 'LIST';
        $this->_acl = new Gamelena_Admin_Acl();
        $validatedList = true;
        $component = $form->p;
        
        if (!$xml) {
            //Si esto es llamado desde CrudRequestController SIEMPRE existe $xml y nunca entra acá.
            if (isset($form->p)) {
                $file = Gamelena_Admin_Xml::getFullPath($form->p);
                $xml  = new Gamelena_Admin_Xml($file, 0, 1);
            } else {
                return false;
            }
        }
        
        if ($xml->getAttribute('aclComponent')) {
            //Si existe tag aclComponent en XML, entonces se validan permisos en función de archivo declarado en aclComponent.
            $component = $xml->getAttribute('aclComponent');
        }
        
        if ($xml->getAttribute('target') !== $form->model) {
            $validatedList = false;
            Console::error("{$form->model} no existe en $component");
        } else if (!$this->_acl->isUserAllowed($component, $action)) {
            $validatedList = false;
        }
        return $validatedList;
    }
    
    
    /**
     * get primary key
     * @deprecated use $this->info(Zend_Db_Table::PRIMARY) instead
     */
    public function getPrimary()
    {
        return (isset($this->_primary)) ? $this->_primary : false;
    } 
    
    /**
     * Extiende arreglo de datos nativo de $self::select() para el despliegue en listado. 
     * @param array - retorno de $self::select()
     * @return Zend_Db_Rowset|array|false
     */
    public function overloadDataList($data) 
    {
        return false;
    }
    
    /**
     * Extiende arreglo de datos nativo de $self::select() para el despliegue en formulario cargado via ajax. 
     * @param Zend_Db_Rowset retorno de $self::select()
     * @return Zend_Db_Rowset|array
     */    
    public function overloadDataForm($data)
    {
        return $data;
    }
    
    /**
     * Indica si se guardarán transacciones en log, Modelo debe extender de Gamelena_Table_Loggeable()
     * @param boolean
     */
    static public function setDefaultLogMode($mode)
    {
        self::$_defaultLogMode = $mode;
    }
    
    /**
     * Método para separar datos de tabla principal de datos de tablas auxiliares, para ser reescrito en modelos respectivos.
     * 
     * @param  Zend_Db_Table_Rowset $data
     * @return array
     */
    protected function cleanDataParams($data)
    {
        return $data;
    }
    
    /**
     * Método para preguntar si determinado usuario es el owner de un elemento
     * 
     * @param  unknown $user
     * @return boolean
     */
    public function isOwner($itemId, $user = null) 
    {
        return false;
    } 
    
    /**
     * Método para configurar manualmente el numero de elementos a ser paginados
     * @return boolean
     */
    public function count()
    {
        return false;
    }
    
    /**
     * Guarda acciones en log
     *
     * @param string       $action
     * @param string|array $condition
     */
    public function log($action, $condition, $table = null) 
    {
        if (is_array($condition)) { $condition = print_r($condition, true); 
        }
        if (self::$_defaultLogMode) {
            try {
                $logBook = new DbTable_LogBook();
                $logData = array (
                        "user" => $this->_user_info->user_name,
                        "acl_roles_id" => $this->_user_info->acl_roles_id,
                        "table" => ($table ? $table : $this->_name),
                        "action" => $action,
                        "ip" => $_SERVER['REMOTE_ADDR'],
                        "stamp" => date("Y-m-d H:i:s")
                );
                if ($condition) { $logData["condition"] = $condition; 
                }
                $logBook->insert($logData);
            } catch (Zend_Acl_Role_Registry_Exception $e) {
                //Si se elimina un perfil con permisos asociados se genera esta Exception 
                //ya que aun no son borrados los permisos asociados, generando una inconsistencia referencial, 
                //estos permisos se borrarán justo despues de este paso, una forma de evitar esta excepción es borrando en cascada por ORM o DB.
            }
        }
    }
}
