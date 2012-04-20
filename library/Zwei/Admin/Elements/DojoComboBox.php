<?php
/**
 * Combobox Dojo
 *
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Elements
 * @version $Id:$
 * @since 0.1
 */


class Zwei_Admin_Elements_DojoComboBox extends Zwei_Admin_Elements_Element{

	function edit($i, $j, $display="block"){
		$options=$this->options();
		return "<select dojoType=\"dijit.form.ComboBox\" style=\"display:$display\" id=\"edit{$i}_{$j}\" name=\"{$this->target}[$i]\">\r\n$options\r\n</select>";
	}


	function display($i, $j){
		$options=$this->options();
		return "<select dojoType=\"dijit.form.ComboBox\" disabled=\"disabled\" id=\"field{$i}_{$j}\">\r\n$options\r\n</select>";
	}

	function options(){
		$options="<option value=\"0\">* None *</option>\r\n";
		//$dao=new DAO();
		$id=!empty($this->params['TABLEPK'])?$this->params['TABLEPK']:'id';
		$model=new $this->params['TABLE'];
		//$model=new MenuModel();

		$select=$model->select();
		$select->from($model, array($id, $this->params['FIELD']));

		//$dao->query("SELECT `{$this->params['FIELD']}`,`$id` FROM {$this->params['TABLE']}");

		$rows=$model->fetchAll($select);
		$selected=array();


		$request=new Zwei_Utils_Form();

		if($this->value==null){
			if(isset($request->{$this->target}))$value=$request->{$this->target};
			else $value=null;
		}else{
			$value=$this->value;
		}


		//while($row=$dao->getAll()){
		foreach($rows as $row){
			$selected[$row[$id]] = $row[$id]==$value ? "selected" : "";
			$options.="<option value=\"".$row[$id]."\" ".$selected[$row[$id]]." >{$row[$this->params['FIELD']]}</option>\r\n";
		}

		return $options;

	}



}

