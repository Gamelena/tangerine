<?php
/**
 * Extiende la funcionalidad de Zend_Db_Table con funcionalidades comunes para Zwei Admin
 *
 * @package Zwei_Db
 * @version $Id:$
 * @since 0.1
 */
require_once ('Zend/Db/Table/Abstract.php');
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
	 * array $data de Zend_Db_Table_Select sobrecargado en $this->overloadData($data). 
	 * @var array|false 
	 */
	protected $_overloaded_data = false;
		
	 /**
     * array $data de Zend_Db_Table_Select sobrecargado en $this->overloadDataTabs($data) 
     * @var array|false
     */
    protected $_overloaded_data_tabs = false;
	
	/**
	 * parámetros de búsqueda llave:valor enviados por request
	 * @var array
	 */
    protected $_query_params;
    /**
     * Se asocia un título al recordset modelo, el cual puede ir en metadata de archivo json. 
     * @var string
     */
    protected $_title = false;
    
	/**
     * Post Constructor.
     * Inicializa atributos de usuario en sesion, permisos y adaptador de DB.
	 */
	public function init()
	{
		if (Zend_Auth::getInstance()->hasIdentity()) {
			$this->_user_info = Zend_Auth::getInstance()->getStorage()->read();
			$this->_acl = new Zwei_Admin_Acl($this->_user_info->user_name);
		} 
		
	    if (!empty($this->_adapter)) { 	
	        $this->setAdapter($this->_adapter);  
	    }
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
	    $config = new Zend_Config_Ini(ROOT_DIR.'/application/configs/application.ini', APPLICATION_ENV);
	    if (isset($config->resources->multidb->{$adapter}->params)) {
            /**
             * [TODO]
             * @deprecated bloque backward compatibility, incluyendo $config
             * 
             */
            $db = Zend_Db::factory($config->resources->multidb->{$adapter});
            $this->_setAdapter($db);
	    } else {    
            $resource = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getResource("multidb");
            $db = $resource->getDb($adapter);
	    }
	}

    /**
     * Inserts a new row.
     *
     * @param  array  $data  Column-value pairs.
     * @return mixed         The primary key of the row inserted.
     */	
	public function insert($data)
	{
        if (class_exists("SettingsModel")) {
            $userName = (isset($this->_user_info->user_name)) ? $this->_user_info->user_name : "NN"; 
            $ip = $_SERVER['REMOTE_ADDR'];

            $strData = print_r($data, true);
            $logMessage = "[$userName $ip] INSERT INTO " . $this->info(Zend_Db_Table::NAME) . " VALUES ($strData) ";
            Debug::writeBySettings($logMessage, 'transactions_log', 'SI', "../log/transactions");
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
	public function update($data, $where)
	{
	    if (class_exists("SettingsModel")) {
	        $userName = (isset($this->_user_info->user_name)) ? $this->_user_info->user_name : "NN"; 
            $ip = $_SERVER['REMOTE_ADDR'];
	        
            $strData = print_r($data, true);
            $strWhere = print_r($where, true);
            $logMessage = "[$userName $ip] UPDATE " . $this->info(Zend_Db_Table::NAME) . " SET (".$strData.") WHERE (".$strWhere.") ";
            Debug::writeBySettings($logMessage, 'transactions_log', 'SI', "../log/transactions");
        }
	    
	    return parent::update($data, $where);
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
            Debug::writeBySettings($logMessage, 'transactions_log', 'SI', "../log/transactions");
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
	 * Setear el status de
	 * @param $result
	 * @param $description
	 */
	protected function setMessage($message)
	{
		$this->_message=$message;
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
	 * Agregar string javascript para añadir validación adicional para editar o eliminar
	 * sobrescribiendo metodo en Modelo a usar
	 * usar en javascript var global_opc = ('edit' || 'add') para discriminar entre editar y agregar
	 * en caso de no pasar validacion agregar un return false en la condición javascript.
	 * @return string
	 */
	public function getEditValidation()
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
	 * Flag para especificar que ignore filtros en ObjectsController
	 * y aplique filtros en modelo
	 * @return boolean
	 */
	public function isFiltered()
	{
		return $this->_is_filtered;
	}

	/**
	 * Convierte un string "Zend_Db_Table where" a un array asociativo campo=>valor
	 * @param $string
	 * @return array()
	 * @deprecated Use $this->where2Array($string) instead
	 */

	public function whereToArray($string)
	{
		$array=explode('=', $string);
		foreach ($array as $i => $v){
		    $i = str_replace("'", "", $i);
		    $v = str_replace("'", "", $v);
			$array[$i] = trim($v);
		}
		return $array;
	}
	
	
    /**
     * Convierte un "Zend_Db_Table where" a un array asociativo campo=>valor
     * @param $string | array
     * @return array
     */

    public function where2Array($where)
    {
        $where = (array) $where;
        $return = array();
        
        foreach ($where as $string) {
            $array = explode('=', $string);
            if (stristr($array[0], ".")) {
                $aux = explode(".", $array[0]);
                $array[0] = str_replace("`", "", $aux[1]);
            }
            
            $return[trim($array[0])] = str_replace("'", "",$array[1]);
        }     
        return $return;
    }
	
	
	public function getPrimary() {
		return (isset($this->_primary)) ? $this->_primary : false;
	} 
	
	/**
	 * Extiende arreglo de datos nativo de $self::select() para el despliegue en listado. 
	 * @param Zend_Db_Rowset retorno de $self::select()
	 * @return array
	 */
	public function overloadData($data) 
	{
		return $this->_overloaded_data;
	}
	
    /**
     * Extiende arreglo de datos nativo de $self::select() para el despliegue en pestañas. 
     * @param Zend_Db_Rowset retorno de $self::select()
     * @return array
     */	
	public function overloadDataTabs($data)
	{
        return $this->_overloaded_data_tabs;		
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
	 * Inicializa $this->_query_params con un array asociativo de parámetros de busqueda
	 * @param Zwei_Utils_Form | false
	 * @return array
	 */
    public function initQueryParams($form = false)
    {
        if (!$form) { $form = new Zwei_Utils_Form(); }
        if (isset($form->search) && (!empty($form->search) || $form->search === '0')) {
            $searchFields = explode(";", $form->search_fields);
            $search = explode(';', $form->search);
            $betweened = false;
            
            if (isset($form->between) && (!empty($form->between) || $form->between === '0')){
		$between = explode(';', $form->between);
	    } else {
		$between = array();
	    }
            $i = 0;    
            foreach ($search as $v) {
                if (!empty($searchFields[$i])) {
                    if (!in_array($searchFields[$i], $between)) {
                        if ($betweened) {
                           if ($search[$i+1] != '') $this->_query_params[$searchFields[$i]] = $search[$i+1];
                        } else {
                           if ($search[$i] != '')  $this->_query_params[$searchFields[$i]] = $search[$i];
                        }    
                    } else {
                        $betweened = true;
                        $this->_query_params[$searchFields[$i]] = array();
                        if ($search[$i] != '') $this->_query_params[$searchFields[$i]][] = $search[$i];
                        if ($search[$i+1] != '') $this->_query_params[$searchFields[$i]][] = $search[$i+1];                            
                    }
                    $i++;
                }    
            }
        }
        return $this->_query_params;
    }
}
