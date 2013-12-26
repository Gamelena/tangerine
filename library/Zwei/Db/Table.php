<?php
/**
 * Extiende la funcionalidad de Zend_Db_Table con funcionalidades comunes para Zwei Admin
 *
 * @package Zwei_Db
 * @version $Id:$
 * @since 0.1
 */
class Zwei_Db_Table extends Zend_Db_Table_Abstract
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
     * Campos sobre los cuales se puede realizar la búsqueda por defecto, esto tambien se puede hacer vía XML
     * @var array
     */
    protected $_search_fields = false;
    /**
     * Permisos de usuario en sesión.
     * @var Zwei_Admin_Acl
     */
    protected $_acl;
    /**
     * Datos de usuario en sesión.
     * @var Zend_Auth_Storage_Interface
     */
    protected $_user_info;
    /**
     * Mensaje a desplegar en Zwei_Admin_Components_Helpers_EditTableDojo.
     * @var string
     */
    protected $_message;
    /**
     * Devuelve respuesta para ejecutar javascript segun valor en Zwei_Admin_Components_Helpers_EditTableDojo().
     * @var string
     */
    protected $_ajax_todo;
    /**
     * indica si tendra filtros personalizados, ignorando los filtros de Zwei_Db_Object para metodo select.
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
     * @var Zwei_Utils_Form
     */
    protected $_form;
    /**
     * Se asocia un título al recordset modelo, el cual puede ir en metadata de archivo json. 
     * @var string
     */
    protected $_title = false;
    
    /**
     * Variable auxiliar para retornar cualquier dato(s) que necesitemos durante la operación de un modelo.
     * Si está inicializada, esta variable es retornada como parte de la respuesta Json en Zwei_Admin_Components_Helpers_EditTableDojo.
     * @var array|false
     */
    protected $_more = null;
    
    
    
    /**
     * Post Constructor.
     * Inicializa atributos de usuario en sesion, permisos y adaptador de DB.
     */
    public function init()
    {
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $this->_user_info = Zend_Auth::getInstance()->getStorage()->read();
            if (isset($this->_user_info->user_name)) {
                $this->_acl = new Zwei_Admin_Acl($this->_user_info->user_name);
            }
        } 
        
        if (!empty($this->_adapter)) {     
            $this->setAdapter($this->_adapter);  
        }
        
        $this->_form = new Zwei_Utils_Form();
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
    public function setAdapter($adapter) 
    {
        $resource = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getResource("multidb");
        $db = $resource->getDb($adapter);
        $this->_setAdapter($db);
    }

    /**
     * Inserts a new row.
     *
     * @param  array  $data  Column-value pairs.
     * @return mixed         The primary key of the row inserted.
     */    
    public function insert(array $data)
    {
        if (class_exists("SettingsModel")) {
            $userName = (isset($this->_user_info->user_name)) ? $this->_user_info->user_name : "NN"; 
            $ip = $_SERVER['REMOTE_ADDR'];

            $strData = print_r($data, true);
            $logMessage = "[$userName $ip] INSERT INTO " . $this->info(Zend_Db_Table::NAME) . " VALUES ($strData) ";
            //Debug::writeBySettings($this->getAdapter()->getConfig(), 'transactions_log', '1', "../log/transactions");
            Debug::writeBySettings($logMessage, 'transactions_log', '1', "../log/transactions");
        }
        
        return parent::insert($data);
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
        $select = new Zend_Db_Table_Select($this);
        
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
            Debug::write($e->getCode() . " " . $e->getMessage());
        }
        
        $update = parent::update($data, $where);
        
        if ($update && class_exists("SettingsModel")) {
            if ($rowOrig) {
                try {
                    $select = new Zend_Db_Table_Select($this);
                    $rowNew = $this->fetchRow($select);
                    if ($rowNew) {
                        $differences = Zwei_Utils_Array::getDifferences($rowOrig->toArray(), $rowNew->toArray());
                        $differences = print_r($differences, true);
                    } else {
                        Debug::write('FIXME, modificaron mi identidad y perdí el seguimiento');
                    }
                } catch (Zend_Db_Exception $e) {
                    Debug::write($e->getCode() . " " . $e->getMessage());
                }
            }
            $userName = (isset($this->_user_info->user_name)) ? $this->_user_info->user_name : "NN"; 
            $ip = $_SERVER['REMOTE_ADDR'];
            
            $strData = print_r($data, true);
            $strWhere = print_r($where, true);
            
            $logMessage = "[$userName $ip] UPDATE " . $this->info(Zend_Db_Table::NAME) . " SET (".$strData.") WHERE (".$strWhere.")";
            Debug::writeBySettings($logMessage . "\nValores Originales antes de Modificar : " . $differences, 'transactions_log', '1', "../log/transactions");
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
        }

        return parent::delete($where);
    }

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
     * 
     * @return multitype:
     */
    public function getMore()
    {
        return $this->_more;
    }
    
    

    /**
     * Flag para especificar que ignore filtros en Zwei_Db_Object
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
     */
    public function disableAutoSearch($value = true)
    {
        $this->_is_filtered = $value;
    }
    
    /**
     * Convierte un string "string sql where" a un array asociativo campo=>valor
     * @param $string
     * @return array()
     */

    public function whereToArray($where)
    {
        $where = (array) $where;
        $return = array();
        
        foreach ($where as $string) {
            $array = explode('=', $string);
            if (stristr($array[0], ".")) {
                $aux = explode(".", $array[0]);
                $array[0] = str_replace("`", "", $aux[1]);
            }
            
            $return[trim(str_replace("`", "", $array[0]))] = trim(str_replace("'", "",$array[1]));
        }
        return $return;
    }
    
    
    public function getPrimary() {
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
     * Indica si se guardarán transacciones en log, Modelo debe extender de Zwei_Table_Loggeable()
     * @param boolean
     */
    public function setDefaultLogMode($mode)
    {
        self::$_defaultLogMode = $mode;
    }
    
    /**
     * Método para separar datos de tabla principal de datos de tablas auxiliares, para ser reescrito en modelos respectivos.
     * 
     * @param Zend_Db_Table_Rowset $data
     * @return array
     */
    protected function cleanDataParams($data)
    {
        return $data;
    }
    
    /**
     * Método para preguntar si determinado usuario es el owner de un elemento
     * 
     * @param unknown $user
     * @return boolean
     */
    public function isOwner($itemId, $user=null) 
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
}
