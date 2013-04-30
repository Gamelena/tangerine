<?php
/**
 * Controlador de index
 *
 * Controlador de front-office, si existiese
 *
 * @package Controllers
 * @version $Id:$
 * @since 0.1
 */

class IndexController extends Zend_Controller_Action
{
    public function init()
    {
    }

    public function indexAction()
    {
        $this->_helper->redirector('index', 'admin');
    }
}

