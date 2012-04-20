<?php
/**
 * Input Select
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Elements
 * @version $Id:$
 * @since 0.1
 */
class Zwei_Admin_Elements_Select extends Zwei_Admin_Elements_Element{

	function edit($i, $j, $display="inline"){
		$options=$this->options();
		return "<select style=\"display:$display\"  id=\"$this->target[$i]\" name=\"$this->target[$i]\">\r\n$options\r\n</select>";
	}

	function display($i, $j){
		$options=$this->options();
		return "<select disabled=\"disabled\"  id=\"edit{$i}_{$j}\" name=\"$this->target[$i]\"\">\r\n$options\r\n</select>";
	}

	function options(){
		$options="<option value=\"0\">* None *</option>\r\n";
		$id=!empty($this->params['TABLE_PK'])?$this->params['TABLE_PK']:'id';
		$ClassModel = Zwei_Utils_String::toClassWord($this->params['TABLE'])."Model";
		$model=new $ClassModel();

		if(!empty($this->params['TABLE_METHOD']))
		{
			$method=Zwei_Utils_String::toFunctionWord($this->params['TABLE_METHOD']);
			$select=$model->$method();
		}else{
			$select=$model->select(array($this->params['FIELD'], $id));
		}
			
		//Zwei_Utils_Debug::write($select->__toString());
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

?>
