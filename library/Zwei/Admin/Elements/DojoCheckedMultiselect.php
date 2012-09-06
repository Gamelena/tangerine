<?php

/**
 * Dojo Multiple Select
 * Para usarlo con XML debe estar adentro de un formulario con "tabs" para cargar datos vía ajax.
 * No habilitado para cargar datos vía de DataGrid ya que este no permite arrays dentro de un recordset como en este caso.
 * 
 * 
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Elements
 * @version $Id:$
 * @since 0.1
 */


class Zwei_Admin_Elements_DojoCheckedMultiselect extends Zwei_Admin_Elements_Element
{
    function edit($i, $j, $display="block")
    {
        $required = isset($this->params['REQUIRED']) && $this->params['REQUIRED']=="true" ? "required=\"true\"" : "required=\"false\"";
        $onchange = isset($this->params['ONCHANGE']) ? "onchange=\"{$this->params['ONCHANGE']}\"":'';
        $regexp = isset($this->params['REG_EXP']) ? "RegExp=\"{$this->params['REG_EXP']}\"" : '';
        $invalid_message = isset($this->params['INVALID_MESSAGE']) ? "invalidMessage=\"{$this->params['INVALID_MESSAGE']}\"" : '';
        $prompt_message = isset($this->params['PROMPT_MESSAGE']) ? "promptMessage=\"{$this->params['PROMPT_MESSAGE']}\"" : '';
        $value =  isset($this->params['DEFAULT_VALUE']) && !isset($this->params['DEFAULT_TEXT']) ? "value=\"{$this->params['DEFAULT_VALUE']}\"" : '';
        $options = $this->options();
        $return =  "<select dojoType=\"dojox.form.CheckedMultiSelect\" multiple=\"true\" id=\"edit{$i}_{$j}\" name=\"$this->target[$i]\" $value $onchange $required $regexp $invalid_message $prompt_message style=\"display:$display\" >\r\n$options\r\n</select>\r\n
        ";
        if (isset($this->params['STYLE'])) {
            $return .= "<style type=\"text/css\">.dojoxCheckedMultiSelectWrapper{$this->params['STYLE']}</style>";
        }
        return $return;
    }
  
    function display($i, $j)
    {
        $request = new Zwei_Utils_Form();
    
        if ($this->value == null) {
            $value = (isset($request->{$this->target})) ? $request->{$this->target} : null;
        } else {
            $value = $this->value;
        }
        
        
        $ClassModel = Zwei_Utils_String::toClassWord($this->params['TABLE'])."Model";   
        $model = new $ClassModel;
        $id = !empty($this->params['TABLE_PK']) ? $this->params['TABLE_PK'] : $model->getName().'.id';
        
        $field = (isset($this->params['TABLE_FIELD'])) ? $this->params['TABLE_FIELD'] : $this->params['FIELD'];
        
        
        if (isset($this->params['DEFAULT_VALUE']) && $value == $this->params['DEFAULT_VALUE']) {
            return isset($this->params['DEFAULT_TEXT']) ? $this->params['DEFAULT_TEXT'] : "Ninguno";
        } else if (!empty($this->params['TABLE_METHOD'])) {
            $method = Zwei_Utils_String::toFunctionWord($this->params['TABLE_METHOD']);
            $select = $model->$method();
        } else { 
            $select = $model->select(array($field, $id));
        }

        $select->where($model->getAdapter()->quoteInto("$id = ? ", $value));
        Zwei_Utils_Debug::writeBySettings($select->__toString(), 'query_log');   
        $rows = $model->fetchAll($select);
                
        return isset($rows[0]) ? $rows[0][$this->params['FIELD']] : ''; 
    }

    function options() 
    {
        
        $options = "";

        $selected = array();
        
        if (!empty($this->params['TABLE'])) {
            $id =! empty($this->params['TABLE_PK'])?$this->params['TABLE_PK'] : 'id';

            
            $ClassModel = Zwei_Utils_String::toClassWord($this->params['TABLE'])."Model";
            $model = new $ClassModel;
            $primary = $model->getPrimary() ? $model->getPrimary() : 'id';
            
            if (!empty($this->params['TABLE_METHOD'])) {
                $method = Zwei_Utils_String::toFunctionWord($this->params['TABLE_METHOD']);
                $select = $model->$method();
                $title = "title";
            } else {
                if (!empty($this->params['TABLE_FIELD'])) {
                    $title = $this->params['TABLE_FIELD'];
                } else if (!empty($this->params['FIELD'])) {
                    $title = $this->params['FIELD'];
                } else {
                    $title = "title";
                }
                $select = $model->select(array($title, $id));   
            }
            Debug::writeBySettings($select->__toString(), 'query_log');
            $rows = $model->fetchAll($select); //Query para pintar (no seleccionar) todas las opciones disponibles.
            $request = new Zwei_Utils_Form();
        
            if ($this->value == null) {
                $value = (isset($request->{$this->target})) ? $request->{$this->target} : null;
            } else {
                $value = $this->value;
            }
            

            if (isset($this->params['DEFAULT_VALUE']) && isset($this->params['DEFAULT_TEXT'])) {
                $text = $this->params['DEFAULT_TEXT'];
                $options .= "<option value=\"{$this->params['DEFAULT_VALUE']}\">$text</option>\r\n";
            }


            foreach ($rows as $row) {
                $selected = "";
                if (isset($this->value) && is_array($this->value) && in_array($row->$primary, $this->value)) {
                    $selected = "selected=\"selected\"";
                } 
                
                
                if (!empty($this->params['TABLE_FIELD'])) {
                    $options .= "<option value=\"".$row[$id]."\" ".$selected." >{$row[$this->params['TABLE_FIELD']]}</option>\r\n";
                } else if (!empty($this->params['FIELD'])) {
                    $options .= "<option value=\"".$row[$id]."\" ".$selected." >{$row[$this->params['FIELD']]}</option>\r\n";
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
            $rows = explode(",", $this->params['LIST']);
            foreach ($rows as $row) {
                //Zwei_Utils_Debug::write('$id='.$id.'$row='.$row.'$value='.$value);
                $selected = $row == $value ? "selected" : "";
                $options .= "<option value=\"".$row."\" ".$selected." >$row</option>\r\n";
            }
            
        }
        return $options;
    }
}
