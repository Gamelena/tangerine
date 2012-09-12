<?php

/**
 * Auxiliar para Zwei_Admin_Components_TableDojo, CRUD en modo edición 
 *
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Components
 * @version $Id:$
 * @since 0.1
 *
 */


class Zwei_Admin_Components_Helpers_EditTableDojo extends Zwei_Admin_Controller
{
	/**
	 * 
	 * @var string
	 */
	private $_out = "";
	/**
	 * @var Zwei_Db_Table
	 */
	private $_model;
	/**
	 * 
	 * @var string
	 */
	private $_model_pk;
	/**
	 * 
	 * @param $page string
	 * @param $id string
	 * @param $view Zend_View_Interface|false 
	 */
	function __construct($page, $id=array(), $view=false)
	{
		parent::__construct($page, $id, $view);
		$form = new Zwei_Utils_Form();
		$this->getLayout();
		$modelclass = Zwei_Utils_String::toClassWord($this->layout[0]['TARGET'])."Model";
        $this->_model = new $modelclass();
        $primary = $this->_model->getPrimary() ? $this->_model->getPrimary() : 'id';
 		
		$count = count($this->layout);
		if (!isset($this->id)) $this->id = array();
		elseif (!is_array($this->id)) $this->id = array($this->id);
		if (!isset($this->layout[1]['VALUE'])) $this->layout[1]['VALUE'] = array("");
		//$vcount = count($this->layout[1]['VALUE']);

		$this->_out .= "
        <script type=\"text/javascript\">
            function showDialog(opc) {
                console.debug(opc);
                var formDlg = (opc=='edit') ? dijit.byId('formDialogoEditar') : dijit.byId('formDialogo');
                console.log('opcion usr:'+ opc);
                global_opc = opc;
                
                    if (opc == 'add') {\r
                    ";

	    $i = 0;
	    $clean_filtering_selects = '';  
		for ($j = 1; $j<$count; $j++) {
			$node = $this->layout[$j];
			$params = array();
			if (isset($node['ADD']) && ($node['ADD'] == "true" || $node['ADD'] == "disabled") && $node['TYPE'] != "pk_original") {
				$pfx = "_add";
				$this->_out .= "\t\t\t try{";
				if ($node['TYPE'] == "dojo_filtering_select" || $node['TYPE'] == 'dojo_yes_no') {
					$this->_out .= "\t\t\t\t dijit.byId('edit{$i}_{$pfx}{$j}').set('value',0);\r\n";
					// workaround bug dojo para limpiar selects despues de insertar
					// de otra forma notifica como opción NO valida una opción SI válida y confunde a usuario
					$clean_filtering_selects .= "\t\t\t\t dijit.byId('edit{$i}_{$pfx}{$j}').set('value', '');\r\n";
				} else if ($node['TYPE'] == "select") {
					$this->_out .= "\t\t\t\t selectValueSet('edit{$i}_{$pfx}{$j}', '0');\r\n";
				} else if (!empty($this->layout[0]['SEARCH_TABLE_TARGET']) && ($this->layout[0]['SEARCH_TABLE_TARGET'] == $node['TARGET'])){
					//esto debiera tener un type="hidden"
					$this->_out .= "\t\t\tdocument.getElementById('edit{$i}_{$pfx}{$j}').value = dijit.byId('search').get('value');\r\n";
				} else {
					$this->_out .= "\t\t\t\tdocument.getElementById('edit{$i}_{$pfx}{$j}').value = '';\r\n";
				}
				$this->_out .= "\t\t\t }catch(e){console.log(e)}";
			}
		}


		$this->_out .= "
                    formDlg.set('title','Agregar {$this->layout[0]['NAME']}');
                } else if(opc == 'edit') {
                    var items = main_grid.selection.getSelected();
                    if(items[0]==undefined){
                        alert('Debes seleccionar una fila');
                        return;
                    }
                    if (items[0].i != undefined) { //Bug Dojo?
                        items[0] = items[0].i;
                    }
                    console.debug(items[0]);\r\n"; 


	    $i=0; 
		for ($j=1; $j<$count; $j++) {
			$node = $this->layout[$j];
			$params = array();
			if (isset($node['EDIT']) && ($node['EDIT'] == "true" || $node['EDIT'] == "disabled" || $node['TYPE'] == "pk_original")) {
				$pfx = "";
				$this->_out .= "\t\t\t try{";
				if ($node['TYPE'] == "dojo_filtering_select") {
					$this->_out .= "\t\t\tdijit.byId('edit{$i}_{$pfx}{$j}').set('value',items[0].{$node['TARGET']});\r\n";
                } else if ($node['TYPE'] == "dojo_checkbox") {  
                	$defaultValue = isset($node['DEFAULT_VALUE']) ? $node['DEFAULT_VALUE'] : '1';
                	$defaultEmpty = isset($node['DEFAULT_EMPTY']) ? $node['DEFAULT_VALUE'] : '0';
                	//check checkbox
                    $this->_out .= "\t\t\tif (items[0].{$node['TARGET']} == '$defaultValue') { dijit.byId('edit{$i}_{$pfx}{$j}').set('value','$defaultValue'); dijit.byId('edit{$i}_{$pfx}{$j}').set('checked', true);
                        \t\t\t}\r\n";
                    //uncheck checkbox 
                    $this->_out .= "\t\t\telse {dijit.byId('edit{$i}_{$pfx}{$j}').set('value','$defaultEmpty'); dijit.byId('edit{$i}_{$pfx}{$j}').set('checked', false);
                        \t\t\t}\r\n"; 					
				} else if($node['TYPE'] == "select") {
					$this->_out .= "\t\t\tselectValueSet('edit{$i}_{$pfx}{$j}', items[0].{$node['TARGET']});\r\n";
                } else if(isset($node['EDIT_CUSTOM_DISPLAY'])) {
                    /**
                     * Si es un input que require lógica especial para el despliegue 
                     * agregar atributo <code>edit_custom_display="true"</code> en elemento XML
                     * y sobrescribir método editCustomDisplay($i, $pfx.$j) en Element personalizado 
                     */
					$sElement = "Zwei_Admin_Elements_".Zwei_Utils_String::toClassWord($node['TYPE']);
					$oElement = new $sElement($node['VISIBLE'], '', '', $node['TARGET'], '', $params);
					$this->_out .= $oElement->editCustomDisplay($i, $pfx.$j);
                } else if ($node['TYPE'] == "pk_original") {
					$this->_out .= "\t\t\tdocument.getElementById('edit{$i}_zwei_pk_original').value = items[0].{$node['TARGET']};\r\n";
				} else {
					$this->_out .= "\t\t\tdocument.getElementById('edit{$i}_{$pfx}{$j}').value = items[0].{$node['TARGET']};\r\n";
				}
				$this->_out .= "\t\t\t }catch(e){console.log(e)}";
			}
		}


		$this->_out.="\t\t\ttry{formDlg.set('title','Editar {$this->layout[0]['NAME']}');}catch(e){console.debug(e);}
                   }
                formDlg.show();
            }\r\n";

		if (isset($this->layout[0]['EDIT_ADDITIONAL_VALIDATION'])) {
			$additional_validation = $this->_model->getEditValidation();//usar en js var global_opc para discriminar entre 'edit' y add'
		} else {
			$additional_validation = '';
		}
        		
		
		$this->_out .= "
        function modify(model, items) {
            var resp = '';
            $additional_validation
            if (global_opc == 'add') {
                resp = insertar(model,items);
            } else if(global_opc == 'edit') {
                var items = main_grid.selection.getSelected();
                if (items[0].i != undefined) { //Bug Dojo?
                   items[0] = items[0].i;
                }
                
                var id = items[0].$primary;
                               
                resp = actualizar(model, items, id);
            }
        
            if (resp.message != '' && resp.message != null) {
                alert(resp.message);
            } else if(resp.state == 'UPDATE_OK') {
                alert('Datos Actualizados');
                cargarDatos(model);
            } else if(resp.state == 'ADD_OK') {
                alert('Datos Ingresados');
                cargarDatos(model);
            } else if(resp.state == 'UPDATE_FAIL') {
                alert('Ha ocurrido un error, o no ha modificado datos');
            } else if(resp.state == 'ADD_FAIL') {
                alert('Ha ocurrido un error, verifique datos o intente más tarde');
            }
                
                

            
            if (resp.todo == 'cargarArbolMenu') {
                cargarArbolMenu() ;
            }
            
        }
    
    
        function insertar(model, items) {
            var res = '';";

            $this->_out .= "dojo.xhrPost( {
                url: base_url+'objects',
                content: {
                ";
            $i=0;

            for ($j=1; $j<$count; $j++){
            	$node=$this->layout[$j];
            	$params=array();
            	if (isset($node['ADD']) && $node['ADD'] == "true" && $node['TYPE'] != "pk_original") {
            		$pfx='_add';
            		if ($node['TYPE']=='dojo_filtering_select'){
              			$this->_out.="\t\t\t\t'data[{$node['TARGET']}]' : dijit.byId('edit{$i}_{$pfx}{$j}').get('value'), \r\n";
            		} else {
             			$this->_out.="\t\t\t\t'data[{$node['TARGET']}]' : document.getElementById('edit{$i}_{$pfx}{$j}').value, \r\n";
            		}
            	}
            }


            $this->_out .= "
                            'action'      :'add',
                            'model'     : model,    
                            'format'    : 'json'
                },
                handleAs: 'json',
                sync: true,
                preventCache: true,
                timeout: 5000,
                load: function(respuesta){
        
                    console.debug(dojo.toJson(respuesta));
                    $clean_filtering_selects
                    res = respuesta;
                    return respuesta;
                },
                error:function(err){
                    //alert('Error en comunicacion de datos. error: '+err);
                    alert('Ha ocurrido un error, por favor reinicie sesión');
                    //window.location.href = base_url+'index/login';
                    return err;
                }
            });
        
        
            return res;
        
        }
    
        function actualizar(model, items, id) {
            var res = '';";

            $this->_out .= "
        
            dojo.xhrPost( {
                url: base_url+'objects',
                content: {
                ";


            for ($j=1; $j<$count; $j++) {
            	$node = $this->layout[$j];
            	$params = array();
            	if (isset($node['EDIT']) && $node['EDIT'] == "true") {
            		$pfx = '';
            		if ($node['TYPE'] == 'dojo_filtering_select' || $node['TYPE'] == 'dojo_yes_no'){
            			$this->_out.="     'data[{$node['TARGET']}]' : dijit.byId('edit{$i}_{$pfx}{$j}').get('value'), \r\n";
            		} else if ($node['TYPE'] == "pk_original") { 
            			$this->_out.="     'id[]' : document.getElementById('edit{$i}_zwei_pk_original').value, \r\n";       			
            		} else {
            			$this->_out.="     'data[{$node['TARGET']}]' : document.getElementById('edit{$i}_{$pfx}{$j}').value, \r\n";
            		}
            	}
            }


            $this->_out .= "
                    '$primary'        : id,
                    'action'    :'edit',
                    'model'     : model,
                    'format'    : 'json'    
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
                    alert('Ha ocurrido un error, por favor reinicie sesión');
                    //window.location.href = base_url+'index/login';
                    return err;
                }
            });
        
        
            return res;
        
        }";

            if (isset($this->layout[0]['CHANGE_PASSWORD']) && $this->layout[0]['CHANGE_PASSWORD']=="true"  && $this->_acl->isUserAllowed($this->page, 'EDIT')){

            	$this->_out .= "function showDialogPass() {
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
                    'id'        : id,
                    'action'    :'edit',
                    'model'     : model,
                    'format'    : 'json'    
                },
                handleAs: 'json',
                sync: true,
                preventCache: true,
                timeout: 5000,
                load: function(respuesta){
        
                    console.debug(dojo.toJson(respuesta));
                    resp = respuesta;
                    
                    if(resp.state == 'UPDATE_OK'){
                        alert('Contraseña Actualizada');
                    }else if(resp.state == 'UPDATE_FAIL'){
                        alert('Ha ocurrido un error, o no ha modificado datos');
                    }
                    
                    
                    return respuesta;
                },
                error:function(err){
                    alert('Vaya, ha ocurrido un error');
                    console.debug(err);
                }
            });
        
        
    
            }
        }";
            }
            $this->_out.="</script>
        ";      
	}


	public function display($mode='EDIT')
	{
		$form = new Zwei_Utils_Form();
		$out = $this->_out;
		$this->_out = '';
		$out .= "<table>\r\n";
		$count = count($this->layout);
		if (!isset($this->id)) $this->id = array();
		elseif (!is_array($this->id)) $this->id = array($this->id);
		if (!isset($this->layout[1]['VALUE'])) $this->layout[1]['VALUE'] = array("");
		$vcount = count($this->layout[1]['VALUE']);
	  
		for ($i=0; $i<$vcount; $i++) {
			for ($j=1; $j<$count; $j++) {
				$node = $this->layout[$j];
				$params = array();
				foreach ($node as $k=>$v) if($k!='VALUE') $params[$k] = $v;
				if (!isset($node['VALUE'][$i])) $node['VALUE'][$i] = "";
	    
				if (!empty($node['VALUE'][$i]) || isset($form->{$node['TARGET']}) && is_array($form->{$node['TARGET']})){
					$value = $node['VALUE'][$i];
				} else {
					$value = isset($form->{$node['TARGET']})?$form->{$node['TARGET']}:'';
				}
	    
				if ($node[$mode] == "true" || $node[$mode] == "disabled") {
					if ($node[$mode] == "disabled") $params["DISABLED"] = $mode;
					$ClassElement = "Zwei_Admin_Elements_".Zwei_Utils_String::toClassWord($node['TYPE']);
					$element = new $ClassElement($node['VISIBLE'],
					$node['EDIT'],
					@$node['NAME'],
					$node['TARGET'],
					$value,
					$params);
					 
					if($mode=='ADD')$pfx="_add";
					else $pfx="";

					if ($node['TYPE'] == 'pk_original') {
					    $out .= $element->edit($i, 'zwei_pk_original');
					} else if ($node['TYPE'] != 'hidden') {
						$out .= "<tr><td><label for=\"{$node['TARGET']}\">{$node['NAME']}</label></td><td>".$element->edit($i, $pfx.$j)."</td></tr>";
					} else {
						$out .= $element->edit($i,$pfx.$j);
					}
				}
			}
			$out.="
		        <tr>
		                    <td align=\"center\" colspan=\"2\">
		                    <input type=\"hidden\" name=\"action\" id=\"action\" value=\"\" />
		                        <button dojoType=\"dijit.form.Button\" type=\"submit\" onClick=\"if (global_opc=='add') { return dijit.byId('formDialogo').validate(); } else { return dijit.byId('formDialogoEditar').validate(); }\">
		                            Guardar
		                        </button>
		                        <button dojoType=\"dijit.form.Button\" type=\"button\" onClick=\"if (global_opc=='add') { dijit.byId('formDialogo').hide(); } else { dijit.byId('formDialogoEditar').hide(); }\">
		                            Cancelar
		                        </button>
		                    </td>
		                </tr>
		                ";
			$out.="<tr><td>&nbsp;</td></tr>";
		}
		$out.="</table>\r\n";

		return $out;
	}
}
