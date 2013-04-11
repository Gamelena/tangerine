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
    private $_version = 10;//actualizar para forzar update de javascript [TODO] hacer administrable
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
        if (!$viewtable->search_in_fields) $viewtable->search_in_fields = 'false';
        if (!$viewtable->format_date) $viewtable->format_date = 'false';
        if (!$viewtable->search_format) $viewtable->search_format = 'false';
        if (!$viewtable->between) $viewtable->between = 'false';
        
        
        $viewtable->getLayout();
        $file = Zwei_Admin_Xml::getFullPath($this->page);
        $xml = new Zwei_Admin_Xml($file, 0, 1);
        
        $request = array();
        foreach (get_object_vars($form) as $var=>$val){
            $request[$var] = $val;
        }
        
        $excelVersion = isset($this->_config->zwei->excel->version) ? $this->_config->zwei->excel->version : 'Excel5';
        $domPrefix = (isset($this->_mainPane) && $this->_mainPane == 'dijitTabs') ? Zwei_Utils_String::toVarWord($form->p) : '';
        $dojoVersion = (isset($this->_config->resources->dojo->cdnVersion)) ? $this->_config->resources->dojo->cdnVersion : "1.6.1";
        
        $start = isset($request['start']) ? (int)$request['start'] : 0;
        $search = isset($request['search']) ? $request['search'] : "";


        $out = "<img id=\"{$domPrefix}switchMainPaneButton\" src=\"http://localhost/promociones/images/expand.png\" onclick=\"switchMainPane()\" style=\"position: relative;float: left;\"/>";
        $out .= "<script>if(typeof(switchMainPane) != \"function\") { dojo.byId('{$domPrefix}switchMainPaneButton').style.display='none'; }</script>";
        $out .= "<h2>{$viewtable->layout[0]['NAME']}</h2>\r\n";
        if (!empty($viewtable->layout[0]['JS'])) $out.="<script type=\"text/javascript\" src=\"".BASE_URL."js/".$xml->getAttribute('js')."?version={$this->_version}\"></script>";
        $out .= "
        <div id=\"{$domPrefix}content_dojo\" class=\"content_dojo\" style=\"width:100%\">\r\n";
        
        $model = Zwei_Utils_String::toClassWord($xml->getAttribute('target')) . "Model";
        $this->_model = new $model;
        $getPk = $this->_model->getPrimary();
        
        $primary = ($getPk && !@stristr($getPk, ".")) ? $getPk : "id";
        
        if ($xml->existsChildren('tab')) {
            $edittable = new Zwei_Admin_Components_Helpers_EditTabs($this->page);
        } else {
            $edittable = new Zwei_Admin_Components_Helpers_EditTableDojo($this->page);
        }
        $edittable->getLayout();

        $id = $edittable->layout[1]['TARGET'];//Esto debe ser reemplazo por $this->_model->getPrimary
        if (isset($request[$id])) $edittable->setId($request[$id]);//?
        
        //$params = $this->getRequested_params();
        
        $out .= $viewtable->display();
        $out .= "\r\n<table align=\"center\"><tr>";
        
        if ($xml->existsChildren('tab')) {
            if ($xml->getAttribute("add") && $xml->getAttribute("add") == "true" && $this->_acl->isUserAllowed($this->page, 'ADD')){
                $out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconNewTask\" id=\"{$domPrefix}btnAdd\" onClick=\"cargarTabsPanelCentral('$this->page', 'add', '$primary', '$domPrefix');try{initModule();}catch(e){console.debug(e);}\">";
                $out .= "Agregar ".$xml->getAttribute("name");
                $out .= "</button></td>";
            }
            
            if ($xml->getAttribute("edit") && $xml->getAttribute("edit") == "true"  && $this->_acl->isUserAllowed($this->page, 'EDIT')){
                $out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconEdit\" id=\"{$domPrefix}btnEdit\" onClick=\"cargarTabsPanelCentral('$this->page', 'edit', '$primary', '$domPrefix');try{initModule();}catch(e){console.debug(e);}\">";
                $out .= "Editar ".$xml->getAttribute("name");
                $out .= "</button></td>";
            }
            
            if ($xml->getAttribute("clone") && $xml->getAttribute("clone") == "true"  && $this->_acl->isUserAllowed($this->page, 'ADD')){
                $out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconNewTask\" id=\"{$domPrefix}btnClone\" onClick=\"cargarTabsPanelCentral('$this->page', 'clone', '$primary', '$domPrefix');try{initModule();}catch(e){console.debug(e);}\">";
                $out .= "Clonar ".$xml->getAttribute("name");
                $out .= "</button></td>";
            }
            
            
        } else {
            if ($xml->getAttribute("add") && $xml->getAttribute("add") == "true" && $this->_acl->isUserAllowed($this->page, 'ADD')){
                $out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconNewTask\" id=\"{$domPrefix}btnAdd\" onClick=\"{$domPrefix}showDialog('add');\">";
                $out .= "Agregar ".$xml->getAttribute("name");
                $out .= "</button></td>";
            }
            
            if ($xml->getAttribute("edit") && $xml->getAttribute("edit") == "true" && $this->_acl->isUserAllowed($this->page, 'EDIT')){
                $out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconEdit\" id=\"{$domPrefix}btnEdit\" onClick=\"{$domPrefix}showDialog('edit');\">";
                $out .= "Editar ".$xml->getAttribute("name");
                $out .= "</button></td>";
            }
        }
        
        if ($xml->getAttribute("change_password") && $xml->getAttribute("change_password") == "true"  && $this->_acl->isUserAllowed($this->page, 'EDIT')){
            $out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconEdit\" id=\"{$domPrefix}btnPswd\" onClick=\"{$domPrefix}showDialogPass();\">";
            $out .= "Cambiar Contrase&ntilde;a";
            $out .= "</button></td>";
        }
        
        if ($xml->getAttribute("delete") && $xml->getAttribute("delete") == "true" && $this->_acl->isUserAllowed($this->page, 'DELETE')) {
            $out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconDelete\" id=\"{$domPrefix}btnEliminarUsr\" onClick=\"eliminar('{$xml->getAttribute("target")}', '$primary', '{$domPrefix}');\">";
            $out .= "Eliminar ".$xml->getAttribute("name");
            $out .= "</button></td>";
        }
        
        if ($xml->getAttribute("excel") && $xml->getAttribute("excel")  == "true") {
            $out .= "<td>";
            if (($xml->getAttribute("search_type") && $xml->getAttribute("search_type") == 'multiple') || $xml->getAttribute("search_table")) {
                $out .= "<button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconTable\" id=\"{$domPrefix}btnExport\" onClick=\"searchMultiple('{$xml->getAttribute("target")}', $viewtable->search_in_fields, $viewtable->search_format, $viewtable->between, 'excel', '$this->page');\">";
            } else {
                $out .= "<button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconTable\" id=\"{$domPrefix}btnExport\" onClick=\"cargarDatos('{$xml->getAttribute("target")}', $viewtable->search_in_fields, $viewtable->format_date, $viewtable->search_format, $viewtable->between, 'excel', '$this->page');\">";
            }
            $out .= "Exportar a Excel";
            $out .= "</button></td>";
        }
        
        if ($xml->getAttribute("functions")) {
            $CustomFunctions = new Zwei_Utils_CustomFunctions();//Declarar esta funcion en proyectos específico heredando de Zwei_Utils_CustomFunctionsBase()
            $params = '';
            $component = $this->page;
            $functions = explode(";",(@$xml->getAttribute("functions")));
            $permissions = explode(";",(@$xml->getAttribute("functions_permissions")));
            $i = 0;
            foreach ($functions as $f) {
                //Zwei_Utils_Debug::write($permissions[$i]);
                if (empty($permissions[$i]) || $this->_acl->isUserAllowed($this->page, strtoupper($permissions[$i]))) {
                    $foo = Zwei_Utils_String::toFunctionWord($f);
                    $out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"{$CustomFunctions->getIcon($foo)}\" id=\"{$domPrefix}btn$foo\" onClick=\"execFunction('$f', '$params', '$component', '$primary', '$domPrefix');\">";
                    $out .= $CustomFunctions->getName($foo);
                    $out .= "</button></td>";
                }
                $i++;
            }
        }
        
        $executeScripts = explode(";",(@$xml->getAttribute("popups_execute_scripts")));
        $dialogDojoType = "dijit.Dialog";
        $execute = "";
        if ($dojoVersion >= '1.8.0') {
            $dialogDojoType = "dojox.widget.DialogSimple";
            $execute = 'executeScripts="true"';
        } 
        
        $permissions = false;
        if ($xml->getAttribute("links")) {
            $CustomFunctions = new Zwei_Utils_CustomFunctions();//Declarar esta funcion en proyectos específico heredando de Zwei_Utils_CustomFunctionsBase()
            $params = '';
            $model = $this->page;
            $items = explode(";",(@$xml->getAttribute("links")));
            $permissions = explode(";",(@$xml->getAttribute("links_permissions")));
            $titles = explode(";",(@$xml->getAttribute("links_title")));
            $i=0;
            foreach ($items as $f) {
                //Zwei_Utils_Debug::write($permissions[$i]);
                if (empty($permissions[$i]) || $this->_acl->isUserAllowed($this->page, strtoupper($permissions[$i]))) {
                    $out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"{$CustomFunctions->getIcon()}\" id=\"{$domPrefix}btnlink$i\" onClick=\"redirectToModule('$i', '$primary', '$domPrefix');\">";
                    $out .= $CustomFunctions->getName($foo);
                    $out .= "</button></td>";
                }
                $i++;
            }
        }
        
        $permissions = false;
        
        $popups = array();
        if ($xml->getAttribute("popups")) {
            $CustomFunctions = new Zwei_Utils_CustomFunctions();
            $params = '';
            $model = $this->page;
            $items = explode(";",(@$xml->getAttribute("popups")));
            $permissions = explode(";",(@$xml->getAttribute("popups_permissions")));
            $titles = explode(";",(@$xml->getAttribute("popups_title")));
            $iframes = explode(";",(@$xml->getAttribute("popups_iframe")));
            $icons = explode(";",(@$xml->getAttribute("popups_icons")));
            /*
             * [TODO] $execute_scripts deberia ser implicitamente 'true' siempre, por ahora se requiere explicitamente
             * para no generar errores en instalaciones anteriores de AdmPortal ya que necesita el módulo "dojox.widget.DialogSimple" para funcionar.
             */
            $executeScripts = explode(";",(@$xml->getAttribute("popups_execute_scripts")));
            if ((!empty($executeScripts[$i]) && $executeScripts[$i] === "true")) {
                $dojoDojoType = "dojox.widget.DialogSimple";
                $execute = 'executeScripts="true"';
            }
            
            $popups_width = explode(";",(@$xml->getAttribute("popups_width")));
            $popups_height = explode(";",(@$xml->getAttribute("popups_height")));
            
            $i = 0;
            foreach ($items as $f) {
                $sIcon = (!empty($icons[$i]) && $icons[$i] != "null") ? $icons[$i] : "dijitIconApplication"; 
                $sIframe = (!empty($iframes[$i]) && $iframes[$i]=="true") ? 'true' : 'false';
                $sTitle = (!empty($titles[$i])) ? $titles[$i] : 'undefined';
                if (empty($permissions[$i]) || $this->_acl->isUserAllowed($this->page, strtoupper($permissions[$i]))) {
                    $out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"$sIcon\" id=\"{$domPrefix}btnlink$i\" onClick=\"popupGrid('$f', $sIframe, '$primary', '$sTitle', '$domPrefix');\">";
                    $out .= $titles[$i];
                    $out .= "</button></td>";
                }
                $i++;
            }
            $popups = $items;
        }
        
        
        $out .= "</tr></table>\r\n";
        
        $height = $xml->getAttribute("height") ? "height=\"{$xml->getAttribute("height")}\"" : "";
        $width = $xml->getAttribute("width") ? "width=\"{$xml->getAttribute("width")}\"" : "";
        $style = $xml->getAttribute("style") ? "style=\"{$xml->getAttribute("style")}\"" : "";
        $iframe = $xml->getAttribute("iframe") && $xml->getAttribute("iframe") == 'true' ? 'true' : 'false';
        $initModule = $xml->getAttribute("js") ? "initModule();" : "";
        
        if (  
           (($xml->getAttribute("add") && $xml->getAttribute("add") == 'true') || ($xml->getAttribute("clone") && $xml->getAttribute("clone") == 'true'))
            && ($this->_acl->isUserAllowed($this->page, 'ADD'))
        ) 
        {
            if (!$xml->existsChildren("tab"))
            {
                $out .= "<div dojoType=\"$dialogDojoType\" $execute id=\"{$domPrefix}formDialogo\" jsId=\"formDialogo\" refreshOnShow=\"true\" onHide=\"this.reset()\" $style title=\"Agregar {$xml->getAttribute("name")}\"  execute=\"{$domPrefix}modify('{$xml->getAttribute("target")}',arguments[0]);\">\r\n";
                $out .= "\t".$edittable->display('ADD');
                $out .= "\n</div>\r\n";
                
            } else {
                $out .= "<div dojoType=\"$dialogDojoType\" $execute id=\"{$domPrefix}formDialogo\" jsId=\"formDialogo\" $style title=\"Agregar {$xml->getAttribute("name")}\" onload=\"global_opc='add';showtab('tabadd_ctrl1', '{$domPrefix}tabadd1');$initModule\" execute=\"{$domPrefix}modify('{$xml->getAttribute("target")}',arguments[0]);\">\r\n";
                if ($iframe == 'true') {
                    $out .= "\t<iframe src=\"\" id=\"{$domPrefix}iframeDialogAdd\" name=\"iframeDialogAdd\" frameborder=\"no\" $height $width></iframe>";
                }
                $out .= "\n</div>\r\n";
            }
        }
        
        if (($xml->getAttribute("edit") && $xml->getAttribute("edit") == 'true')
        && ($this->_acl->isUserAllowed($this->page, 'EDIT')))
        {
            if (!$xml->existsChildren("tab"))
            {
                $out .= "<div dojoType=\"$dialogDojoType\" $execute id=\"{$domPrefix}formDialogoEditar\" $style refreshOnShow=\"true\" jsId=\"formDialogoEditar\" title=\"Editar {$xml->getAttribute("name")}\" execute=\"{$domPrefix}modify('{$xml->getAttribute("target")}',arguments[0]);\">\r\n";
                $out .= "\t".$edittable->display('EDIT');
                $out .= "\n</div>\r\n";
            } else {
                $out .= "<div dojoType=\"$dialogDojoType\" $execute id=\"{$domPrefix}formDialogoEditar\" $style jsId=\"formDialogoEditar\" refreshOnShow=\"true\" title=\"Editar {$xml->getAttribute("name")}\"  onload=\"global_opc='edit';showtab('tabedit_ctrl1', '{$domPrefix}tabedit1');$initModule\"  execute=\"{$domPrefix}modify('{$xml->getAttribute("target")}',arguments[0]);\">\r\n";
                if ($iframe == 'true') {
                    $out .= "\t<iframe src=\"\" id=\"{$domPrefix}iframeDialogEdit\" name=\"iframeDialogoEdit\" frameborder=\"no\" $height $width></iframe>";
                }    
                $out .= "\n</div>\r\n";
            }
        }
        
        
        
        $i=0;
        foreach ($popups as $i => $v) {
            if (!empty($iframes[$i]) && $iframes[$i] == "true") {
                $out .= "<div dojoType=\"$dialogDojoType\" id=\"{$domPrefix}formDialogo$i\" $execute jsId=\"formDialogo$i\" title=\"{$titles[$i]}\" execute=\"{$domPrefix}modify('{$xml->getAttribute("target")}',arguments[0]);\">\r\n";
                $out .= "\n</div>\r\n";
            } else {
                $pwidth = (!empty($popups_width[$i])) ? "min-width:{$popups_width[$i]}px;" : '';
                $pheight = (!empty($popups_height[$i])) ? "min-height:{$popups_height[$i]}px;" : '';
                
                $out .= "<div dojoType=\"$dialogDojoType\" style=\"$pwidth $pheight\" $execute id=\"{$domPrefix}formDialogo$i\" jsId=\"formDialogo$i\" title=\"{$titles[$i]}\"  onload=\"global_opc='edit';showtab('tabedit_ctrl1', '{$domPrefix}tabedit1', $iframe);$initModule\"  execute=\"{$domPrefix}modify('{$xml->getAttribute("target")}',arguments[0]);\">\r\n";
                if ($iframe == 'true') {
                    $out .= "\t<iframe src=\"\" id=\"{$domPrefix}iframeDialogEdit\" name=\"iframeDialogoEdit$i\" frameborder=\"no\" $height $width $style></iframe>";
                }    
                $out .= "\n</div>\r\n";
            }
            $i++;
        }
        
        
        $out .= "</div>\r\n";
        $out .="<div id=\"{$domPrefix}output_grid\"></div>";
        
        
        if (($xml->getAttribute("change_password") && $xml->getAttribute("change_password") == 'true') && $this->_acl->isUserAllowed($this->page,'EDIT')) {
            $out .= "<div dojoType=\"$dialogDojoType\" $execute id=\"{$domPrefix}formPassword\" title=\"Cambio de password\" execute=\"{$domPrefix}changePassword('{$viewtable->layout[0]['TARGET']}',arguments[0]);\">\r\n";
            $out .= "
                <table cellspacing=\"10\" align=\"center\">
                    <tr>
                        <td>
                            <label for=\"txtNvoPass\">Nueva contrase&ntilde;a</label>
                        </td>
                        <td>
                            <input type=\"password\" name=\"txtNvoPass\" placeHolder=\"Ingresar nueva contrase&ntilde;a\" dojoType=\"dijit.form.ValidationTextBox\"
                                   trim=\"true\" required=\"true\" id=\"{$domPrefix}password[0]\" pwType=\"new\"  />
                            <!--<input type=\"hidden\" name=\"pk_original\" id=\"$primary\" pwType=\"new\" value=\"$id\"  />-->
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
                                    onClick=\"return dijit.byId('{$domPrefix}formPassword').validate();\">
                                Guardar Contrase&ntilde;a
                            </button>
                        </td>
                        
                    </tr>
                </table>\r\n";
            $out.="</div>";
        }
        
        $out .= "<input type=\"hidden\" id=\"{$domPrefix}data_url\" value=\"\" />";
        
        if ($xml->getAttribute("js")) {
            //Función opcional para ser ejecutada al cargar el JS {nombrejs}Init()
            $functionInit = str_replace('.js','', $xml->getAttribute("js")).'Init';
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
         * Si cargamos un dijit.tabContainer debemos tener el javascript necesario fuera de el (acá)
         */
        if ($xml->existsChildren("tab"))
        {       
            $out .= $this->getJsCrud($domPrefix, $primary);
        }   
        return $out; 
    }
    
    /**
     * 
     * @param string $domPrefix
     * @param string $primary
     * @param string $jsFriend
     * @return string
     */
    public function getJsCrud($domPrefix = '', $primary = 'id', $jsFriend = '' ) 
    {
        $xhr_insert_data = '';
        $xhr_update_data = '';
        $out = '';
        
        $file = Zwei_Admin_Xml::getFullPath($this->page);
        $Xml = new Zwei_Admin_Xml($file, 0, 1);
        //Si hay pestañas, obtenemos el array de pestañas para recorrerlas una por una, 
        //si no hay pestañas creamos un array con UN elemento para que entre UNA vez al primer loop
        $tabs = ($Xml->existsChildren("tab")) ? $Xml->children() : array(null);
        
        $k = 1;
        foreach ($tabs as $tab) {
            //Si hay pestañas se recorre cada elemento dentro cada pestaña 
            //si no hay pestañas se recorre el xml completo de una vez
            $children = ($Xml->existsChildren("tab")) ? $tab->children() : $Xml->children();
            foreach ($children as $node) {
                if (($node["add"] == "true" || $node["add"] == "readonly" || $node["clone"] == "true" || $node["clone"] == "readonly") && !empty($node['target'])) {
                    $pfx = '_add';
                    if ($node['type'] == 'dojo_filtering_select' || $node['type'] == 'dojo_yes_no' || $node['type'] == 'dojo_checkbox') {
                        $xhr_insert_data .= "\t\t\t\t'data[{$node['target']}]' : dijit.byId('edit0_{$domPrefix}{$pfx}{$k}').get('value'), \r\n";
                    } else if (strstr($node['type'], "dojo_checked_multiselect")) {
                        $xhr_insert_data .= "\t\t\t\t'data[{$node['target']}]' : dijit.byId('edit0_{$domPrefix}{$pfx}{$k}').get('value').join(':::'), \r\n";
                    } else if ($node['type'] == 'dojo_uploader') {
                        $xhr_insert_data .= "\t\t\t\t'data[{$node['TARGET']}]' : typeof(dijit.byId('edit0_{$domPrefix}{$pfx}{$k}').get('value')[0] != 'undefined') 
                            ? dijit.byId('edit0_{$domPrefix}{$pfx}{$k}').get('value')[0].name
                            : null, \r\n";
                        $xhr_insert_data .= "\t\t\t\t'metadata[{$node['TARGET']}][\"size\"]' : typeof(dijit.byId('edit0_{$domPrefix}{$pfx}{$k}').get('value')[0] != 'undefined') 
                            ? dijit.byId('edit0_{$domPrefix}{$pfx}{$k}').get('value')[0].size
                            : null, \r\n";
                        $xhr_insert_data .= "\t\t\t\t'metadata[{$node['TARGET']}][\"type\"]' : typeof(dijit.byId('edit0_{$domPrefix}{$pfx}{$k}').get('value')[0] != 'undefined')
                            ? dijit.byId('edit0_{$domPrefix}{$pfx}{$k}').get('value')[0].type
                            : null, \r\n";
                    } else {
                        $xhr_insert_data .= "\t\t\t\t'data[{$node['target']}]' : document.getElementById('edit0_{$domPrefix}{$pfx}{$k}').value, \r\n";
                    }
                }
        
                if (($node["edit"] == "true" || $node["edit"] == "readonly") && !empty($node['target'])) {
                    $pfx = '';
                    if ($node['type'] == 'dojo_filtering_select' || $node['type'] == 'dojo_yes_no' || $node['type'] == 'dojo_checkbox') {
                        $xhr_update_data .= "\t\t\t\t'data[{$node['target']}]' : dijit.byId('edit0_{$domPrefix}{$pfx}{$k}').get('value'), \r\n";
                    } else if ($node['type'] == 'dojo_uploader') {
                        $xhr_update_data .= "\t\t\t\t'data[{$node['TARGET']}]' : dijit.byId('edit0_{$domPrefix}{$pfx}{$k}').get('value')[0].name, \r\n";
                        $xhr_update_data .= "\t\t\t\t'metadata[{$node['TARGET']}][\"size\"]' : dijit.byId('edit0_{$domPrefix}{$pfx}{$k}').get('value')[0].size, \r\n";
                        $xhr_update_data .= "\t\t\t\t'metadata[{$node['TARGET']}][\"type\"]' : dijit.byId('edit0_{$domPrefix}{$pfx}{$k}').get('value')[0].type, \r\n";
                    } else if (strstr($node['type'], "dojo_checked_multiselect")) {
                        // Se concatenan todos los campos de un multiselect en un string unico con valores delimitados por :::
                        $xhr_update_data .= "\t\t\t\t'data[{$node['target']}]' : dijit.byId('edit0_{$domPrefix}{$pfx}{$k}').get('value').join(':::'), \r\n";
                    } else {
                        $xhr_update_data .= "\t\t\t\t'data[{$node['target']}]' : document.getElementById('edit0_{$domPrefix}{$pfx}{$k}').value, \r\n";
                    }
                }
                $k++;
            }
        }
        
        $modelclass = Zwei_Utils_String::toClassWord($Xml->getAttribute("target"))."Model";
        $this->_model = new $modelclass();
        $additional_validation = $this->_model->getEditValidation();//usar en js var global_opc para discriminar entre 'edit' y add'
        $storeType = $Xml->getAttribute("server_pagination") && $Xml->getAttribute("server_pagination") == 'true' ? "'query'" : 'false';
        
        $out.="
        <script type=\"text/javascript\">
        //showtab('tab_ctrl1', '{$domPrefix}tab1');
        function {$domPrefix}modify(model, items, mode, id ) {
            var resp = '';
            $additional_validation
            if (mode == 'add' || mode == 'clone') {
                resp = {$domPrefix}insertar(model,items);
            } else if(mode == 'edit') {
                if (typeof(id) == 'undefined' && typeof(dijit.byId('{$domPrefix}main_grid')) != 'undefined') {
                    var items = dijit.byId('{$domPrefix}main_grid').selection.getSelected();
                    var id = items[0].$primary;
                }
                resp = {$domPrefix}actualizar(model, items, id);
            }
         
            if(resp.message != '' && resp.message != null){
                alert(resp.message);
            }else if(resp.state == 'UPDATE_OK'){
                alert('Datos Actualizados');
                if(typeof(dijit.byId('{$domPrefix}main_grid')) != 'undefined') 
                    cargarDatos(model, false, false, false, false, 'json', false, '$domPrefix', $storeType);
                dijit.byId('{$domPrefix}formDialogoEditar').hide();
            }else if(resp.state == 'ADD_OK'){
                alert('Datos Ingresados');
                if(typeof(dijit.byId('{$domPrefix}main_grid')) != 'undefined') 
                    cargarDatos(model, false, false, false, false, 'json', false, '$domPrefix', $storeType);
                dijit.byId('{$domPrefix}formDialogo').hide();
            }else if(resp.state == 'UPDATE_FAIL'){
                alert('Ha ocurrido un error, o no ha modificado datos');
            }else if(resp.state == 'ADD_FAIL'){
                alert('Ha ocurrido un error, verifique datos o intente más tarde');
            }
        }
        
        
        function {$domPrefix}insertar(model, items) {
            var res = '';
            var id = null;
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
                    $jsFriend
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
        
        function {$domPrefix}actualizar(model, items, id) {
            console.log('actualizar');
            var res = '';
            dojo.xhrPost( {
                url: base_url+'objects',
                content: {
                    $xhr_update_data
                    '$primary'  : id,
                    'action'    :'edit',
                    'model'     : model,
                    'format'    : 'json'
                },
                handleAs: 'json',
                sync: true,
                preventCache: true,
                timeout: 5000,
                load: function(respuesta) {
                    $jsFriend
                    console.debug(dojo.toJson(respuesta));
                    res = respuesta;
                    return respuesta;
                },
                error:function(err) {
                    alert('Error en comunicacion de datos. error: '+err);
                    return err;
                }
            });
            return res;
        }
        ";
         
        if ($Xml->getAttribute("change_password") && $Xml->getAttribute("change_password") == "true"  && $this->_acl->isUserAllowed($this->page, 'EDIT')){
        
            $out .= "
            function {$domPrefix}showDialogPass() {
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
                
                if(strNvoPass != strNvoPassConf){
                    alert(\"La confirmacion de la nueva contrasena es erronea\");
                    return false;
                }else{
                    dojo.xhrPost({
                        url: base_url+'objects',
                        content: {
                            'data[password]':hex_md5(document.getElementById('{$domPrefix}password[0]').value),\r\n
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
        
        $out.="</script>
        ";
        return $out;
    }
}
