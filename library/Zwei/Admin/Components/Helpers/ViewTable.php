<?php
/**
 * Auxiliar para Zwei_Admin_Components_Table, CRUD en modo listar
 *
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Components
 * @version $Id:$
 * @since 0.1
 *
 */

class Zwei_Admin_Components_Helpers_ViewTable extends Zwei_Admin_Controller
{
    function display(){
        $form=new Zwei_Utils_Form();
        $out="<table cellspacing=\"1\">\r\n<tr>";
        $count=count($this->layout);
        $start=isset($form->start)?$form->start:"";
        $search=isset($form->search)?urlencode($form->search):"";
        $text=isset($form->text)?urlencode($form->text):"";
      
        for($i=1; $i<$count; $i++){
            if($this->layout[$i]['VISIBLE']&&$this->layout[$i]['TARGET']!=""){
                if(isset($form->dir)&&$form->dir=="ASC")$dir="DESC";
                else $dir="ASC";
                $image="";
                if(isset($form->sort)&&$form->sort==$this->layout[$i]['TARGET']){
                    if(isset($form->dir)&&$form->dir=="ASC")$image="<img src=\"images/arrow_up.png\" />";
                    else $image="<img src=\"images/arrow_down.png\" />";
                }
                $out.="<th class=\"theader\"><a class=\"white\" href=\"javascript:void(0)\" onclick=\"dojo_panel_central.set('href','index/components?p=$this->page&start=$start&search=$search&text=$text&sort=".urlencode($this->layout[$i]['TARGET'])."&dir=$dir".$this->getRequested_params()."&ajax=1');sort='".addslashes($this->layout[$i]['TARGET'])."';sortdir='$dir'\">$image {$this->layout[$i]['NAME']}</a></th>";
            }elseif($this->layout[$i]['VISIBLE'] || $this->layout[$i]['TYPE']=='id_box'){
                $out.="<th class=\"theader\">{$this->layout[$i]['NAME']}</th>";
            }
        }
        $out.="<th class=\"theader\">&nbsp;</th></tr>\r\n";
        if(isset($this->layout[1]['VALUE'])){
            $vcount=count($this->layout[1]['VALUE']);
        }else $vcount=0;
        for($i=0; $i<$vcount; $i++){
            $id=0;
            $out.="<tr class=\"trow\">";
            for($j=1; $j<$count; $j++){
                $node=$this->layout[$j];
                //var_dump($node);
                $params=array();
                foreach($node as $k=>$v)if($k!='VALUE')$params[$k]=$v;
                if($node['TYPE']=='id_box')$id=$node['VALUE'][$i];
                $params['ID']=$id;
                $ClassElement="Zwei_Admin_Elements_".Zwei_Utils_String::toClassWord($node['TYPE']);
                $element=new $ClassElement($node['VISIBLE'],$node['EDIT'],$node['NAME'],$node['TARGET'],$node['VALUE'][$i],$params);
                if($node['VISIBLE'] || $node['TYPE']=='id_box')$out.="<td>".$element->display($i,$j)."</td>\r\n";
            }
            $out.="<td>";
            if(isset($this->layout[0]['EDIT']) && $this->layout[0]['EDIT']=="true"){
                $out.="<a id=\"c_edit{$i}\" href=\"javascript:void(0)\" onclick=\"edit($i,$count,'$this->page')\" title=\"Edit\"><img src=\"images/admin/32/pencil.png\" alt=\"Editar\"  /></a><img id=\"loading{$i}\" src=\"images/admin/loading.gif\" style=\"display:none\" /><a style=\"display:none\" id=\"c_update{$i}\" href=\"javascript:void(0)\" onclick=\"update($i,$count)\" title=\"Save\"><img src=\"images/admin/accept.png\" alt=\"Aceptar\" /></a> <a style=\"display:none\" id=\"c_cancel{$i}\" href=\"javascript:void(0)\" onclick=\"cancel($i,$count)\" title=\"Cancel\"><img src=\"images/admin/cancel.png\" alt=\"Cancelar\" /></a>";
                //$out.="<a id=\"c_edit{$i}\" href=\"javascript:void(0)\" onclick=\"edit('{$node['TARGET']}',$count,'$this->page')\" title=\"Edit\"><img src=\"images/admin/32/pencil.png\" alt=\"Editar\"  /></a><img id=\"loading{$i}\" src=\"images/admin/loading.gif\" style=\"display:none\" /><a style=\"display:none\" id=\"c_update{$i}\" href=\"javascript:void(0)\" onclick=\"update($i,$count)\" title=\"Save\"><img src=\"images/admin/accept.png\" alt=\"Aceptar\" /></a> <a style=\"display:none\" id=\"c_cancel{$i}\" href=\"javascript:void(0)\" onclick=\"cancel($i,$count)\" title=\"Cancel\"><img src=\"images/admin/cancel.png\" alt=\"Cancelar\" /></a>";
            }
            if(isset($this->layout[0]['DELETE']) && $this->layout[0]['DELETE']=="true"){
                $out.=" <a href=\"javascript:void(0)\" onclick=\"if(confirm('¿Estás Seguro?')==true){dojo_panel_central.set('href','{$_SERVER['REQUEST_URI']}&a=delete&{$this->layout[1]['TARGET']}[]=$id')}\" title=\"Delete\"><img src=\"images/admin/32/record-delete.png\" alt=\"Borrar\" /></a></td>";
            }
            $out.="</tr>\r\n";
        }
        $out.="</table>";
        return $out;
    }
}
?>