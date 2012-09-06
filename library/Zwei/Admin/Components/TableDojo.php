<?php

/**
 * Tabla HTML Dojo para un Dojo Layout, Interfaz para operaciones CRUD
 *
 * @example
 * <code>
 * <section name="Perfiles" type="table_dojo" target="acl_roles" list="true" search="msisdn,fijo" edit="true" add="true" delete="true">
 <field name="ID" target="id" type="id_box" visible="false" edit="false" add="false"/>
 <field name="Perfil" target="role_name" type="dojo_validation_textbox" required="true" trim="true" visible="true" edit="true" add="true"/>
 </section>
 * </code>
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Components
 * @version $Id:$
 * @since 0.3
 *
 */

class Zwei_Admin_Components_TableDojo extends Zwei_Admin_Controller implements Zwei_Admin_ComponentsInterface
{
    /**
     * 
     * @var string
     */
    public $page;
    /**
     * 
     * @var int
     */
    private $_version = 9;//actualizar para forzar update de javascript [TODO] hacer administrable
    /**
     * 
     * @var Zwei_Db_Table
     */
    private $_model;

    /**
     * Despliegue para mostrar listados
     * @return string HTML
     */
    function display()
    {
        $form = new Zwei_Utils_Form();
        $viewtable = new Zwei_Admin_Components_Helpers_ViewTableDojo($this->page);
        $viewtable->getLayout();
        
        $request = array();
        foreach (get_object_vars($form) as $var=>$val){
            $request[$var] = $val;
        }
        
        $excelVersion = isset($this->_config->zwei->excel->version) ? $this->_config->zwei->excel->version : 'Excel5';
        $domPrefix = ($this->_mainPane == 'dijitTabs') ? Zwei_Utils_String::toVarWord(str_replace('.', '_', $form->p)) : '';

        $start = isset($request['start']) ? (int)$request['start'] : 0;
        $search = isset($request['search']) ? $request['search'] : "";


        //Zwei_Utils_Debug::write($viewtable->layout);
        $out = "<h2>{$viewtable->layout[0]['NAME']}</h2>\r\n";
        if(!empty($viewtable->layout[0]['JS'])) $out.="<script type=\"text/javascript\" src=\"".BASE_URL."js/".$viewtable->layout[0]['JS']."?version={$this->_version}\"></script>";
        $out .= "
        <div id=\"{$domPrefix}content_dojo\" class=\"content_dojo\" style=\"width:100%\">\r\n";
        
        $model = Zwei_Utils_String::toClassWord($viewtable->layout[0]['TARGET']) . "Model";
        $this->_model = new $model;
        $getPk = $this->_model->getPrimary();

        $primary = ($getPk && !@stristr($getPk, ".")) ? $getPk : "id";


        if ($viewtable->layout[1]['_name'] == 'TAB') {
            $edittable = new Zwei_Admin_Components_Helpers_EditTabs($this->page);
        } elseif ($viewtable->layout[1]['_name'] == 'TAB_DOJO'){
            $edittable = new Zwei_Admin_Components_Helpers_EditTabsDojo($this->page);
        } else {
            $edittable = new Zwei_Admin_Components_Helpers_EditTableDojo($this->page);
        }
        $edittable->getLayout();
        
        $id = $edittable->layout[1]['TARGET'];
        if(isset($request[$id])) $edittable->setId($request[$id]);

        //$params = $this->getRequested_params();

        $out .= $viewtable->display();
        $out .= "\r\n<table align=\"center\"><tr>";

        if ($viewtable->layout[1]['_name'] == 'TAB') {
            if (isset($viewtable->layout[0]['ADD']) && $viewtable->layout[0]['ADD'] == "true" && $this->_acl->isUserAllowed($this->page, 'ADD')){
                $out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconNewTask\" id=\"{$domPrefix}btnAdd\" onClick=\"cargarTabsPanelCentral('$this->page','add', '$primary');try{initModule();}catch(e){console.debug(e);}\">";
                $out .= "Agregar ".$viewtable->layout[0]['NAME'];
                $out .= "</button></td>";
            }

            if (isset($viewtable->layout[0]['EDIT']) && $viewtable->layout[0]['EDIT'] == "true"  && $this->_acl->isUserAllowed($this->page, 'EDIT')){
                $out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconEdit\" id=\"{$domPrefix}btnEdit\" onClick=\"cargarTabsPanelCentral('$this->page','edit', '$primary');try{initModule();}catch(e){console.debug(e);}\">";
                $out .= "Editar ".$viewtable->layout[0]['NAME'];
                $out .= "</button></td>";
            }

            if (isset($viewtable->layout[0]['CLONE']) && $viewtable->layout[0]['CLONE'] == "true"  && $this->_acl->isUserAllowed($this->page, 'ADD')){
                $out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconNewTask\" id=\"{$domPrefix}btnClone\" onClick=\"cargarTabsPanelCentral('$this->page','clone', '$primary');try{initModule();}catch(e){console.debug(e);}\">";
                $out .= "Clonar ".$viewtable->layout[0]['NAME'];
                $out .= "</button></td>";
            }
            
            
        } else {
            if (isset($viewtable->layout[0]['ADD']) && $viewtable->layout[0]['ADD'] == "true" && $this->_acl->isUserAllowed($this->page, 'ADD')){
                $out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconNewTask\" id=\"{$domPrefix}btnAdd\" onClick=\"showDialog('add');\">";
                $out .= "Agregar ".$viewtable->layout[0]['NAME'];
                $out .= "</button></td>";
            }

            if (isset($viewtable->layout[0]['EDIT']) && $viewtable->layout[0]['EDIT'] == "true"  && $this->_acl->isUserAllowed($this->page, 'EDIT')){
                $out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconEdit\" id=\"btnEdit\" onClick=\"showDialog('edit');\">";
                $out .= "Editar ".$viewtable->layout[0]['NAME'];
                $out .= "</button></td>";
            }
        }

        if (isset($viewtable->layout[0]['CHANGE_PASSWORD']) && $viewtable->layout[0]['CHANGE_PASSWORD'] == "true"  && $this->_acl->isUserAllowed($this->page, 'EDIT')){
            $out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconEdit\" id=\"{$domPrefix}btnPswd\" onClick=\"showDialogPass();\">";
            $out .= "Cambiar Contrase&ntilde;a";
            $out .= "</button></td>";
        }


        if (isset($viewtable->layout[0]['DELETE']) && $viewtable->layout[0]['DELETE'] == "true" && $this->_acl->isUserAllowed($this->page, 'DELETE')) {
            $out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconDelete\" id=\"{$domPrefix}btnEliminarUsr\" onClick=\"eliminar('{$viewtable->layout[0]['TARGET']}', '$primary');\">";
            $out .= "Eliminar ".$viewtable->layout[0]['NAME'];
            $out .= "</button></td>";
        }

        if (isset($viewtable->layout[0]['EXCEL']) && $viewtable->layout[0]['EXCEL'] == "true") {
            $out .= "<td>";
            if (@$viewtable->layout[0]['SEARCH_TYPE'] == 'multiple' || !empty($viewtable->layout[0]['SEARCH_TABLE'])) {
                $out .= "<button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconTable\" id=\"{$domPrefix}btnExport\" onClick=\"searchMultiple('{$viewtable->layout[0]['TARGET']}', $viewtable->search_in_fields, $viewtable->search_format, $viewtable->between, 'excel', '$this->page');\">";
            } else {
                $out .= "<button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconTable\" id=\"{$domPrefix}btnExport\" onClick=\"cargarDatos('{$viewtable->layout[0]['TARGET']}', $viewtable->search_in_fields, $viewtable->format_date, $viewtable->search_format, $viewtable->between, 'excel', '$this->page');\">";
            }
            $out .= "Exportar a Excel";
            $out .= "</button></td>";
        }

        if (isset($viewtable->layout[0]['FUNCTIONS'])) {
            $CustomFunctions = new Zwei_Utils_CustomFunctions();//Declarar esta funcion en proyectos específico heredando de Zwei_Utils_CustomFunctionsBase()
            $params = '';
            $component = $this->page;
            $functions = explode(";",(@$viewtable->layout[0]['FUNCTIONS']));
            $permissions = explode(";",(@$viewtable->layout[0]['FUNCTIONS_PERMISSIONS']));
            $i = 0;
            foreach ($functions as $f) {
                //Zwei_Utils_Debug::write($permissions[$i]);
                if (empty($permissions[$i]) || $this->_acl->isUserAllowed($this->page, strtoupper($permissions[$i]))) {
                    $foo = Zwei_Utils_String::toFunctionWord($f);
                    $out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"{$CustomFunctions->getIcon($foo)}\" id=\"{$domPrefix}btn$foo\" onClick=\"execFunction('$f', '$params', '$component', '$primary');\">";
                    $out .= $CustomFunctions->getName($foo);
                    $out .= "</button></td>";
                }
                $i++;
            }
        }
        $permissions = false;
        if (isset($viewtable->layout[0]['LINKS'])) {
            $CustomFunctions = new Zwei_Utils_CustomFunctions();//Declarar esta funcion en proyectos específico heredando de Zwei_Utils_CustomFunctionsBase()
            $params = '';
            $model = $this->page;
            $items = explode(";",(@$viewtable->layout[0]['LINKS']));
            $permissions = explode(";",(@$viewtable->layout[0]['LINKS_PERMISSIONS']));
            $titles = explode(";",(@$viewtable->layout[0]['LINKS_TITLE']));
            $i=0;
            foreach ($items as $f) {
                //Zwei_Utils_Debug::write($permissions[$i]);
                if (empty($permissions[$i]) || $this->_acl->isUserAllowed($this->page, strtoupper($permissions[$i]))) {
                    $out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"{$CustomFunctions->getIcon()}\" id=\"{$domPrefix}btnlink$i\" onClick=\"redirectToModule('$i', '$primary');\">";
                    $out .= $CustomFunctions->getName($foo);
                    $out .= "</button></td>";
                }
                $i++;
            }
        }

        $permissions = false;

        $popups = array();
        if (isset($viewtable->layout[0]['POPUPS'])) {
            $CustomFunctions = new Zwei_Utils_CustomFunctions();
            $params = '';
            $model = $this->page;
            $items = explode(";",(@$viewtable->layout[0]['POPUPS']));
            $permissions = explode(";",(@$viewtable->layout[0]['POPUPS_PERMISSIONS']));
            $titles = explode(";",(@$viewtable->layout[0]['POPUPS_TITLE']));
            $iframes = explode(";",(@$viewtable->layout[0]['POPUPS_IFRAME']));
            $icons = explode(";",(@$viewtable->layout[0]['POPUPS_ICONS']));
            $i = 0;
            foreach ($items as $f) {
                //$href=str_replace("{id}",$this->params['ID'],$f);
                //$href=str_replace("{value}",$this->value,$href);
                $sIcon = (!empty($icons[$i]) && $icons[$i] != "null") ? $icons[$i] : "dijitIconApplication"; 
                $sIframe = (!empty($iframes[$i]) && $iframes[$i]=="true") ? 'true' : 'false';
                $sTitle = (!empty($titles[$i])) ? $titles[$i] : 'undefined';
                //Zwei_Utils_Debug::write($permissions[$i]);
                if (empty($permissions[$i]) || $this->_acl->isUserAllowed($this->page, strtoupper($permissions[$i]))) {
                    $out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"$sIcon\" id=\"{$domPrefix}btnlink$i\" onClick=\"popupGrid('$f', $sIframe, '$primary', '$sTitle');\">";
                    $out .= $titles[$i];
                    $out .= "</button></td>";
                }
                $i++;
            }
            $popups = $items;
        }


        $out .= "</tr></table>\r\n";
        
        $height = isset ($viewtable->layout[0]['HEIGHT']) ? "height=\"{$viewtable->layout[0]['HEIGHT']}\"" : "";
        $width = isset ($viewtable->layout[0]['WIDTH']) ? "width=\"{$viewtable->layout[0]['WIDTH']}\"" : "";
        $style = isset ($viewtable->layout[0]['STYLE']) ? "style=\"{$viewtable->layout[0]['STYLE']}\"" : "";
        $iframe = isset ($viewtable->layout[0]['IFRAME']) && $viewtable->layout[0]['IFRAME'] == 'true' ? 'true' : 'false';
        $initModule = isset ($viewtable->layout[0]['JS']) ? "initModule();" : "";

        if ((@$viewtable->layout[0]['ADD'] == 'true' || @$viewtable->layout[0]['CLONE'] == 'true')
        && ($this->_acl->isUserAllowed($this->page, 'ADD'))) 
        {
            if ($viewtable->layout[1]['_name'] != 'TAB')
            {
                $out .= "<div dojoType=\"dijit.Dialog\" id=\"{$domPrefix}formDialogo\" jsId=\"formDialogo\" refreshOnShow=\"true\" onHide=\"this.reset()\" $style title=\"Agregar {$viewtable->layout[0]['NAME']}\"  execute=\"modify('{$viewtable->layout[0]['TARGET']}',arguments[0]);\">\r\n";
                $out .= "\t".$edittable->display('ADD');
                $out .= "\n</div>\r\n";
    
            } else {
                $out .= "<div dojoType=\"dijit.Dialog\" id=\"{$domPrefix}formDialogo\" jsId=\"formDialogo\" $style title=\"Agregar {$viewtable->layout[0]['NAME']}\" onload=\"global_opc='add';showtab('tabadd_ctrl1', 'tabadd1');$initModule\" execute=\"modify('{$viewtable->layout[0]['TARGET']}',arguments[0]);\">\r\n";
                if ($iframe == 'true') {
                    $out .= "\t<iframe src=\"\" id=\"{$domPrefix}iframeDialogAdd\" name=\"iframeDialogAdd\" frameborder=\"no\" $height $width></iframe>";
                }
                $out .= "\n</div>\r\n";
            }
        }

        if ((isset($viewtable->layout[0]['EDIT']) && $viewtable->layout[0]['EDIT'] == 'true')
        && ($this->_acl->isUserAllowed($this->page, 'EDIT')))
        {
            if ($viewtable->layout[1]['_name'] != 'TAB')
            {
                $out .= "<div dojoType=\"dijit.Dialog\" id=\"{$domPrefix}formDialogoEditar\" $style refreshOnShow=\"true\" jsId=\"formDialogoEditar\" title=\"Editar {$viewtable->layout[0]['NAME']}\" execute=\"modify('{$viewtable->layout[0]['TARGET']}',arguments[0]);\">\r\n";
                $out .= "\t".$edittable->display('EDIT');
                $out .= "\n</div>\r\n";
            } else {
                $out .= "<div dojoType=\"dijit.Dialog\" id=\"{$domPrefix}formDialogoEditar\" $style jsId=\"formDialogoEditar\" refreshOnShow=\"true\" title=\"Editar {$viewtable->layout[0]['NAME']}\"  onload=\"global_opc='edit';showtab('tabedit_ctrl1', 'tabedit1');$initModule\"  execute=\"modify('{$viewtable->layout[0]['TARGET']}',arguments[0]);\">\r\n";
                if ($iframe == 'true') {
                    $out .= "\t<iframe src=\"\" id=\"{$domPrefix}iframeDialogEdit\" name=\"iframeDialogoEdit\" frameborder=\"no\" $height $width></iframe>";
                }    
                $out .= "\n</div>\r\n";
            }
        }

        
        
        $i=0;
        
        foreach ($popups as $i => $v)
        {
            if (!empty($iframes[$i]) && $iframes[$i]=="true")
            {
                $out .= "<div dojoType=\"dijit.Dialog\" id=\"{$domPrefix}formDialogo$i\" jsId=\"formDialogo$i\" title=\"{$titles[$i]}\" execute=\"modify('{$viewtable->layout[0]['TARGET']}',arguments[0]);\">\r\n";
                $out .= "\n</div>\r\n";
            } else {
                $out .= "<div dojoType=\"dijit.Dialog\" id=\"{$domPrefix}formDialogo$i\" jsId=\"formDialogo$i\" title=\"{$titles[$i]}\"  onload=\"global_opc='edit';showtab('tabedit_ctrl1', 'tabedit1', $iframe);$initModule\"  execute=\"modify('{$viewtable->layout[0]['TARGET']}',arguments[0]);\">\r\n";
                if ($iframe == 'true') {
                    $out .= "\t<iframe src=\"\" id=\"{$domPrefix}iframeDialogEdit\" name=\"iframeDialogoEdit$i\" frameborder=\"no\" $height $width $style></iframe>";
                }    
                $out .= "\n</div>\r\n";
            }
            $i++;
        }
        
        
        $out .= "</div>\r\n";
        $out .="<div id=\"{$domPrefix}output_grid\"></div>";


        if (@$viewtable->layout[0]['CHANGE_PASSWORD']=='true' && $this->_acl->isUserAllowed($this->page,'EDIT')) {
            $out .= "<div dojoType=\"dijit.Dialog\" id=\"{$domPrefix}formPassword\" title=\"Cambio de password\" execute=\"changePassword('{$viewtable->layout[0]['TARGET']}',arguments[0]);\">\r\n";
            $out .= "<br/><br/>\r\n";
            $out .= "
                <table cellspacing=\"10\" align=\"center\">
                    <tr>
                        <td>
                            <label for=\"txtNvoPass\">Nueva contrase&ntilde;a</label>
                        </td>
                        <td>
                            <input type=\"password\" name=\"txtNvoPass\" placeHolder=\"Ingresar nueva contrase&ntilde;a\" dojoType=\"dijit.form.ValidationTextBox\"
                                   trim=\"true\" required=\"true\" id=\"{$domPrefix}password[0]\" pwType=\"new\"  />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for=\"txtNvoPassConf\">Re-ingrese contrase&ntilde;a</label>
                        </td>
                        <td>
                            <input type=\"password\" name=\"txtNvoPassConf\" placeHolder=\"Confirmar nueva contrase&ntilde;a\" dojoType=\"dijit.form.ValidationTextBox\"
                                   trim=\"true\" required=\"true\" id=\"{$domPrefix}password_confirm[0]\" pwType=\"verify\" />
                        </td>
                    </tr>
                    <tr>
                        <td colspan=\"2\" align=\"center\">
                            <button type=\"submit\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconSave\" id=\"{$domPrefix}btnGuardarDatosPass\"
                                    onClick=\"return dijit.byId('formPassword').validate();\">
                                Guardar Contrase&ntilde;a
                            </button>
                        </td>

                    </tr>
                </table>\r\n";
            $out.="</div>";
        }

        $out .= "<input type=\"hidden\" id=\"{$domPrefix}data_url\" value=\"\" />";

        if (!empty($viewtable->layout[0]['JS'])) {
            //Función opcional para ser ejecutada al cargar el JS {nombrejs}Init()
            $functionInit = str_replace('.js','', $viewtable->layout[0]['JS']).'Init';
            $out .= "
            <script type=\"text/javascript\">
                function initModule(){
                    try {
                       $functionInit();
                    } catch (e) {
                        console.log('Función $functionInit no declarada');    
                    }
                }
                initModule();  
            </script>
            ";
        }
        
        
        /**
         * Si cargamos un lightbox debemos tener el javascript necesario fuera de el (acá)
         */
        if ($viewtable->layout[1]['_name'] == 'TAB')
        {       
            $xhr_insert_data = '';
            $xhr_update_data = '';
            
            $file = Zwei_Admin_Xml::getFullPath($this->page);
            
            $string_xml = file_get_contents($file);
            $Xml = new SimpleXMLElement($string_xml);
            $tabs = $Xml->children();
            
            $k = 1;
            foreach ($tabs as $tab) {
                foreach ($tab->children() as $node) {
                    if (($node["add"] == "true" || $node["add"] == "readonly" || $node["clone"] == "true" || $node["clone"] == "readonly") && !empty($node['target'])) {
                        $pfx = '_add';
                        if ($node['type']=='dojo_filtering_select' || $node['type'] == 'dojo_yes_no' || $node['type'] == 'dojo_checkbox') {
                            $xhr_insert_data .= "\t\t\t\t'data[{$node['target']}]' : dijit.byId('edit0_{$pfx}{$k}').get('value'), \r\n";
                        } else if (strstr($node['type'], "dojo_checked_multiselect")) {    
                            $xhr_insert_data .= "\t\t\t\t'data[{$node['target']}]' : dijit.byId('edit0_{$pfx}{$k}').get('value').join(':::'), \r\n";
                        } else {
                            $xhr_insert_data .= "\t\t\t\t'data[{$node['target']}]' : document.getElementById('edit0_{$pfx}{$k}').value, \r\n";
                        }
                    }

                    if (($node["edit"] == "true" || $node["edit"] == "readonly") && !empty($node['target'])) {
                        $pfx = '';
                        if ($node['type'] == 'dojo_filtering_select' || $node['type'] == 'dojo_yes_no' || $node['type'] == 'dojo_checkbox') {
                            $xhr_update_data .= "\t\t\t\t'data[{$node['target']}]' : dijit.byId('edit0_{$pfx}{$k}').get('value'), \r\n";
                        } else if (strstr($node['type'], "dojo_checked_multiselect")) {        
                            $xhr_update_data .= "\t\t\t\t'data[{$node['target']}]' : dijit.byId('edit0_{$pfx}{$k}').get('value').join(':::'), \r\n";
                        } else {
                            $xhr_update_data .= "\t\t\t\t'data[{$node['target']}]' : document.getElementById('edit0_{$pfx}{$k}').value, \r\n";
                        }
                    }
                    $k++; 
                }
            }

            $modelclass = Zwei_Utils_String::toClassWord($viewtable->layout[0]['TARGET'])."Model";
            $Model = new $modelclass();
            $additional_validation = $Model->getEditValidation();//usar en js var global_opc para discriminar entre 'edit' y add'

            $out.="
            <script type=\"text/javascript\">
            //showtab('tab_ctrl1', 'tab1');
            function modify(model, items, mode) {
                var resp = '';
                console.log('modify');
                $additional_validation
                if(mode == 'add' || mode == 'clone') {
                    resp = insertar(model,items);
                } else if(mode == 'edit') {
                    var items = main_grid.selection.getSelected();
                    var id = items[0].$primary;
                    resp = actualizar(model, items, id);
                }
                   
                if(resp.message != '' && resp.message != null){
                    alert(resp.message);
                }else if(resp.state == 'UPDATE_OK'){
                    alert('Datos Actualizados');
                    cargarDatos(model);
                    dijit.byId('formDialogoEditar').hide();
                }else if(resp.state == 'ADD_OK'){
                    alert('Datos Ingresados');
                    cargarDatos(model);
                    dijit.byId('formDialogo').hide();
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
                console.log('actualizar');
                var res = '';
                dojo.xhrPost( {
                    url: base_url+'objects',
                    content: {
                        $xhr_update_data
                        '$primary'        : id,
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
        }   
        return $out; 
    }
}
