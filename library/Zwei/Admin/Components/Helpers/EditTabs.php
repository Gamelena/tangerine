<?php

/**
 * Tabla HTML de la edición de elementos del CMS
 *
 * Nota: Se usa la clase SimpleXMLElement en lugar de usar Zwei_Admin_Controller::layout
 * para trabajar en forma más simple con muchos niveles de nodos hijos,
 * tomar en cuenta que las etiquetas XML se trabajan en minúsculas 
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Components
 * @version $Id:$
 * @since 0.1
 */

class Zwei_Admin_Components_Helpers_EditTabs extends Zwei_Admin_Controller
{
    /**
     * 
     * @var integer
     */
    private $_version = 10;//actualizar para forzar update de javascript [TODO] hacer administrable
    
    /**
     * 
     * @var Zwei_Db_Table
     */

    /**
     * 
     * @var Zwei_Db_Table
     */
    private $_model;
    
    function display($mode = 'add')
    {
        $form = new Zwei_Utils_Form();

        if (preg_match('/(.*).php/', $this->page)) {
            $file = BASE_URL ."/components/".$this->page;
        } else {
            $file = COMPONENTS_ADMIN_PATH."/".$this->page;
        }
        
        $string_xml = file_get_contents($file);

        $Xml = new SimpleXMLElement($string_xml);
        $this->getLayout();
        $model = Zwei_Utils_String::toClassWord($this->layout[0]['TARGET'])."Model";
        $this->_model = new $model();
        $primary = $this->_model->getPrimary() ? $this->_model->getPrimary() : "id";

        $out = "<h2>{$this->layout[0]['NAME']}</h2>\r\n";
        $out .= "
        <script type=\"text/javascript\">
            function showtab(tab,area){
              var e=document.getElementsByTagName('div');
              for (i=0; i<e.length; i++){
                if(e[i].className=='settings_area')e[i].style.display='none';
              }
              var e=document.getElementsByTagName('a');
              for (i=0; i<e.length; i++){
                if(e[i].className=='settings_tab')e[i].style.backgroundColor='';
              }
              document.getElementById(area).style.display='block';
              tab.style.background='url(\"/dojotoolkit/dijit/themes/claro/images/commonHighlight.png\") #CFE5FA repeat-x';
            }
            global_opc = '$mode';
        </script>\r\n";     
      

      
        if (!empty($this->layout[0]['JS'])) $out.="<script type=\"text/javascript\" src=\"".BASE_URL."js/".$this->layout[0]['JS']."?version={$this->_version}\"></script>";
      
        $out .= "<div dojoType=\"dijit.form.Form\" id=\"tabForm\" jsId=\"tabForm\" name=\"taForm\" encType=\"multipart/form-data\" action=\"\" method=\"\" onsubmit=\"return false\">\r\n";
      
        if (!isset($this->id)) $this->id = array();
        elseif (!is_array($this->id)) $this->id = array($this->id);
      
        $tabs = $Xml->children();
      
        $h = 'style="background:#cccccc"';
        $i = 0;
        $k = 1;
        foreach($tabs as $tab) {
            $i++;
            $node_tab_mode = (string) $tab[$mode];
            if($node_tab_mode=="true"){
                $out .= '<a id="tab_ctrl'.$i.'" class="settings_tab" '.$h.' onclick="showtab(this,\'tab'.$i.'\')">'.$tab['name'].'</a>';
                $h = '';
            }

        }
        $out.="<div class=\"brclear\"></div><br />\r\n";
        $hidden='';

        $i=0;
        $xhr_insert_data='';
        $xhr_update_data='';
        $xhr_clean_data='';
        
        //Loop por cada pestaña
        foreach ($tabs as $j => $tab) {
            if ($mode=='edit' || $mode=='clone') {
                $ClassModel=Zwei_Utils_String::toClassWord($tab['target'])."Model";
                $Model=new $ClassModel();
                $select=$Model->select();
                 
                $my_id = ($Model->getPrimary()) ? $Model->getPrimary() : "id";
                
                if (!is_array($my_id) || count($my_id) == 1) {
                    if (count($my_id) == 1) {
                        $my_id = array_values($my_id);
                        $my_id = $my_id = $my_id[0];
                    }
                    
                    $select->where($select->getAdapter()->quoteInto("$my_id = ?", $this->id));
                } else {
                    foreach($my_id as $id){
                        $select->where($select->getAdapter()->quoteInto("$id = ?", $this->id));
                    }
                }
                Zwei_Utils_Debug::writeBySettings($select->__toString(), "query_log");
  
                $data = $Model->fetchAll($select);
                $overloadData = $Model->overloadData($data);
                if ($overloadData) $data = $overloadData;
            }
             
            $out.='<div id="tab'.($i+1).'" class="settings_area" '.$hidden.'>';
            $out.="\r\n";
            $out.="\t<table>\r\n";

             
            $j=0;
            //Loop por cada elemento dentro de una pestaña
            foreach ($tab->children() as $node) {
                $params = $this->getInputParams($node);
                if ($mode == "edit") {
                    $params['ID'] = $this->id[0];
                    if ($node['edit'] == "disabled") $params['DISABLED'] = true;
                    if ($node['edit'] == "readonly") $params['READONLY'] = true;
                }
                 
                //Zwei_Utils_Debug::write($params);
                $node_field = (string) $node['field'];
                $node_target = (string) $node['target'];
                $node_value = (string) $node['value'];
                $node_join = (string) $node['join'];
                $node_offset = (string) $node['offset'];
                //$node_tab_mode=(string)$node[$mode];
                 
                //if($node_tab_mode=="true"){
                if ($mode == 'edit' || $mode == 'clone') {
                    if(isset($node['join'])){
                        //[TODO] optimizar
                        $select2 = $Model->select();
          
                        $my_id = ($Model->getPrimary()) ? $Model->getPrimary() : "id";
                        if (is_array($my_id)) $my_id = array_values($my_id);
                         
                        if (!is_array($my_id) || count($my_id) == 1) {
                            if (is_array($my_id) == 1) $my_id = $my_id[0];
                            $select2->where($select2->getAdapter()->quoteInto("$my_id = ?", $this->id));
                        } else {
                            foreach ($my_id as $id) {
                                $select2->where($select2->getAdapter()->quoteInto("$id = ?", $this->id));
                            }
                        };
                         
                        $field = $node_join;
                        $data = $Model->fetchAll($select2->where("$field = ? ", $node_target));
                         
                    }
                     
                     
                    if (isset($node['offset'])) {
                        $select2 = $Model->select();
                         
                        $my_id = (method_exists($Model,"getPk")) ? $Model->getPk() : "id";
                        if (is_array($my_id)) $my_id = array_values($my_id);
                         
                        if (!is_array($my_id) || count($my_id) == 1){
                            if (is_array($my_id) == 1) $my_id = $my_id[0];                          
                            $select2->where($select2->getAdapter()->quoteInto("$my_id = ?", $this->id));
                        } else {
                            foreach($my_id as $id){
                                $select2->where($select2->getAdapter()->quoteInto("$id = ?", $this->id));
                            }
                        };
                         
                        $select2->limit(1, $node_offset);
                        $data = $Model->fetchAll($select2);
                    }
                     
                    $value = "";
                     
                    try {
                        if (isset($node['field']) && !isset($node['table'])) {
                            $value = $data[0][$node_field];
                        }else{
                            $value = @$data[0][$node_target];
                        }
                         
                    } catch (Zend_Db_Exception $e) {
                        if ($e->getCode() != 0) {
                            Zwei_Utils_Debug::write("field:$node_field|target:$node_target|".$e->getMessage()."|".$select->__toString()."|error:".$e->getCode());
                        }
                        //$value="";
                    }
                } else {
                    if (!empty($node['value'][$i]) && is_array($form->{$node['target']})) {
                        $value = $node['value'][$i];
                    } else {
                        $value = isset($form->{$node['target']}) ? $form->{$node['target']}:'';
                    }
                }
                 
                $ClassElement = "Zwei_Admin_Elements_".Zwei_Utils_String::toClassWord($node['type']);
                 
                $element = new $ClassElement(
                $node['visible'],
                $node['edit'],
                $node['name'],
                $node['target'],
                $value,
                $params
                );
                 
                $node_mode = (string) $node[$mode];
                $node_edit = (string) $node["edit"];
                $node_add = (string) $node["add"];
                //$node_clone=(string)$node["clone"];
                if ($mode == 'add') $pfx = "_add";
                else $pfx = "";
                 
                //Zwei_Utils_Debug::("{$node['target']} $node_mode $mode $node_edit $node_add");
                $name = str_replace('\n','<br/>',$node['name']);
                if ($node_mode == "true" || $node_mode == "disabled" || $node_mode == "readonly") {
                    $out .= "\t\t<tr id=\"row_{$node['target']}\"><th><label for=\"{$node['target']}\">{$name}</label></th><td>".$element->edit(0, $pfx.$k)."</td></tr>\r\n";
                     
                    if (!empty($node['target'])) {
                        if ($node['type']=='dojo_filtering_select' || $node['type']=='dojo_yes_no' || $node['type'] == 'dojo_checkbox') {
                            $xhr_insert_data.="\t\t\t\t'data[{$node['target']}]' : dijit.byId('edit0_{$pfx}{$k}').get('value'), \r\n";
                        } else {
                            $xhr_insert_data.="\t\t\t\t'data[{$node['target']}]' : document.getElementById('edit0_{$pfx}{$k}').value, \r\n";
                        }
                    }
                     
                } elseif($mode == "edit" && $node_edit == "false" && $node_add == "true") {
                    //En caso de que sea agregable pero no editable, se mostrara en el formulario, sin input
                    $out.="\t\t<tr><th><label>{$name}</label></th><td>$value</td></tr>\r\n";
                }
                $j++;
                $k++;
            }
            //}
            $out.="<tr><td>&nbsp;</td></tr>\n</table>\r\n";
            $out.="\t</div>\r\n";
            $i++;
        }
      
        $out.="
                <script type=\"dojo/method\" event=\"onSubmit\">
                    if (this.validate()) {
                        modify('{$this->layout[0]['TARGET']}', arguments[0], '$mode');
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
                            <button dojoType=\"dijit.form.Button\" id=\"tabs_btn_save\" type=\"submit\">
                                Guardar
                            </button>
                        </td>
                    </tr>
                    ";  
      
      
        $out.="</table>\r\n</div>\r\n";
        //exit();
        if (isset($this->layout[0]['EDIT_ADDITIONAL_VALIDATION'])) {
            $modelclass=Zwei_Utils_String::toClassWord($this->layout[0]['TARGET'])."Model";
            $Model=new $modelclass();
            $additional_validation= $Model->getEditValidation();//usar en js var global_opc para discriminar entre 'edit' y add'
        } else {
            $additional_validation='';
        }
        $out.="
        <script type=\"text/javascript\">
        showtab(document.getElementById('tab_ctrl1'),'tab1');
        function modify(model, items, mode) {
            var resp = '';
            $additional_validation
            if(mode == 'add' || mode == 'clone') {
                resp = insertar(model,items);
            } else if(mode == 'edit') {
                var items = main_grid.selection.getSelected();
                var id = items[0].id;
                resp = actualizar(model, items, id);
            }
               
            if(resp.message != '' && resp.message != null){
                alert(resp.message);
            }else if(resp.state == 'UPDATE_OK'){
                alert('Datos Actualizados');
                cargarPanelCentral('index/components?p=$this->page');
            }else if(resp.state == 'ADD_OK'){
                alert('Datos Ingresados');
                cargarPanelCentral('index/components?p=$this->page');
            }else if(resp.state == 'UPDATE_FAIL'){
                alert('Ha ocurrido un error, o no ha modificado datos');
            }else if(resp.state == 'ADD_FAIL'){
                alert('Ha ocurrido un error, verifique datos o intente más tarde');
            }
        }
    
    
        function insertar(model, items) {
            var res = '';
            dojo.xhrPost( {
                url: base_url+'objects',
                content: {
                $xhr_insert_data
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
                    res = respuesta;
        
                    return respuesta;
                },
                error:function(err){
                    alert('Error en comunicacion de datos. error: '+err);
                    return err;
                }
            });
            console.log(res);
            return res;
        }
    
        function actualizar(model, items, id) {
        
            var res = '';
            dojo.xhrPost( {
                url: base_url+'objects',
                content: {
                $xhr_insert_data
                    'id'        : id,
                    'action'    :'edit',
                    'model'     : model,
                    'format'    : 'json'    
                },
                handleAs: 'json',
                sync: true,
                preventCache: true,
                timeout: 5000,
                load: function(respuesta) {
        
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
        ";
                 
            $out.="</script>
        ";              
                 
            if (!empty($this->layout[0]['JS'])) {
                $functionInit=str_replace('.js','', $this->layout[0]['JS']).'Init';
                $out.="
                <script type=\"text/javascript\">
                try {
                   $functionInit();
                } catch (e) {
                    console.log('Función $functionInit no declarada');    
                }
                </script>
            ";
                }
                 
        return $out;
    }
}
