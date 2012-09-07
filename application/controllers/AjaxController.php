<?php
/**
 * Controlador de peticiones Ajax
 *
 * Acá se deben escribir las funciones que retornen una respuesta Ajax
 *
 * @package Controllers
 * @version Id:$
 * @since versión 0.5
 *
 */

class AjaxController extends Zend_Controller_Action
{

    public function init()
    {
		if(!Zend_Auth::getInstance()->hasIdentity()) $this->_redirect('index/login');
		$this->_helper->layout()->disableLayout();
		$this->view->base_url=BASE_URL;
    }

    public function abonadosCountAction()
    {
		$this->_helper->viewRenderer('index');
		$id=$_REQUEST['id'];
		$Abonados=new AbonadosModel();
		$select=$Abonados->select('count')->where($Abonados->getAdapter->quoteInto('id_promocion = ?', $id));
		$result=$Abonados->fetchAll($select);
		$num_abonados=number_format($result[0]['count'],0,',','.');
		$this->view->content="&nbsp<b>$num_abonados</b>";

    }

    public function indexAction()
    {
		 
		 
    }

    public function loadingAction()
    {
        // action body
    }


}

