<?php
/**
 * Extiende la funcionalidad de Zend_Db_Table con funcionalidades comunes para Zwei Admin
 *
 * @package Zwei_Db
 * @version $Id:$
 * @since 0.1
 */
class Zwei_Db_Table extends Zend_Db_Table
{
	protected $_label;
	protected $_search_fields = false;
	protected $_acl;
	protected $_user_info;
	protected $_message;
	protected $_is_filtered = false;

	public function init()
	{
		if (Zend_Auth::getInstance()->hasIdentity()) {
			$this->_user_info = Zend_Auth::getInstance()->getStorage()->read();
			$this->_acl = new Zwei_Admin_Acl($this->_user_info->user_name);
		} else {
			//$this->_redirect('index/login');
		}
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
	public function getName(){
		return $this->_name;
	}

	/**
	 * Retorna el label del datastore dojo
	 * @return string
	 */

	public function getLabel(){
		return $this->_label;
	}

	/**
	 * Agregar string javascript para añadir validación adicional para la busqueda,
	 * sobrescribiendo metodo en Modelo a usar
	 * @return string
	 */
	public function getSearchValidation(){
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

	protected function whereToArray($string){
		$array=explode('=', $string);
		foreach ($array as $i=>$v){
			$array[$i]=trim($v);
		}
		return $array;
	}
}