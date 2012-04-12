<?php
/**
 * Controlador de funciones genericas para Zend XML Admin
 *
 * Permite invocar métodos de Zwei_Utils_CustomFunctions() por URL.
 * Para ser invocado mediante el atributo "functions" de los components xml del admin.
 *
 * @example: <section type="table_dojo" functions="assign_request,excel_export" (...)>
 * este ejemplo pintará botones
 * que ejecuten $CustomFunctions->assignRequest(...) y $CustomFunctions->excelExport(...)
 *
 * @package Controllers
 * @version $Id:$
 * @since 0.1
 *
 */
class FunctionsController extends Zend_Controller_Action
{
	public function init()
	{
		$this->_helper->layout()->disableLayout();
		if (!Zend_Auth::getInstance()->hasIdentity()) $this->_redirect('index/login');
		$this->_user_info = Zend_Auth::getInstance()->getStorage()->read();
		$this->_acl = new Zwei_Admin_Acl($this->_user_info->user_name);
	}

	public function indexAction(){
		$CustomFunctions = new Zwei_Utils_CustomFunctions();
		$Form = new Zwei_Utils_Form();
		$string_params = $Form->params;
		$method = Zwei_Utils_String::toFunctionWord($Form->method);
		 
		if (isset($Form->id)) {
			$CustomFunctions->setId($Form->id);
		}
		 
		$params = explode(",", $string_params);
		 
		if (is_array($params)) {
			$count_params=count($params);
		} else {
			$count_params=0;
		}

		switch($count_params)
		{
			case 0:
				$response = $CustomFunctions->$method();
				break;
			case 1:
				$response = $CustomFunctions->$method($params[0]);
				break;
			case 2:
				$response = $CustomFunctions->$method($params[0], $params[1]);
				break;
			case 3:
				$response = $CustomFunctions->$method($params[0], $params[1], $params[2]);
				break;
			case 4:
				$response = $CustomFunctions->$method($params[0], $params[1], $params[2], $params[3]);
				break;
			case 5:
				$response = $CustomFunctions->$method($params[0], $params[1], $params[2], $params[3], $params[4]);
				break;
			default:
				Zwei_Utils_Debug::write("Núm. de parámetros excedido ($count_params) para Zwei_Utils_CustomFunctions::$method($string_params)");
				$response=false;
		}

		$this->view->content=$response;
	}
}