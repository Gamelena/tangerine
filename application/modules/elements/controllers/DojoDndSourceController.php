<?php

class Elements_DojoDndSourceController extends Zend_Controller_Action
{
    /**
     * 
     * @var Zend_Db_Table_Abstract
     */
    protected $_model;
    
    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $r = $this->getRequest();

        $this->view->i =  $r->getParam('i');
        $this->view->domId =  $r->getParam('domId');
        $this->view->target =  $r->getParam('target');
        $this->view->selectedTitle =  $r->getParam('selectedTitle', 'Seleccionados');
        $this->view->unselectedTitle =  $r->getParam('unselectedTitle', 'No seleccionados');
        $this->view->saveUnselected = $r->getParam('saveUnselected', false);
        $this->view->editable = $r->getParam('editable', false);//@todo implement me in view
        $this->view->droppable = $r->getParam('droppable', false);//@todo implement me in view
        
        
        $this->view->unselectedTarget = $this->view->target;
        
        if ($this->view->saveUnselected) {
            $this->view->unselectedTarget = 'data[tangerineUnselected]';
        }
        
        
        $this->view->unselectedItems = array();
        $this->view->selectedItems = array();
        
        $selected = array();
        
        if ($r->getParam('table')) {
            $this->_model = $r->getParam('table');
            $this->_model = new $this->_model();
            $id = $r->getParam('tablePk') ? $r->getParam('tablePk') : 'id';
            
            $primary = $this->_model->info(Zend_Db_Table::PRIMARY);
            $primary = $primary[1];
            
            if ($r->getParam('tableMethod')) {
                $method = $r->getParam('tableMethod');
                $select = $this->_model->$method();
                $title = "title";
            } else {
                if ($r->getParam('tableField')) {
                    $select = $this->_model->select(array($r->getParam('tableField'), $id));
                } else if ($r->getParam('field')){
                    $select = $this->_model->select(array($r->getParam('field'), $id));
                } else {
                    $select = $this->_model->select(array("title", $id));
                }
            }
            if (method_exists($select, "__toString")) Console::debug($select->__toString());
            $rows = $this->_model->fetchAll($select); //Query para pintar, sin seleccionar, todas las opciones disponibles.
            
            if ($r->getParam('value')) {
                $value = $r->getParam('value');
            } else {
                $value = $r->getParam('target') ? $r->getParam('target') : null;
            }
            
            
            if ($r->getParam('defaultValue') || $r->getParam('defaultText')) {
                $this->view->unselectedItems[$r->getParam('defaultValue', '')] = $r->getParam('defaultText', '');
            }
             
            foreach ($rows as $row) {
                $selected = is_array($r->getParam('value', null)) && in_array($row->$primary, $r->getParam('value'));
                
                if ($r->getParam('tableField')) {
                    if ($selected) {
                        $this->view->selectedItems[$row[$id]] = $row[$r->getParam('tableField')];
                    } else {
                        $this->view->unselectedItems[$row[$id]] = $row[$r->getParam('tableField')];
                    }
                } else if ($r->getParam('field')) {
                    if ($selected) {
                        $this->view->selectedItems[$row[$id]] = $row[$r->getParam('field')];
                    } else {
                        $this->view->unselectedItems[$row[$id]] = $row[$r->getParam('field')];
                    }
                } else {
                    if ($selected) {
                        $this->view->selectedItems[$row[$id]] = $row["title"];
                    } else {
                        $this->view->unselectedItems[$row[$id]] = $row["title"];
                    }
                }
            }
            
        } else {
            if (!$r->getParam('value')) {
                $value = (isset($request->{$r->getParam('target')})) ? $request->{$r->getParam('target')} : null;
            } else {
                $value = $r->getParam('value');
            }
            
            $options = "";
            $rows = $r->getParam('list') ? explode(",", $r->getParam('list')) : array();
            
            foreach ($rows as $row) {
                $selected = $row == $value;
                if ($selected) {
                    $this->view->selectedItems[$row] = $row;
                } else {
                    $this->view->unselectedItems[$row] = $row;
                }
            }
        }
    }
}

