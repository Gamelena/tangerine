<?php
/**
 * Controlador de funciones genericas para Zend XML Admin
 *
 * Permite invocar métodos de Zwei_Utils_CustomFunctions() por URL.
 * Para ser invocado mediante el atributo "functions" de los components xml del admin.
 *
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
        if(!Zwei_Admin_Auth::getInstance()->hasIdentity()) {
            echo "<script>if (window.parent != undefined) window.parent.location.href='".BASE_URL."admin/login'
              else window.location.href='".BASE_URL."admin/login';</script>"; 
            //$this->_redirect('admin/login');
        }
        $this->_user_info = Zend_Auth::getInstance()->getStorage()->read();
    }

    public function indexAction(){
        $CustomFunctions = new Zwei_Utils_CustomFunctions();
        $form = new Zwei_Utils_Form();
        $string_params = $form->params;
        $method = Zwei_Utils_String::toFunctionWord($form->method);
         
        if (isset($form->id)) {
            $CustomFunctions->setId($form->id);
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
