<?php

/**
 * Dojo Multiple Select
 * Para usarlo con XML debe estar adentro de un formulario que cargue datos vía ajax @forms.ajax="true".
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
        
        $this->view->readonly = $r->getParam('readonly') === 'true' || $r->getParam($r->getParam('mode')) == 'readonly' ? "readonly=\"readonly\"" : '';
        $this->view->disabled = $r->getParam('disabled') === 'true' || $r->getParam($r->getParam('mode')) == 'disabled' ? "disabled=\"disabled\"" : '';
        
        $this->view->required = $r->getParam('required', '') === 'true' ? "required=\"true\"" : '';
        $this->view->onblur = $r->getParam('onblur') ? "onblur=\"{$r->getParam('onblur')}\"" : '';
        $this->view->onchange = $r->getParam('onchange') ? "onchange=\"{$r->getParam('onchange')}\"" : '';
        $this->view->onclick = $r->getParam('onclick') ? "onclick=\"{$r->getParam('onclick')}\"" : '';
        $this->view->style = $r->getParam('style') ? $r->getParam('style') : '';
        $this->view->invalidMessage = $r->getParam('invalidMessage') ? "invalidMessage=\"{$r->getParam('invalidMessage')}\"" : '';
        $this->view->promptMessage = $r->getParam('promptMessage') ? "promptMessage=\"{$r->getParam('promptMessage')}\"" : '';
        
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
            //[TODO] deprecar getPrimary y usar info('primary') nativo de ZF
            $primary = method_exists($model, 'getPrimary') && $model->getPrimary() ? $model->getPrimary() : 'id';
        
            if ($r->getParam('tableMethod')) {
                $method = $r->getParam('tableMethod');
                $select = $model->$method();
                $title = "title";
            } else {
                $select = $model->select();
            }
            if (method_exists($select, "__toString")) Debug::writeBySettings($select->__toString(), 'query_log');
            $rows = $model->fetchAll($select); //Query para pintar, sin seleccionar, todas las opciones disponibles.
        
            if ($r->getParam('value')) {
                $value = $r->getParam('value');
            } else {
                $value = $r->getParam('target') ? $r->getParam('target') : null;
            }
        
            
            if ($r->getParam('defaultValue') || $r->getParam('defaultText')) {
                $options .= "<option value=\"{$r->getParam('defaultValue', '')}\">{$r->getParam('defaultText', '')}</option>\r\n";
            }
           
        
            foreach ($rows as $row) {
                $selected = "";
                if (is_array($r->getParam('value', null)) && in_array($row->$primary, $r->getParam('value'))) {
                    $selected = "selected=\"selected\"";
                }
        
        
                if ($r->getParam('tableField')) {
                    $options .= "<option value=\"".$row[$id]."\" ".$selected." >{$row[$r->getParam('tableField')]}</option>\r\n";
                } else if ($r->getParam('field')) {
                    $options .= "<option value=\"".$row[$id]."\" ".$selected." >{$row[$r->getParam('field')]}</option>\r\n";
                } else {
                    $options .= "<option value=\"".$row[$id]."\" ".$selected." >{$row["title"]}</option>\r\n";
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
                //Zwei_Utils_Debug::write('$id='.$id.'$row='.$row.'$value='.$value);
                $selected = $row == $value ? "selected" : "";
                $options .= "<option value=\"".$row."\" ".$selected." >$row</option>\r\n";
            }
        
        }
        return $options;
    }


}

