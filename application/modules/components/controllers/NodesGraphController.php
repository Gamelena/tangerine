<?php

class Components_NodesGraphController extends Zend_Controller_Action
{
    /**
     * 
     * @var string
     */
    private $_xmlNodes = 'mensajes.xml';
    
    /**
     * 
     * @var string
     */
    private $_xmlLinks = 'enlaces.xml';
    /**
     * 
     * @var string
     */
    private $_primary = 'id';
    /**
     * 
     * @var Zwei_Db_Table
     */
    private $_modelNodes;
    /**
     * 
     * @var Zwei_Db_Table
     */
    private $_modelLinks;
    
    public function init()
    {
        if (!Zwei_Admin_Auth::getInstance()->hasIdentity()) {
            $this->_redirect('admin/login');
        } else {
            $this->_modelNodes = new MensajesModel();
            $this->_modelLinks = new TreeLinksModel();
            $config = new Zend_Config($this->getInvokeArg('bootstrap')->getOptions());
            
            $this->view->domPrefix = isset($config->zwei->layout->mainPane) && $config->zwei->layout->mainPane == 'dijitTabs'
                ? str_replace('.', '_', $this->_xmlNodes)
                : '';
            $this->view->domPrefix2 = isset($config->zwei->layout->mainPane) && $config->zwei->layout->mainPane == 'dijitTabs'
                ? str_replace('.', '_', $this->_xmlLinks)
                : '';
        }
    }

    public function indexAction()
    {
        $this->view->idCampana = $this->getRequest()->getParam("id");
        $r = $this->getRequest();
        $params = $r->getParams();
        
        $this->view->paramsLinks = $params;
        $this->view->paramsLinks['group_id'] = $this->view->idCampana;
        $this->view->paramsLinks['p'] = $this->_xmlLinks;
        $this->view->paramsLinks['loadPartial'] = 'true';
        
        $this->view->paramsNodes = $params;
        $this->view->paramsNodes['p'] = $this->_xmlNodes;
        $this->view->paramsNodes['loadPartial'] = 'true';
        
        $this->view->request = $r;
        
        //Obtener Nodos
        $where = $this->_modelNodes->getAdapter()->quoteInto("idCampana = ? ", $r->getParam("id"));
        $select = $this->_modelNodes->select()->where($where);
        Debug::writeBySettings($select->__toString(), 'query_log');//Debug query
        $this->view->nodes = $this->_modelNodes->fetchAll($select);
        
        //Obtener enlaces
        $where = $this->_modelLinks->getAdapter()->quoteInto("group_id = ? ", $r->getParam("id"));
        $select = $this->_modelLinks->select()->where($where);
        Debug::writeBySettings($select->__toString(), 'query_log');//Debug query
        $this->view->links = $this->_modelLinks->fetchAll($select);
    }
}

