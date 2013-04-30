<?php

class Components_NodesGraphController extends Zend_Controller_Action
{
    /**
     * 
     * @var string
     */
    private $_xmlMessages = 'mensajes.xml';
    
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
        $this->_modelNodes = new MensajesModel();
        $this->_modelLinks = new TreeLinksModel();
        $config = new Zend_Config($this->getInvokeArg('bootstrap')->getOptions());
        
        $this->view->domPrefix = isset($config->zwei->layout->mainPane) && $config->zwei->layout->mainPane == 'dijitTabs'
            ? str_replace('.', '_', $this->_xmlMessages)
            : '';
        $this->view->domPrefix2 = isset($config->zwei->layout->mainPane) && $config->zwei->layout->mainPane == 'dijitTabs'
            ? str_replace('.', '_', $this->_xmlLinks)
            : '';
    }

    public function indexAction()
    {
        $this->view->idCampana = $this->getRequest()->getParam("id");
        $tableDojo = new Zwei_Admin_Components_TableDojo($this->_xmlMessages);
        $this->view->jsCrud = $tableDojo->getJsCrud($this->view->domPrefix, $this->_primary, "addNode(respuesta, id, {$this->getRequest()->getParam("id")});");
        
        $tableDojo = new Zwei_Admin_Components_TableDojo($this->_xmlLinks);
        $this->view->jsCrud2 = $tableDojo->getJsCrud($this->view->domPrefix2, $this->_primary, "addLink(respuesta, {$this->getRequest()->getParam("id")});");
        
        $this->view->request = $this->getRequest();
        
        //Obtener Nodos
        $where = $this->_modelNodes->getAdapter()->quoteInto("idCampana = ? ", $this->getRequest()->getParam("id"));
        $select = $this->_modelNodes->select()->where($where);
        Debug::writeBySettings($select->__toString(), 'query_log');//Debug query
        $this->view->nodes = $this->_modelNodes->fetchAll($select);
        
        //Obtener enlaces
        $where = $this->_modelLinks->getAdapter()->quoteInto("group_id = ? ", $this->getRequest()->getParam("id"));
        $select = $this->_modelLinks->select()->where($where);
        Debug::writeBySettings($select->__toString(), 'query_log');//Debug query
        $this->view->links = $this->_modelLinks->fetchAll($select);
    }
}

