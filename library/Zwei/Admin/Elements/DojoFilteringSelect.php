<?php

/**
 * Dojo Filtering Select
 * 
 * 
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Elements
 * @version $Id:$
 * @since 0.1
 */


class Zwei_Admin_Elements_DojoFilteringSelect extends Zwei_Admin_Elements_Element
{
	function edit($i, $j, $display="block")
	{
		$required = isset($this->params['REQUIRED']) ? "required=\"true\"" : "required=\"false\"";
		$onchange = isset($this->params['ONCHANGE']) ? "onchange=\"{$this->params['ONCHANGE']}\"":'';
		$disabled = isset($this->params['DISABLED']) ? "disabled=\"{$this->params['DISABLED']}\"":'';
		$readonly = isset($this->params['READONLY']) ? "readonly=\"{$this->params['READONLY']}\"":'';
		$regexp = isset($this->params['REG_EXP']) ? "RegExp=\"{$this->params['REG_EXP']}\"" : '';
		$invalid_message = isset($this->params['INVALID_MESSAGE']) ? "invalidMessage=\"{$this->params['INVALID_MESSAGE']}\"" : '';
		$prompt_message = isset($this->params['PROMPT_MESSAGE']) ? "promptMessage=\"{$this->params['PROMPT_MESSAGE']}\"" : '';
		$value =  isset($this->params['DEFAULT_VALUE']) && !isset($this->params['DEFAULT_TEXT']) ? "value=\"{$this->params['DEFAULT_VALUE']}\"" : '';
		$options = $this->options();
		$return =  "<select dojoType=\"dijit.form.FilteringSelect\" id=\"edit{$i}_{$j}\" name=\"$this->target[$i]\" onload=\"dijit.byId('edit{$i}_{$j}').set('value', dijit.byId('edit{$i}_{$j}').get('value'))\" $value $onchange $required $regexp $invalid_message $prompt_message $disabled $readonly style=\"display:$display\" >\r\n$options\r\n</select>\r\n
		";
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
            
            if (!empty($this->params['TABLE_METHOD'])) {
                $method = Zwei_Utils_String::toFunctionWord($this->params['TABLE_METHOD']);
                $select = $model->$method();
            } else {
                if (!empty($this->params['TABLE_FIELD'])) {
                    $select = $model->select(array($this->params['TABLE_FIELD'], $id));
                } else if (!empty($this->params['FIELD'])){
                    $select = $model->select(array($this->params['FIELD'], $id));
                } else {
                    $select = $model->select(array("title", $id));                	
                }   
            }
            Zwei_Utils_Debug::writeBySettings($select->__toString(), 'query_log');
            $rows = $model->fetchAll($select);
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
                $selected = $row[$id] == $this->value ? "selected" : "";
                if (!empty($this->params['TABLE_FIELD'])) {
                    $options .= "<option value=\"".$row[$id]."\" ".$selected." >{$row[$this->params['TABLE_FIELD']]}</option>\r\n";
                } else if (!empty($this->params['FIELD'])) {
                    $options .= "<option value=\"".$row[$id]."\" ".$selected." >{$row[$this->params['FIELD']]}</option>\r\n";
                } else {
                	$options .= "<option value=\"".$row[$id]."\" ".$selected." >{$row["title"]}</option>\r\n";
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
