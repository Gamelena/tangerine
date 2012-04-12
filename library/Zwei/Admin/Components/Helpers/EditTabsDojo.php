<?php

/**
 * Tabla HTML de la edición de elementos del CMS [INCOMPLETA, NO PROBADO A FONDO]
 * Tabla HTML de la edición de elementos del CMS, con pestañas, esto se determina por los nodos "tab_dojo"
 *
 * Nota: Acá se debieron romper las reglas en la forma de procesar el XML,
 * se usa la clase SimpleXMLElement en lugar de usar Zwei_Admin_Controller::layout
 * para trabajar en forma más eficiente con muchos niveles de nodos hijos
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Components
 * @version $Id:$
 * @since 0.1
 *
 */


class Zwei_Admin_Components_Helpers_EditTabsDojo extends Zwei_Admin_Controller
{
	function display($mode='add'){
		$form=new Zwei_Utils_Form();

		$string_xml=file_get_contents(COMPONENTS_ADMIN_PATH.'/'.$this->page.'.xml');
		$Xml=new SimpleXMLElement($string_xml);

		$out="<div dojoType=\"dijit.layout.TabContainer\" style=\"width: 100%; height: 100%;\" tabStrip=\"true\">\r\n";

		if(!isset($this->id))$this->id=array();
		elseif(!is_array($this->id))$this->id=array($this->id);

		//$ClassModel=Zwei_Utils_String::toClassWord($Xml->attributes()->target)."Model";
		//$Model=new $ClassModel();

		$this->getLayout();

		//for($i=0; $i<$vcount; $i++){
		$tabs=$Xml->children();
		$i=0;
		foreach ($tabs as $tab) {
	  //if(in_array($this->layout[1]['VALUE'][$i],$this->id)||$vcount==1){
			if($mode=='edit'){
				$ClassModel=Zwei_Utils_String::toClassWord($tab['target'])."Model";
				$Model=new $ClassModel();
				$select=$Model->select();
				$my_id=(method_exists($Model,"getPk")) ? $Model->getPk() : "id";
				$select->where($select->getAdapter()->quoteInto("$my_id = ?", $this->id));
				//Zwei_Utils_Debug::write($select->__toString());
				$data=$Model->fetchAll($select);
			}
			//onClick=\"return dijit.byId('tab_form$i').validate();\"

			$selected=($i==0) ? ' selected="true"':'';
			$out.="\t<div dojoType=\"dijit.layout.ContentPane\" title=\"{$tab['name']}\" $selected>\r\n";
			$out.="<div dojoType=\"dijit.form.Form\" id=\"tab_form$i\" jsId=\"tab_form$i\" encType=\"multipart/form-data\" action=\"\" method=\"\" onsubmit=\"return false\">\r\n";
			$out.="<table>\r\n";
			if(true){

				$j=0;
				foreach($tab->children() as $node){
					//var_dump(node);
					//$node=$this->layout[$j];
					$params=array();
					 
					if($mode=='edit'){
						$value=$data[0][$node['target']->__toString()];
					}else{
						if(!empty($node['value'][$i]) && is_array($form->{$node['target']})){
							$value=$node['value'][$i];
						}else{
							$value=isset($form->{$node['target']})?$form->{$node['target']}:'';
						}
					}

					$ClassElement="Zwei_Admin_Elements_".Zwei_Utils_String::toClassWord($node['type']);
					 
					$element=new $ClassElement($node['visible'],$node['edit'],$node['name'],$node['target'],$value,$params);

					$name=str_replace("\n","<br/>",$node['name']);
					Zwei_Utils_Debug::write($node);
					if($node[$mode]){
						if($mode=='add')$pfx="_add";
						else $pfx="";

						$out.="<tr><td><label for=\"{$node['target']}\">{$name}</label></td><td>".$element->edit(0,$pfx.$j)."</td></tr>\r\n";
					}elseif($mode=="edit" && !$node[$mode]){
						if($mode=='add')$pfx="_add";
						else $pfx="";
						$out.="<tr><td><label>{$name}</label><td>$value</td></tr>\r\n";
					}
					$j++;
					//*/
		  }

		  $out.="
			<script type=\"dojo/method\" event=\"onSubmit\">
                if (this.validate()) {
                	global_opc='$mode';
	                modify('{$this->layout[0]['TARGET']}', arguments[0]);
                    return false;
                } else {
                	alert('Por favor corrija los campos marcados.');
                    return false;
                }
                return true;
            </script>\r\n";


		  $out.="
		  		<tr>
                    <td align=\"center\" colspan=\"2\">
                    	<input type=\"hidden\" name=\"action\" id=\"action\" value=\"\" />
                        <button dojoType=\"dijit.form.Button\" type=\"submit\">
                            Guardar
                        </button>
                    </td>
                </tr>
                ";
		  $out.="<tr><td>&nbsp;</td></tr>\n</table>\n</div>\r\n";
			}


			$out.="\t</div>\r\n";
			$i++;
		}
		//$out.="</table>\r\n";
		//exit();

		$out.="
	function modify(model, items) {
	    var resp = '';
	
	    if(global_opc == 'add') {
	        resp = insertar(model,items);
	    } else if(global_opc == 'edit') {
	        var items = main_grid.selection.getSelected();
	        var id = items[0].id;
	        resp = actualizar(model, items, id);
	    }
	
	
	    
	    if(resp.ESTADO == 'ERR_USUARIO_EXISTE')
	        alert('ERROR: El usuario ya existe');
	
	    cargarDatos(model);
	    //formDlg = dijit.byId('formDialogEditar');
	    //formDlg.hide();
	}


	function insertar(model, items) {
	    var res = '';
	    dojo.xhrPost( {
	        url: base_url+'objects',
	        content: {
	        ";

		for($i=0; $i<$vcount; $i++){
			if(in_array($this->layout[1]['value'][$i],$this->id)||$vcount==1){
				for($j=1; $j<$count; $j++){
					$node=$this->layout[$j];
					$params=array();
					if($node[$mode]){
						if($mode=='ADD')$pfx="_add";
						else $pfx="";
							
						if($node['type']=='dojo_filtering_select'){
							$out.="\t\t\t\t'data[{$node['target']}]' : dijit.byId('{$node['target']}[0]').get('value'), \r\n";
						}else{
							$out.="\t\t\t\t'data[{$node['target']}]' : document.getElementById('{$node['target']}[0]').value, \r\n";
						}
					}
				}
			}
		}

		$out.="
						'action'      :'add',
						'model'		: model,	
						'format'	: 'json'
	        },
	        handleAs: 'json',
	        sync: true,
	        preventCache: true,
	        timeout: 5000,
	        load: function(respuesta){
	
	            console.debug(dojo.toJson(respuesta));
	            res = respuesta;
	
	            return respuesta;
	        },
	        error:function(err){
	            alert('Error en comunicacion de datos. error: '+err);
	            return err;
	        }
	    });
	
	
	    return res;
	
	}

	function actualizar(model, items, id) {
	
	    var res = '';
	
	    dojo.xhrPost( {
	        url: base_url+'objects',
	        content: {
	        ";


		for($i=0; $i<$vcount; $i++){
			if(in_array($this->layout[1]['value'][$i],$this->id)||$vcount==1){
				for($j=1; $j<$count; $j++){
					$node=$this->layout[$j];
					$params=array();
					if($node[$mode]){
						//$out.="		'{$node['target']}' : items[0].{$node['target']}, \r\n";
						if($node['type']=='dojo_filtering_select' || $node['type']=='dojo_yes_no'){
							$out.="		'data[{$node['target']}]' : dijit.byId('{$node['target']}[0]').get('value'), \r\n";
						}else{
							$out.="		'data[{$node['target']}]' : document.getElementById('{$node['target']}[0]').value, \r\n";
						}
					}
				}
			}
		}
		 
		$out .= "
				'id'		: id,
				'action'    :'edit',
				'model'		: model,
				'format'	: 'json'	
	        },
	        handleAs: 'json',
	        sync: true,
	        preventCache: true,
	        timeout: 5000,
	        load: function(respuesta){
	
	            console.debug(dojo.toJson(respuesta));
	            res = respuesta;
	
	            return respuesta;
	        },
	        error:function(err){
	            alert('Error en comunicacion de datos. error: '+err);
	            return err;
	        }
	    });
	
	
	    return res;
	
	}
	
	
	
	
	function eliminar(model) {
	
	    console.log('En eliminarUsuario');
	
	    var items = main_grid.selection.getSelected();
	    console.debug(items);
	    console.debug(items[0].id);
	    if(confirm('Desea eliminar el registro seleccionado?')) {
	        eliminarRegistro(model, items[0].id);
	        main_grid.removeSelectedRows();
	    }
	
	}
	
	function eliminarRegistro(model, id) {
	
	    var res = '';
	
	    dojo.xhrPost( {
	        url: base_url+'objects',
	        content: {
	            'action'          :'delete',
	            'id'  : id,
	            'model': model,
	           	'format': 'json' 
	        },
	        handleAs: 'json',
	        sync: true,
	        preventCache: true,
	        timeout: 5000,
	        load: function(respuesta){
	
	            console.debug(dojo.toJson(respuesta));
	            res = respuesta;
	
	            return respuesta;
	        },
	        error:function(err){
	            alert('Error en comunicacion de datos. error: '+err);
	            return err;
	        }
	    });
	
	
	    return res;
	
	}
	
	function showDialogPass() {
		    var formDlg = dijit.byId('formPassword');
			formDlg.set('title','Cambio de Contraseña');
	        var items = main_grid.selection.getSelected();
		    if(items[0]==undefined){
		    	alert('Por favor selecciona la fila con tus datos');
		        return;
		    }
		    formDlg.show();
	}\r\n
	
	function changePassword(model, items){
		var strNvoPass  = dijit.byId(\"password[0]\").get(\"value\");
    	var strNvoPassConf  = dijit.byId(\"password_confirm[0]\").get(\"value\");
	
    	var items = main_grid.selection.getSelected();
	    var id = items[0].id;
    	
	    if(strNvoPass != strNvoPassConf) {
	        alert(\"La confirmacion de la nueva contrasena es erronea\");
	        return false;
	    }else{
	    
	     dojo.xhrPost( {
	        url: base_url+'objects',
	        content: {
	        	'data[password]':hex_md5(document.getElementById('password[0]').value),\r\n
				'id'		: id,
				'action'    :'edit',
				'model'		: model,
				'format'	: 'json'	
	        },
	        handleAs: 'json',
	        sync: true,
	        preventCache: true,
	        timeout: 5000,
	        load: function(respuesta){
	
	            console.debug(dojo.toJson(respuesta));
	            res = respuesta;
	
	            return respuesta;
	        },
	        error:function(err){
	            alert('Error en comunicacion de datos. error: '+err);
	            return err;
	        }
	    });
	
	

	    }
	}
	showTabsContent('$form->action');
    </script>
    ";
		return $out;
	}
}
?>