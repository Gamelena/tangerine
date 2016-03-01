<?php

/**
 * Dojo Multiple Select
 * Para usarlo con XML debe estar adentro de un formulario que cargue datos vía ajax.
 * No habilitado para cargar datos directamente de DataGrid ya que este no permite arrays dentro de un recordset como en este caso.
 *
 * @category   Zwei
 * @package    Zwei_Admin
 * @subpackage Elements
 * @version    $Id:$
 * @since      0.1
 */

class Elements_DojoxFormCheckedMultiSelectBinaryController extends Elements_BaseController
{
    public function indexAction()
    {
        $r = $this->getRequest();
        $this->view->style = $r->getParam('style') ? $r->getParam('style') : '';
        $this->view->invalidMessage = $r->getParam('invalidMessage') ? "invalidMessage=\"{$r->getParam('invalidMessage')}\"" : '';
        $this->view->promptMessage = $r->getParam('promptMessage') ? "promptMessage=\"{$r->getParam('promptMessage')}\"" : '';
        $this->view->value = $r->getParam('value', '0');
        
        $this->view->options = $this->options();
    }
    
    public function options()
    {
        $r = $this->getRequest();
        $options = "";
        $selected = array();
        
        if (!$r->getParam('value') && !$r->getParam('defaultValue')) {
            $value = (isset($request->{$this->view->target})) ? $request->{$this->view->target} : null;
        } else {
            $value = $r->getParam('value', $r->getParam('defaultValue'));
        }
        
        if ($r->getParam('table')) {
            $id = $r->getParam('tablePk') ? $r->getParam('tablePk') : 'id';
            
            $className = $r->getParam('table');
            $model = new $className;
        
            if ($r->getParam('tableMethod')) {
                $method = $r->getParam('tableMethod');
                $select = $model->$method();
                $title = "title";
            } else {
                if ($r->getParam('tableField')) {
                    $select = $model->select(array($r->getParam('tableField'), $id));
                } else if ($r->getParam('field')) {
                    $select = $model->select(array($r->getParam('field'), $id));
                } else {
                    $select = $model->select(array("title", $id));
                }
            }
            
            if (method_exists($select, "__toString")) { Debug::writeBySettings($select->__toString(), 'query_log'); 
            }
            
            $rows = $model->fetchAll($select); //Query para pintar, sin seleccionar, todas las opciones disponibles.
            
            if ($r->getParam('defaultValue') || $r->getParam('defaultText')) {
                $options .= "<option value=\"{$r->getParam('defaultValue', '')}\">{$r->getParam('defaultText', '')}</option>\r\n";
            }
           
        
            foreach ($rows as $i => $row) {
                $selected = "";
                if ($value & pow(2, $i)) {
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
            $rows = explode(",", $r->getParam('list'));
            foreach ($rows as $i => $row) {
                //Zwei_Utils_Debug::write('$id='.$id.'$row='.$row.'$value='.$value);
                $selected = "";
                if ($value & pow(2, $i)) {
                    $selected = "selected=\"selected\"";
                }
                $options .= "<option value=\"".pow(2, $i)."\" ".$selected." >$row</option>\r\n";
            }
        
        }
        return $options;
    }


}

