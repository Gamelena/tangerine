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
     * @fixme la salida de $this->_out debe ser eliminada y usar en su lugar la salida Zwei_Admin_Components_TableDojo::getJsCrud(...), hay código duplicado
     */
    function __construct($page, $id=array(), $view=false)
    {
        parent::__construct($page, $id, $view);
        $form = new Zwei_Utils_Form();
        $this->getLayout();
        $modelclass = Zwei_Utils_String::toClassWord($this->layout[0]['TARGET'])."Model";
        $this->_model = new $modelclass();
        $domPrefix = (isset($this->_mainPane) && $this->_mainPane == 'dijitTabs') ? Zwei_Utils_String::toVarWord($form->p) : '';
        $primary = $this->_model->getPrimary() ? $this->_model->getPrimary() : 'id';
        if (is_array($primary)) $primary = implode(",", $primary);
        $count = count($this->layout);
        if (isset($form->{$primary})) $this->id = $form->{$primary};
        
        if (!isset($this->id)) $this->id = array();
        elseif (!is_array($this->id)) $this->id = array($this->id);
        if (!isset($this->layout[1]['VALUE'])) $this->layout[1]['VALUE'] = array("");
        //$vcount = count($this->layout[1]['VALUE']);

        $this->_out .= "
        <script type=\"text/javascript\">
            function {$domPrefix}showDialog(opc) {
                console.debug(opc);
                var formDlg = (opc=='edit') ? dijit.byId('{$domPrefix}formDialogoEditar') : dijit.byId('{$domPrefix}formDialogo');
                console.log('opcion usr:'+ opc);
                global_opc = opc;
                    if (opc == 'add') {\r
                    ";

        $i = 0;
        $clean_filtering_selects = '';  
        for ($j = 1; $j<$count; $j++) {
            $node = $this->layout[$j];
            $params = array();
            if (isset($node['ADD']) && ($node['ADD'] == "true" || $node['ADD'] == "disabled" || $node['ADD'] == "readonly") && $node['TYPE'] != "pk_original") {
                $pfx = "_add";
                $this->_out .= "\t\t\t try{";
                if ($node['TYPE'] == "dojo_filtering_select" || $node['TYPE'] == 'dojo_yes_no') {
                    $this->_out .= "\t\t\t\t dijit.byId('edit{$i}_{$domPrefix}{$pfx}{$j}').set('value',0);\r\n";
                    // workaround bug dojo para limpiar selects despues de insertar
                    // de otra forma notifica como opción NO valida una opción SI válida y confunde a usuario
                    $clean_filtering_selects .= "\t\t\t\t dijit.byId('edit{$i}_{$domPrefix}{$pfx}{$j}').set('value', '');\r\n";
                } else if ($node['TYPE'] == "dojo_checkbox") {  
                    $defaultValue = isset($node['DEFAULT_VALUE']) ? $node['DEFAULT_VALUE'] : '1';
                    $defaultEmpty = isset($node['DEFAULT_EMPTY']) ? $node['DEFAULT_VALUE'] : '0';
                    //check checkbox
                    if (isset($node['CHECKED']) && $node['CHECKED'] == 'true') {
                        $this->_out .= "\t\t\t\t dijit.byId('edit{$i}_{$domPrefix}{$pfx}{$j}').set('value','$defaultValue'); dijit.byId('edit{$i}_{$domPrefix}{$pfx}{$j}').set('checked', true);\r\n";
                    } else {    
                    //uncheck checkbox 
                        $this->_out .= "\t\t\t\t dijit.byId('edit{$i}_{$domPrefix}{$pfx}{$j}').set('value','$defaultEmpty'); dijit.byId('edit{$i}_{$domPrefix}{$pfx}{$j}').set('checked', false);\r\n";
                    }
                } else if ($node['TYPE'] == "select") {
                    $this->_out .= "\t\t\t\t selectValueSet('edit{$i}_{$domPrefix}{$pfx}{$j}', '0');\r\n";
                } else if (!empty($this->layout[0]['SEARCH_TABLE_TARGET']) && ($this->layout[0]['SEARCH_TABLE_TARGET'] == $node['TARGET'])){
                    //esto debiera tener un type="hidden"
                    $this->_out .= "\t\t\tdocument.getElementById('edit{$i}_{$domPrefix}{$pfx}{$j}').value = dijit.byId('{$domPrefix}search').get('value');\r\n";
                } else {
                    $this->_out .= "\t\t\t\tdocument.getElementById('edit{$i}_{$domPrefix}{$pfx}{$j}').value = '';\r\n";
                }
                $this->_out .= "\t\t\t }catch(e){console.log(e)}";
            }
        }


        $this->_out .= "
                    formDlg.set('title','Agregar {$this->layout[0]['NAME']}');
                } else if(opc == 'edit') {
                    var items = dijit.byId('{$domPrefix}main_grid').selection.getSelected();
                    if(items[0]==undefined){
                        alert('Debes seleccionar una fila');
                        return;
                    }
                    if (items[0].i != undefined && items[0].r._items != undefined) { //Bug Dojo?
                        items[0] = items[0].i;
                    }
                    console.debug(items[0]);\r\n"; 


        $i=0; 
        for ($j=1; $j<$count; $j++) {
            $node = $this->layout[$j];
            $params = array();
            if (isset($node['EDIT']) && ($node['EDIT'] == "true" || $node['EDIT'] == "disabled" || $node['EDIT'] == "readonly" || $node['TYPE'] == "pk_original")) {
                $pfx = "";
                $this->_out .= "\t\t\t try{";
                if ($node['TYPE'] == "dojo_filtering_select") {
                    $this->_out .= "\t\t\tdijit.byId('edit{$i}_{$domPrefix}{$pfx}{$j}').set('value',items[0].{$node['TARGET']});\r\n";
                } else if ($node['TYPE'] == "dojo_checkbox") {  
                    $defaultValue = isset($node['DEFAULT_VALUE']) ? $node['DEFAULT_VALUE'] : '1';
                    $defaultEmpty = isset($node['DEFAULT_EMPTY']) ? $node['DEFAULT_VALUE'] : '0';
                    //check checkbox
                    $this->_out .= "\t\t\tif (items[0].{$node['TARGET']} == '$defaultValue') { dijit.byId('edit{$i}_{$domPrefix}{$pfx}{$j}').set('value','$defaultValue'); dijit.byId('edit{$i}_{$domPrefix}{$pfx}{$j}').set('checked', true);
                        \t\t\t}\r\n";
                    //uncheck checkbox 
                    $this->_out .= "\t\t\telse {dijit.byId('edit{$i}_{$domPrefix}{$pfx}{$j}').set('value','$defaultEmpty'); dijit.byId('edit{$i}_{$domPrefix}{$pfx}{$j}').set('checked', false);
                        \t\t\t}\r\n";                     
                } else if($node['TYPE'] == "select") {
                    $this->_out .= "\t\t\tselectValueSet('edit{$i}_{$domPrefix}{$pfx}{$j}', items[0].{$node['TARGET']});\r\n";
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
                    $this->_out .= "\t\t\tdocument.getElementById('edit{$i}_{$domPrefix}zwei_pk_original').value = items[0].{$node['TARGET']};\r\n";
                } else {
                    $this->_out .= "\t\t\tdocument.getElementById('edit{$i}_{$domPrefix}{$pfx}{$j}').value = items[0].{$node['TARGET']};\r\n";
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
        function {$domPrefix}modify(model, items) {
            var resp = '';
            $additional_validation
            if (global_opc == 'add') {
                resp = {$domPrefix}insertar(model,items);
            } else if(global_opc == 'edit') {
                var items = dijit.byId('{$domPrefix}main_grid').selection.getSelected();
                if (items[0].i != undefined && items[0].r._items != undefined) { //Bug Dojo?
                   items[0] = items[0].i;
                }
                var id = items[0].$primary;
                resp = {$domPrefix}actualizar(model, items, id);
            }
        
            if (resp.message != '' && resp.message != null) {
                alert(resp.message);
            } else if(resp.state == 'UPDATE_OK') {
                alert('Datos Actualizados');
                
                cargarDatos(model, false, false, false, false, 'json', false, '$domPrefix');
                dijit.byId('{$domPrefix}formDialogoEditar').hide();
            } else if(resp.state == 'ADD_OK') {
                alert('Datos Ingresados');
                cargarDatos(model, false, false, false, false, 'json', false, '$domPrefix');
                dijit.byId('{$domPrefix}formDialogo').hide();
            } else if(resp.state == 'UPDATE_FAIL') {
                alert('Ha ocurrido un error, o no ha modificado datos');
            } else if(resp.state == 'ADD_FAIL') {
                alert('Ha ocurrido un error, verifique datos o intente más tarde');
            }
                
                

            
            if (resp.todo == 'cargarArbolMenu') {
                cargarArbolMenu() ;
            }
            
        }
    
    
        function {$domPrefix}insertar(model, items) {
            var res = '';";

            $this->_out .= "dojo.xhrPost( {
                url: base_url+'objects',
                content: {
                ";
            $i=0;

            for ($j=1; $j<$count; $j++){
                $node=$this->layout[$j];
                $params=array();
                if (isset($node['ADD']) && ($node['ADD'] == "true" || $node['ADD'] == "readonly") && $node['TYPE'] != "pk_original") {
                    $pfx='_add';
                    if ($node['TYPE']=='dojo_filtering_select') {
                          $this->_out.="\t\t\t\t'data[{$node['TARGET']}]' : dijit.byId('edit{$i}_{$domPrefix}{$pfx}{$j}').get('value'), \r\n";
                    } else {
                         $this->_out.="\t\t\t\t'data[{$node['TARGET']}]' : document.getElementById('edit{$i}_{$domPrefix}{$pfx}{$j}').value, \r\n";
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
    
        function {$domPrefix}actualizar(model, items, id) {
            var res = '';";

            $this->_out .= "
        
            dojo.xhrPost( {
                url: base_url+'objects',
                content: {
                ";


            for ($j=1; $j<$count; $j++) {
                $node = $this->layout[$j];
                $params = array();
                if (isset($node['EDIT']) && ($node['EDIT'] == "true" || $node['EDIT'] == "readonly")) {
                    $pfx = '';
                    if ($node['TYPE'] == 'dojo_filtering_select' || $node['TYPE'] == 'dojo_yes_no'){
                        $this->_out.="     'data[{$node['TARGET']}]' : dijit.byId('edit{$i}_{$domPrefix}{$pfx}{$j}').get('value'), \r\n";
                    } else if ($node['TYPE'] == 'dojo_uploader') {
                        $this->_out.="     'data[{$node['TARGET']}]' : typeof(dijit.byId('edit{$i}_{$domPrefix}{$pfx}{$j}').get('value')[0]) != 'undefined' 
                            ? dijit.byId('edit{$i}_{$domPrefix}{$pfx}{$j}').get('value')[0].name 
                            : null, \r\n";
                        $this->_out.="     'metadata[{$node['TARGET']}][\"size\"]' : typeof(dijit.byId('edit{$i}_{$domPrefix}{$pfx}{$j}').get('value')[0]) != 'undefined' 
                            ? dijit.byId('edit{$i}_{$domPrefix}{$pfx}{$j}').get('value')[0].size 
                            : null, \r\n";
                        $this->_out.="     'metadata[{$node['TARGET']}][\"type\"]' : typeof(dijit.byId('edit{$i}_{$domPrefix}{$pfx}{$j}').get('value')[0]) != 'undefined' 
                            ? dijit.byId('edit{$i}_{$domPrefix}{$pfx}{$j}').get('value')[0].type 
                            : null, \r\n";
                    } else if ($node['TYPE'] == "pk_original") { 
                        $this->_out.="     'id[]' : document.getElementById('edit{$i}_{$domPrefix}zwei_pk_original').value, \r\n";                   
                    } else {
                        $this->_out.="     'data[{$node['TARGET']}]' : document.getElementById('edit{$i}_{$domPrefix}{$pfx}{$j}').value, \r\n";
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

                $this->_out .= "function {$domPrefix}showDialogPass() {
                var formDlg = dijit.byId('{$domPrefix}formPassword');
                formDlg.set('title','Cambio de Contraseña');
                var items = dijit.byId('{$domPrefix}main_grid').selection.getSelected();
                if(items[0]==undefined){
                    alert('Por favor selecciona la fila con tus datos');
                    return;
                }
                formDlg.show();
        }\r\n
        
        function {$domPrefix}changePassword(model, items){
            var strNvoPass  = dijit.byId(\"{$domPrefix}password[0]\").get(\"value\");
            var strNvoPassConf  = dijit.byId(\"{$domPrefix}password_confirm[0]\").get(\"value\");
        
            var items = dijit.byId('{$domPrefix}main_grid').selection.getSelected();
            var id = items[0].id;
            
            if(strNvoPass != strNvoPassConf) {
                alert(\"La confirmacion de la nueva contrasena es erronea\");
                return false;
            }else{
            
             dojo.xhrPost( {
                url: base_url+'objects',
                content: {
                    'data[password]':hex_md5(document.getElementById('{$domPrefix}password[0]').value),\r\n
                    '$primary'  : id,
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
        $domPrefix = (isset($this->_mainPane) && $this->_mainPane == 'dijitTabs') ? Zwei_Utils_String::toVarWord(str_replace('.', '_', $form->p)) : '';
        $out = $this->_out;
        $this->_out = '';
        $out .= "<div dojoType=\"dijit.form.Form\" method=\"post\" action=\"".BASE_URL."uploads?{$_SERVER['QUERY_STRING']}\" enctype=\"multipart/form-data\" target=\"ifrm_process\" id=\"{$domPrefix}tabFormInner{$mode}\" class=\"tabForm$mode\" jsId=\"{$domPrefix}tabFormInner$mode\" name=\"{$domPrefix}tabFormInner$mode\" >\r\n";

        $primary = $this->_model->getPrimary() ? $this->_model->getPrimary() : 'id';
        $id = isset($form->{$primary}) ? "'{$this->id[0]}'" : 'undefined'; 
        
        $out.="
        <script type=\"dojo/method\" event=\"onSubmit\">
        if (this.validate()) {
            try {
                console.debug(arguments);
                {$this->_model->getEditValidation()}
                {$domPrefix}modify('{$this->layout[0]['TARGET']}', arguments[0], '$mode', $id);
                return true;
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
        
        
        $out .= "<table>\r\n";
        $count = count($this->layout);
        //if (!isset($this->id)) $this->id = array();
        //elseif (!is_array($this->id)) $this->id = array($this->id);
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
        
                if ($node[$mode] == "true" || $node[$mode] == "disabled" || $node[$mode] == "readonly") {
                    if ($node[$mode] == "disabled") $params["DISABLED"] = $mode;
                    if ($node[$mode] == "readonly") $params["READONLY"] = $mode;
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
                        $out .= $element->edit($i, $domPrefix.'zwei_pk_original');
                    } else if ($node['TYPE'] != 'hidden') {
                        $out .= "<tr><td><label for=\"{$node['TARGET']}\">{$node['NAME']}</label></td><td>".$element->edit($i, $domPrefix.$pfx.$j)."</td></tr>";
                    } else {
                        $out .= $element->edit($i,$domPrefix.$pfx.$j);
                    }
                }
            }
            $out.="
                <tr>
                            <td align=\"center\" colspan=\"2\">
                            <input type=\"hidden\" name=\"action\" id=\"action\" value=\"\" />
                                <button dojoType=\"dijit.form.Button\" type=\"submit\" onClick=\"if (global_opc=='add') { return dijit.byId('{$domPrefix}tabFormInnerADD').validate(); } else { return dijit.byId('{$domPrefix}tabFormInnerEDIT').validate(); }\">
                                    Guardar
                                </button>
                                <button dojoType=\"dijit.form.Button\" type=\"button\" onClick=\"if (global_opc=='add') { dijit.byId('{$domPrefix}formDialogo').hide(); } else { dijit.byId('{$domPrefix}formDialogoEditar').hide(); }\">
                                    Cancelar
                                </button>
                            </td>
                        </tr>
                        ";
            $out.="<tr><td>&nbsp;</td></tr>";
        }
        $out.="</table>\r\n";
        $out.="</div>";

        return $out;
    }
}
