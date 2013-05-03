<?php

/**
 * Dojo Multiple Select
 * Para usarlo con XML debe estar adentro de un formulario que cargue datos vía ajax.
 * No habilitado para cargar datos directamente de DataGrid ya que este no permite arrays dentro de un recordset como en este caso.
 *
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Elements
 * @version $Id:$
 * @since 0.1
 */

class Elements_DojoxFormCheckedMultiSelectController extends Zend_Controller_Action
{

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
        
        $this->view->readonly = $r->getParam('readonly', '') === 'true' ? "readonly=\"readonly\"" : '';
        $this->view->disabled = $r->getParam('disabled', '') === 'true' ? "disabled=\"disabled\"" : '';
        $this->view->required = $r->getParam('required', '') === 'true' ? "required=\"true\"" : '';
        $this->view->onblur = $r->getParam('onblur') ? "onblur=\"{$r->getParam('onblur')}\"" : '';
        $this->view->regExp = $r->getParam('regExp') ? "regExp=\"{$r->getParam('regExp')}\"" : '';
        $this->view->invalidMessage = $r->getParam('invalidMessage') ? "invalidMessage=\"{$r->getParam('invalidMessage')}\"" : '';
        $this->view->promptMessage= $r->getParam('promptMessage') ? "promptMessage=\"{$r->getParam('promptMessage')}\"" : '';
        
        $this->view->options = $this->options();
    }
    
    public function options()
    {
        $r = $this->getRequest();
        $options = "";
        $selected = array();
        
        if ($r->getParam('table')) {
            $id = $r->getParam('tablePk') ? $r->getParam('tablePk') : 'id';
            
            $className = $r->getParam('table');
            $model = new $className;
            $primary = $model->getPrimary() ? $model->getPrimary() : 'id';
        
            if ($r->getParam('tableMethod')) {
                $method = $r->getParam('tableMethod');
                $select = $model->$method();
                $title = "title";
            } else {
                if ($r->getParam('tableField')) {
                    $select = $model->select(array($r->getParam('tableField'), $id));
                } else if ($r->getParam('field')){
                    $select = $model->select(array($r->getParam('field'), $id));
                } else {
                    $select = $model->select(array("title", $id));
                }
                $select = $model->select(array($title, $id));
            }
            Debug::writeBySettings($select->__toString(), 'query_log');
            $rows = $model->fetchAll($select); //Query para pintar (no seleccionar) todas las opciones disponibles.
        
            if ($r->getParam('value')) {
                $value = $this->value;
            } else {
                $value = $r->getParam('target') ? $r->getParam('target') : null;
            }
        
        
            if ($r->getParam('defaultValue') !== false || $r->getParam('defaultText') !== false) {
                $options .= "<option value=\"{$r->getParam('defaultValue', '')}\">{$r->getParam('defaultText', '')}</option>\r\n";
            }
        
            foreach ($rows as $row) {
                $selected = "";
                if (is_array($r->getParam('value', null)) && in_array($row->$primary, is_array($r->getParam('value')))) {
                    $selected = "selected=\"selected\"";
                }
        
        
                if ($r->getParam('tableField')) {
                    $options .= "<option value=\"".$row[$id]."\" ".$selected." >{$row[$r->getParam('tableField')]}</option>\r\n";
                } else if ($r->getParam('field')) {
                    $options .= "<option value=\"".$row[$id]."\" ".$selected." >{$row[$r->getParam('field')]}</option>\r\n";
                } else {
                    $options .= "<option value=\"".$row[$id]."\" ".$selected." >{$row[$title]}</option>\r\n";
                }
            }
        } else {
            if ($this->value == null) {
                $value = (isset($request->{$this->target})) ? $request->{$this->target} : null;
            } else {
                $value = $this->value;
            }
        
            $options = "";
            $rows = explode(",", $r->getParam('list'));
            foreach ($rows as $row) {
                //Zwei_Utils_Debug::write('$id='.$id.'$row='.$row.'$value='.$value);
                $selected = $row == $value ? "selected" : "";
                $options .= "<option value=\"".$row."\" ".$selected." >$row</option>\r\n";
            }
        
        }
        return $options;
    }


}

