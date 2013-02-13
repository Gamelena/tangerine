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
 * @version $Id$
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
    private $_model;
    
    function display($mode = 'add')
    {
        $form = new Zwei_Utils_Form();

        $file = Zwei_Admin_XML::getFullPath($this->page);
        
        //$string_xml = file_get_contents($file);

        $Xml = new Zwei_Admin_Xml($file, 0, 1);

        $model = Zwei_Utils_String::toClassWord($Xml->getAttribute("target"))."Model";
        $this->_model = new $model();

        $out = '';        
        $modeDom = ($mode == 'add' || $mode == 'clone') ? 'add' : 'edit';
        $domPrefix = (isset($this->_mainPane) && $this->_mainPane == 'dijitTabs') ? Zwei_Utils_String::toVarWord($form->p) : '';
      
        if ($Xml->getAttribute('js')) $out.="<script type=\"text/javascript\" src=\"".BASE_URL."js/".$Xml->getAttribute('js')."?version={$this->_version}\"></script>";
      
        $out .= "<div dojoType=\"dijit.form.Form\" id=\"{$domPrefix}tabForm{$modeDom}\" class=\"tabForm$modeDom\" jsId=\"{$domPrefix}tabForm\" name=\"{$domPrefix}tabForm$modeDom\" encType=\"multipart/form-data\" action=\"\" method=\"\" onsubmit=\"return false\">\r\n";
      
        
        if (!isset($this->id)) $this->id = array();
        elseif (!is_array($this->id)) $this->id = array($this->id);
      
        $tabs = ($Xml->existsChildren("tab")) ? $Xml->children() : array(null);
      
        $h = "style=\"background:#cccccc\"";
        $i = 0;
        $k = 1;
        foreach($tabs as $tab) {
            $i++;
            $node_tab_mode = (string) $tab[$mode];
            if ($node_tab_mode == "true") {
                $out .= "<a id=\"tab{$mode}_ctrl$i\" class=\"settings_tab\" $h onclick=\"showtab(this.id, '{$domPrefix}tab{$modeDom}{$i}')\">{$tab['name']}</a>";
                $h = '';
            }

        }
        $out .= "<div class=\"brclear\"></div><br />\r\n";
        $hidden = '';

        $i=0;
        $xhr_insert_data='';//[TODO] BORRAR variable backward compatibility
        
        //Loop por cada pestaña
        foreach ($tabs as $j => $tab) {
            if ($mode=='edit' || $mode=='clone') {
                $ClassModel = Zwei_Utils_String::toClassWord($tab['target'])."Model";
                $this->_model = new $ClassModel();
                $select = $this->_model->select();

                $my_id = ($this->_model->getPrimary()) ? $this->_model->getPrimary() : $this->_model->getName().".id";
                if (!is_array($my_id) || count($my_id) == 1) {
                    if (is_array($my_id) && count($my_id) == 1) {
                        $my_id = array_values($my_id);
                        $my_id = $my_id[0];
                    }
                    $select->where($select->getAdapter()->quoteInto("$my_id = ?", $this->id));
                } else {
                    foreach($my_id as $id){
                        $select->where($select->getAdapter()->quoteInto("$id = ?", $this->id));
                    }
                }
                
                Zwei_Utils_Debug::writeBySettings($select->__toString(), "query_log");
  
                $data = $this->_model->fetchAll($select);
                $overloadData = $this->_model->overloadDataTabs($data);
                if ($overloadData) { $data = $overloadData; }
            }
             
            $out.='<div id="'.$domPrefix.'tab'.$modeDom.($i+1).'" class="settings_area" '.$hidden.'>';
            $out.="\r\n";
            $out.="\t<table>\r\n";

             
            $j=0;
            //Loop por cada elemento dentro de una pestaña
            $children = $tab != null ? $tab->children() : $Xml->children();
            
            foreach ($children as $node) {
                $params = $this->getInputParams($node);
                if ($mode == "edit") {
                    $params['ID'] = $this->id[0];
                    if ($node['edit'] == "disabled") $params['DISABLED'] = true;
                    if ($node['edit'] == "readonly") $params['READONLY'] = true;
                }
                 
                //Zwei_Utils_Debug::write($params);
                $node_field = (string) $node['field'];
                $node_target = (string) $node['target'];
                //$node_value = (string) $node['value'];
                $node_join = (string) $node['join'];
                $node_offset = (string) $node['offset'];
                //$node_tab_mode=(string)$node[$mode];
                 
                if ($mode == 'edit' || $mode == 'clone') {
                    if(isset($node['join'])){
                        //[TODO] optimizar
                        $select2 = $this->_model->select();
          
                        $my_id = ($this->_model->getPrimary()) ? $this->_model->getPrimary() : $this->_model->getName().".id";
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
                        $data = $this->_model->fetchAll($select2->where("$field = ? ", $node_target));
                         
                    }
                     
                    /**
                     * Parámetro xml offset="$int", para seleccionar registro por registro
                     * en caso de que tenga que buscarlos en una tabla auxiliar 1:1. 
                     * Normalmente son unos cuantos registros con $int correlativo partiendo de 0
                     * en teoría con una correcta normalización de tablas esto no sería necesario de usar.
                     */
                    
                    if (isset($node['offset'])) {
                        $select2 = $this->_model->select();
                         
                        $my_id = ($this->_model->getPrimary()) ? $this->_model->getPrimary() : $this->_model->getName()."id";
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
                        $data = $this->_model->fetchAll($select2);
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
                //$node_clone = (string)$node["clone"];
                if ($mode == 'add' || $mode == 'clone') $pfx = "_add";
                else $pfx = "";
                 
                //Zwei_Utils_Debug::("{$node['target']} $node_mode $mode $node_edit $node_add");
                $name = str_replace('\n','<br/>',$node['name']);
                if ($node_mode == "true" || $node_mode == "disabled" || $node_mode == "readonly") {
                    $out .= "\t\t<tr id=\"row_{$node['target']}\"><th><label for=\"{$node['target']}\">{$name}</label></th>";
                    $out .= "<td>".$element->edit('0', $domPrefix.$pfx.$k)."</td></tr>\r\n";
                    //[TODO] BORRAR var $xhr_insert_data deprecated, existe para backward compatibility 
                    if (!empty($node['target'])) {
                        if ($node['type']=='dojo_filtering_select' || $node['type']=='dojo_yes_no' || $node['type'] == 'dojo_checkbox') {
                            $xhr_insert_data.="\t\t\t\t'data[{$node['target']}]' : dijit.byId('edit0_{$domPrefix}{$pfx}{$k}').get('value'), \r\n";
                        } else {
                            $xhr_insert_data.="\t\t\t\t'data[{$node['target']}]' : document.getElementById('edit0_{$domPrefix}{$pfx}{$k}').value, \r\n";
                        }
                    }
                    //fin BORRAR
                } elseif($mode == "edit" && $node_edit == "false" && $node_add == "true") {
                    //En caso de que sea agregable pero no editable, se mostrara en el formulario, sin input
                    $out.="\t\t<tr><th><label>{$name}</label></th><td>$value</td></tr>\r\n";
                }
                $j++;
                $k++;
            }

            $out.="<tr><td>&nbsp;</td></tr>\n</table>\r\n";
            $out.="\t</div>\r\n";
            $i++;
        }
      
        $out.="
                <script type=\"dojo/method\" event=\"onSubmit\">
                    if (this.validate()) {
                        try {
                            {$domPrefix}modify('{$Xml->getAttribute('target')}', arguments[0], '$mode');
                        } catch (e) {
                            console.debug(e)    
                        }
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
                            <button dojoType=\"dijit.form.Button\" id=\"{$domPrefix}tabs_btn_save$modeDom\" type=\"submit\">
                                Guardar
                            </button>
                        </td>
                    </tr>
                    ";  
      
      
        $out.="</table>\r\n</div>\r\n";

                 
        return $out;
    }
}
