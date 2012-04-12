<?php
/**
 * Componente para mantener variables configuración general
 *
 * Las variables deben ser previamente creadas en tabla,
 * por defecto se usa modelo SettingModel el cual puede ser cambiado en atributo opcional "target".<br/>
 * Ejemplo:
 * <code>
 * <section name="Configuraci&amp;oacute;n" type="settings" target="settings" target="app_settings" />
 * </code>
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Components
 * @version $Id:$
 * @since 0.1
 *
 */
class Zwei_Admin_Components_Settings{
	public $page;

	function __construct($page){
		$this->page=$page;
	}

	function display(){
		$form=new Zwei_Utils_Form();
		$settings=new SettingsModel();
		$out = "<h2>Configuraci&oacute;n</h2>\r\n";
		 
		if(isset($form->save)){
			foreach ($form->id as $i=>$v){
				$value=isset($form->value[$i])?$form->value[$i]:"";
				$where=$settings->getAdapter()->quoteInto('id = ?', $v);
				$data=array('value'=>$value);
				$settings->update($data, $where);
			}
		}

		if(isset($form->f)&&isset($form->v)){
			//echo $form->f;
			if(class_exists($form->f)){
				$f=new $form->f;
				$f->setValue($form->v);
				return $f->run();
			}
		}

		$out.='<script type="text/javascript">
    function showtab(tab,area){
      var e=document.getElementsByTagName("div");
      for (i=0; i<e.length; i++){
        if(e[i].className=="settings_area")e[i].style.display="none";
      }
      var e=document.getElementsByTagName("a");
      for (i=0; i<e.length; i++){
        if(e[i].className=="settings_tab")e[i].style.backgroundColor="";
      }
      document.getElementById(area).style.display="block";
      tab.style.backgroundColor="#cccccc";
    }
    </script>
    ';
		 

		$out.="<form action=\"/index/components?p=$this->page\" method=\"post\">\r\n";

		$groupies=$settings->loadGroups();
		$groups=array();
		$h='style="background:#cccccc"';
		$i=0;
		foreach($groupies as $group){
			$g=!empty($group['group'])?$group['group']:'General';
			$groups[]=$group['group'];
			$out.='<a class="settings_tab" '.$h.' onclick="showtab(this,\'tab'.++$i.'\')">'.$g.'</a>';
			$h='';
		}
		$out.='<div class="brclear"></div><br />';
		$settings=new SettingsModel();
		$hidden='';
		$i=0;

		$table=$settings->getName();

		foreach($groups as $j=>$group){
			$select=
			$settings->select()
			->where($settings->getAdapter()->quoteInto($table.'.group = ?', $group))
			->order('ord ASC');
				
			Zwei_Utils_Debug::write($select->__toString());
			$groupies=$settings->fetchAll($select);
			$out.='<div id="tab'.($j+1).'" class="settings_area" '.$hidden.'>';

			foreach($groupies as $row){
				$out.="<input type=\"hidden\" name=\"id[]\" value=\"$row[id]\" />\r\n";
				$out.="<b>$row[id]:</b> ";
				$ClassElement = "Zwei_Admin_Elements_".Zwei_Utils_String::toClassWord($row['type']);
				$e=new $ClassElement(true,true,"","value",$row['value']);

				if(!empty($row['enum'])){

					$out.=$this->_select("value[$i]",$row['enum'],$row['value']);

				}else{

					$out.=$e->edit($i,0);

				}

				if(!empty($row['function'])&&class_exists($row['function'])){

					$f=new $row['function'];
					$f->setId($i);
					$out.=$f->display();
				}
				$out.="<br />".nl2br($row['description']);
				$out.="\r\n<br /><br />\r\n";
				$i++;
			}
			$hidden='style="display:none"';
			$out.='</div>';
		}
		$out.="<input type=\"hidden\" name=\"save\" value=\"save\" />\r\n";
		$out.="<input type=\"submit\" value=\"Save\" /></form>";
		return $out;
	}

	function _select($name, $values, $value){
		$r='<select name="'.$name.'">';
		foreach(explode(',',$values) as $v){
			if($value==$v){
				$r.='<option value="'.$v.'" selected="selected">'.$v.'</option>';
			}else{
				$r.='<option value="'.$v.'">'.$v.'</option>';
			}
		}
		$r.='</select>';
		return $r;
	}
}
