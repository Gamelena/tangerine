<?php
/**
 * Controlador de Datos
 *
 * Controlador principal que interactúa con capa de datos para operaciones CRUD, 
 * recibe parametros por $_REQUEST y devuelve datos y/o mensajes en formatos como json u xls
 *
 * @package Controllers
 * @version $Id:$
 * @since 0.1
 */

class ObjectsController extends Zend_Controller_Action
{
	/**
	 * Objeto que lista permisos del usuario
	 * @var Zwei_Admin_Acl
	 */
	private $_acl;
	/**
	 * Instancia Singleton
	 * @var Zend_Auth
	 */
	private $_user_info;
	/**
	 * Instancia de $_REQUEST, preferible a Zend_Controller_Request ya que este no permite manejar un <pre>$_REQUEST['action']</pre>
	 * @var Zwei_Utils_Form
	 */
	private $_form;
	/**
	 * Arreglo que será parte de la respuesta en json, Dojo data store, u otro formato a definir
	 * @var array()
	 */
	private $_response_content=array();

	public function init()
	{
		if (Zend_Auth::getInstance()->hasIdentity()) {
			$this->_user_info = Zend_Auth::getInstance()->getStorage()->read();
			$this->_acl = new Zwei_Admin_Acl($this->_user_info->user_name);
		} else {
			$this->_redirect('index/login');
		}

		$this->_helper->layout()->disableLayout();
		$this->_form = new Zwei_Utils_Form();

	}

	/**
	 * Retorna un json a partir de un objeto modelo,
	 * enviar nombre de clase modelo separada por "_" y sin sufijo "Model",
	 * ej: enviar "model=solicitud_th" en lugar de "model=SolicitudThModel"
	 * @return excel|json
	 */

	public function indexAction()
	{

		/**
		 * [TODO] Validar segun perfil de usuario autorizado a obtener estos datos
		 * [TODO] En el caso de datos personales solo se debieran poder ver y editar los datos de UN reg.
		 */

		if (@$this->_form->format == 'excel') {
			header('Content-type: application/vnd.ms-excel');
			header("Content-Disposition: attachment; filename={$this->_form->model}.xls");
			header("Pragma: no-cache");
			header("Expires: 0");

		} else {
			$this->_helper->ContextSwitch
			->setAutoJsonSerialization(false)
			->addActionContext('index', 'json')
			->initContext();
		}

		//enviar nombre de clase modelo separada por "_" y sin sufijo "Model",
		//ej: enviar solicitud_th en lugar de SolicitudThModel"

		$ClassModel = Zwei_Utils_String::toClassWord($this->_form->model)."Model";

		if (class_exists($ClassModel)) {

			$oModel = new $ClassModel();
			$this->view->collection = array();

			if (isset($this->_form->action) && Zend_Auth::getInstance()->hasIdentity()) {

				$data = array();
				$oModel->getAdapter()->getProfiler()->setEnabled(true);

				if ($this->_form->action == 'add') {
					 
					foreach ($this->_form->data as $i=>$v) {
						$data[$i] = $v;
					}

					try {
						$response = $oModel->insert($data);
						if ($response) {
							$this->_response_content['state'] = 'ADD_OK';
						} else {
							$this->_response_content['state'] = 'ADD_FAIL';
						}
					} catch(Zend_Db_Exception $e) {
						$this->_response_content['state'] = 'ADD_FAIL';
						$query = $oModel->getAdapter()->getProfiler()->getLastQueryProfile()->getQuery();
						$query_params = print_r($oModel->getAdapter()->getProfiler()->getLastQueryProfile()->getQueryParams(),true);
						Zwei_Utils_Debug::write("Zend_Db_Exception:{$e->getMessage()},Code:{$e->getCode()}/Query:$query/Params:$query_params");
					}

				} elseif ($this->_form->action == 'delete') {
					 
					if (!empty($this->_form->id) || $this->_form->id === "0") {
						$where = $oModel->getAdapter()->quoteInto("id = ?", $this->_form->id);
						$response = $oModel->delete($where);
						if ($response) {
							$this->_response_content['state'] = 'DELETE_OK';
						} else {
							$this->_response_content['state'] = 'DELETE_FAIL';
						}
					} else {
						$this->_response_content['state'] = 'DELETE_FAIL';
					}
					//Zwei_Utils_Debug::write($response);
				} else if ($this->_form->action == 'edit') {
					foreach ($this->_form->data as $i=>$v) {
						$data[$i] = $v;
					}
					$where = $oModel->getAdapter()->quoteInto("id = ?", $this->_form->id);
					try {
						$response = $oModel->update($data, $where);
						if ($response) {
							$this->_response_content['state'] = 'UPDATE_OK';
						} else {
							$this->_response_content['state'] = 'UPDATE_FAIL';
						}
					} catch (Zend_Db_Exception $e) {
						$log_data = print_r($data, true);
						$log_where = print_r($where, true);
						$this->_response_content['state'] = 'UPDATE_FAIL';
						Zwei_Utils_Debug::write("Zend_Db_Exception:{$e->getMessage()},Code:{$e->getCode()}|model:$ClassModel|".$e->getTraceAsString());
					}
					//Zwei_Utils_Debug::write($response);
				}
				$this->_response_content['todo'] = $oModel->getAjaxTodo();

				$query = $oModel->getAdapter()->getProfiler()->getLastQueryProfile()->getQuery();
				$query_params = print_r($oModel->getAdapter()->getProfiler()->getLastQueryProfile()->getQueryParams(),true);
				$oModel->getAdapter()->getProfiler()->setEnabled(false);
				// IMPORTANTE, LOG DE TRANSACCIONES: acá van query y usuario a un log de texto en caso de ser query INSERT, DELETE o UPDATE
				Zwei_Utils_Debug::write("[TRANSACTION:{$this->_form->action}]\nUser:{$this->_user_info->user_name}\nQuery:$query\nQuery Params:$query_params",  "../log/transactions");

			}//if (isset($this->_form->action))

			$oDbObject = new Zwei_Db_Object($this->_form);
			$oSelect = $oDbObject->select();

			$data = $oModel->fetchAll($oSelect);
			$i = 0;

			//si ?format=excel exportamos el rowset a html, los headers de excel ya han sido generados
			if (@$this->_form->format == 'excel') {
				$Table = new Zwei_Utils_Table();
				if (isset($this->_form->p)) {
					$content = $Table->rowsetToHtml($data, $this->_form->p);
				} else {
					$content = $Table->rowsetToHtml($data);
				}
			} elseif (count($this->_response_content) > 0) {
				$this->_response_content['message'] = $oModel->getMessage();
				$data = array( 'id'=>'0',
                               'state'=>$this->_response_content['state'],
                               'message'=>$this->_response_content['message'],
				               'todo'=>$this->_response_content['todo']);
				$content = Zend_Json::encode($data);
			} else {
				foreach ($data as $rowArray) {
					$collection[$i]=array();
					foreach ($rowArray as $column => $value) {
						$collection[$i][$column] = utf8_encode(html_entity_decode($value));
					}
					$i++;
				}
				//$str_collection=print_r($collection, true);
				//Zwei_Utils_Debug::write($str_collection);
				if (method_exists($oModel,'getPrimary')) {
					$id=$oModel->getPrimary();
					$content = new Zend_Dojo_Data($id[1], @$collection);
				} else {
					/*
					 * En caso de que no exista ninguna PK simple, inventamos un ID aca para que funcione el datastore
					 * (SOLO PARA LISTAR, NO USAR ESTA ID PARA EDITAR, MODIFICAR O ELIMINAR)
					 */
					if (!isset($collection[0]['id'])) {
						for($j=0;$j<$i;$j++) {
							$collection[$j]['id']=$j;
						}
					}
					$content = new Zend_Dojo_Data('id', @$collection);
				}
				if (method_exists($oModel,'getLabel')) {
					$content->setLabel($oModel->getLabel());
				}
			}
			$this->view->content = $content;

		}//if (class_exists($ClassModel))

	}//public function indexAction()

	public function multiUpdateAction()
	{
		$ClassModel = Zwei_Utils_String::toClassWord($this->_form->model);
		$oSettings=new $ClassModel();

		$oSettings->getAdapter()->getProfiler()->setEnabled(true);

		foreach ($this->_form->id as $i=>$v) {
			//echo $i;
			$value=isset($this->_form->value[$i])?$this->_form->value[$i]:"";
			$where=$oSettings->getAdapter()->quoteInto('id = ?', $v);
			$data=array('value'=>$value);
			$oSettings->update($data, $where);

			$query=$oSettings->getAdapter()->getProfiler()->getLastQueryProfile()->getQuery();
			$query_params=print_r($oSettings->getAdapter()->getProfiler()->getLastQueryProfile()->getQueryParams(),true);

			Zwei_Utils_Debug::write("[TRANSACTION:{$this->_form->action}]\nUser:{$this->_user_info->user_name}\nQuery:$query\nQuery Params:$query_params", "../log/transactions");

		}
		$oSettings->getAdapter()->getProfiler()->setEnabled(false);
	}
}
