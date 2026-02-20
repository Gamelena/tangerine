<?php

/**
 * Dojo Multiple Select
 * Para usarlo con XML debe estar adentro de un formulario que cargue datos vía ajax @forms.ajax="true".
 * No habilitado para cargar datos directamente de DataGrid ya que este no permite arrays dentro de un recordset como en este caso.
 *
 * @category   Gamelena
 * @package    Gamelena_Admin
 * @subpackage Elements
 * @version    $Id:$
 * @since      0.1
 */

class Elements_DojoxFormCheckedMultiSelectController extends Elements_BaseController
{
    public function indexAction()
    {
        $r = $this->getRequest();
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
            if (method_exists($select, "__toString")) { Debug::writeBySettings($select->__toString(), 'query_log'); 
            }
            $rows = $model->fetchAll($select); //Query para pintar, sin seleccionar, todas las opciones disponibles.
        
            $value = $r->getParam('value');
            
            if ($r->getParam('value') && !is_array($r->getParam('value'))) {
                $value = json_decode($r->getParam('value'));
            }
            
            foreach ($rows as $row) {
                $selected = "";
                if (is_array($value) && in_array($row->$primary, $value)) {
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
                //Console::log('$id='.$id.'$row='.$row.'$value='.$value);
                $selected = $row == $value ? "selected" : "";
                $options .= "<option value=\"".$row."\" ".$selected." >$row</option>\r\n";
            }
        
        }
        return $options;
    }


}

