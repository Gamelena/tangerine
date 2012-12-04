<?php

/**
 * Auxiliar para Zwei_Admin_Components_Table, CRUD en modo edición 
 *
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Components
 * @version $Id:$
 * @since 0.1
 *
 */

class Zwei_Admin_Components_Helpers_EditTable extends Zwei_Admin_Controller
{
    public function display($mode='EDIT')
    {
        $oForm = new Zwei_Utils_Form();
        $out = "<table style=\"width:auto;\">";
        $count = count($this->layout);
        if (!isset($this->id)) $this->id = array();
        else if (!is_array($this->id)) $this->id = array($this->id);
        if (!isset($this->layout[1]['VALUE'])) $this->layout[1]['VALUE'] = array("");
      
        $vcount = count($this->layout[1]['VALUE']);
        for ($i=0; $i<$vcount; $i++) {
            if (in_array($this->layout[1]['VALUE'][$i],$this->id) || $vcount == 1) {
                for ($j=1; $j<$count; $j++) {
                    $node = $this->layout[$j];
                    $params = array();
                    foreach ($node as $k=>$v) if ($k != 'VALUE') $params[$k] = $v;
                    if (!isset($node['VALUE'][$i])) $node['VALUE'][$i] = "";
                    if (!empty($node['VALUE'][$i]) || isset($oForm->{$node['TARGET']}) && is_array($oForm->{$node['TARGET']})){
                        $value = $node['VALUE'][$i];
                    } else {
                        $value = isset($oForm->{$node['TARGET']})?$oForm->{$node['TARGET']}:'';
                    }
                    $ClassElement = "Zwei_Admin_Elements_".Zwei_Utils_String::toClassWord($node['TYPE']);
                    $element = new $ClassElement($node['VISIBLE'],$node['EDIT'],$node['NAME'],$node['TARGET'],$value,$params);
                    if ($node[$mode]) {
                        if($mode == 'ADD') $pfx="_add";
                        else $pfx = "";
                        $out .= "<tr><td>{$node['NAME']}:</td><td>".$element->edit($i,$pfx.$j)."</td></tr>";
                    }
                }
                $out .= "<tr><td>&nbsp;</td></tr>";
            }
        }
        $out .= "</table>";
        return $out;
    }

    function edit()
    {
        $oForm = new Zwei_Utils_Form();
        $count = count($this->id);
        $ecount = count($this->layout);

        $ClassName = Zwei_Utils_String::toClassWord($this->target)."Model";
      
        //Zwei_Utils_Debug::write($this->id);
      
        $oModel = new $ClassName;
        $data = array();
        $ret = false;

        for ($i=0; $i<$count; $i++) {

            $tmp = each($this->id);
            $k = $tmp['key']; //obtener el id real del form
            for ($j=1; $j<$ecount; $j++) {
                if ($this->layout[$j]['EDIT'] && $this->layout[$j]['TYPE'] != "id_box") {
                    $node = $this->layout[$j];
                    $params = array();
                        
                    foreach ($node as $l=>$v) {
                        if ($l!='VALUE') $params[$l] = $v;
                    }
                        
                    $ClassElement="Zwei_Admin_Elements_".Zwei_Utils_String::toClassWord($this->layout[$i]['TYPE']);
                    $e=new $ClassElement(false,false,"",$this->layout[$j]['TARGET'],null,$params);
                    if (isset($oForm->{$this->layout[$j]['TARGET']}) && isset($oForm->{$this->layout[$j]['TARGET']}[$k]))  {
                        $value=$oForm->{$this->layout[$j]['TARGET']}[$k];
                    } else {
                        $value="";
                    }
                    $v=$e->get($value,$k);
                        
                    if ((string) $v!= '{no-change}') {
                        $data[$this->layout[$j]['TARGET']]=$v;
                    }
                }
            }

            if(isset($this->id[$k])){
                $where=$oModel->getAdapter()->quoteInto('id = ?', $this->id[$k]);
                $oModel->update($data, $where);
            }
        }
        return $ret;
    }

    function add()
    {
        $oForm = new Zwei_Utils_Form();
        $ecount = count($this->layout);
        $ClassName = Zwei_Utils_String::toClassWord($this->target)."Model";
        $oModel = new $ClassName;
        $data = array();

        for($i=1; $i<$ecount; $i++)
        {
            if($this->layout[$i]['ADD']&&$this->layout[$i]['TYPE']!="idbox"){
                $ClassElement = "Zwei_Admin_Elements_".Zwei_Utils_String::toClassWord($this->layout[$i]['TYPE']);
                $e = new $ClassElement(false,false,"",$this->layout[$i]['TARGET']);
                $v = isset($oForm->{$this->layout[$i]['TARGET']})?$e->get($oForm->{$this->layout[$i]['TARGET']}[0],0):"";
                if($v == "{no-change}") $v = "";
                $data[$this->layout[$i]['TARGET']] = $v;
            }
        }
        $oModel->insert($data);
    }


    function delete()
    {
        $ClassName = Zwei_Utils_String::toClassWord($this->target)."Model";
        $oModel = new $ClassName;
        foreach ($this->id as $id) {
            $where=$oModel->getAdapter()->quoteInto('id = ?', $id);
            $oModel->delete($where);
        }

    }
}