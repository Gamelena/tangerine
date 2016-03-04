<?php
/**
 * Controlador de front-office, si existiese.
 *
 * @package Controllers
 * @version $Id:$
 * @since   0.1
 */
class IndexController extends Zend_Controller_Action
{
    public function init()
    {
    }

    public function indexAction()
    {
        $params = array();
        $r = $this->getRequest();
        if ($r->getParam('template')) {
            $params['template'] = $r->getParam('template');
        }
        if ($r->getParam('theme')) {
            $params['theme'] = $r->getParam('theme');
        }
        
        $this->_helper->redirector('index', 'admin', 'default', $params);
    }
}
