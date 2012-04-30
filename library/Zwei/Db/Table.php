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
	 * Campo etiqueta o título
	 * @var string
	 */
	protected $_label;
	
	/**
	 * Tipo de bitacora
	 * @var boolean|array
	 */
	static protected $_defaultLogMode = false;
	
	protected $_search_fields = false;
	/**
	 * 
	 * @var Zwei_Admin_Acl
	 */
	protected $_acl;
	/**
	 * @var Zend_Auth
	 */
	protected $_user_info;
	/**
	 * Mensaje a desplegar en Zwei_Admin_Components_Helpers_EditTableDojo
	 * @var string
	 */
	protected $_message;
	/**
	 * Devuelve respuesta para ejecutar javascript segun valor en Zwei_Admin_Components_Helpers_EditTableDojo()
	 * @var string
	 */
	protected $_ajax_todo;
	/**
	 * indica si tendra filtros personalizados, ignorando los filtros de Zwei_Db_Object para metodo select
	 * @var boolean
	 */
	protected $_is_filtered = false;
	/**
	* Adaptador de Base de datos. 
	* Debe estar declarado en .ini o xml como resources.multidb.{$_adapter}
	* @var string 
	*/
	protected $_adapter;
	
	/**
	 * array $data de Zend_Db_Table_Select sobrecargado en $this->overloadData($data) 
	 * @var Zend_Db_Table_Rowset
	 */
	protected $_overloaded_data = false;
		
	
	public function init()
	{
		if (Zend_Auth::getInstance()->hasIdentity()) {
			$this->_user_info = Zend_Auth::getInstance()->getStorage()->read();
			$this->_acl = new Zwei_Admin_Acl($this->_user_info->user_name);
		} 
		
	    if (!empty($this->_adapter)) { 	
	        $config = new Zend_Config_Ini(ROOT_DIR.'/application/configs/application.ini', APPLICATION_ENV);
	        $db = Zend_Db::factory($config->resources->multidb->{$this->_adapter});
	        $this->setDefaultAdapter($db);
	    }
	}
	
	public function setAdapter($adapter) 
	{
        $config = new Zend_Config_Ini(ROOT_DIR.'/application/configs/application.ini', APPLICATION_ENV);
        $db = Zend_Db::factory($config->resources->multidb->{$adapter});
        $this->setDefaultAdapter($db);		
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
	 * Retorna el label del datastore dojo
	 * @return string
	 */

	public function getLabel()
	{
		return $this->_label;
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
	 */

	public function whereToArray($string)
	{
		$array=explode('=', $string);
		foreach ($array as $i=>$v){
			$array[$i]=trim($v);
		}
		return $array;
	}
	
	public function getPrimary() {
		return (isset($this->_primary)) ? $this->_primary : false;
	} 
	
	public function overloadData($data) 
	{
		return $this->_overloaded_data;
	}
	
	public function setDefaultLogMode($mode)
	{
	    self::$_defaultLogMode = $mode; 	
	}
}