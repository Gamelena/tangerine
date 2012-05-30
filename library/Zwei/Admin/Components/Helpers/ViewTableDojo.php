<?php
/**
 * Auxiliar para Zwei_Admin_Components_Table_Dojo, CRUD en modo listar
 *
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Components
 * @version $Id:$
 * @since 0.1
 *
 */

class Zwei_Admin_Components_Helpers_ViewTableDojo extends Zwei_Admin_Controller
{
    public $search_in_fields;
    public $format_date;
    public $search_format;
    public $between;
    private $count;

    public function display()
    {
        $userInfo = Zend_Auth::getInstance()->getStorage()->read();
        $this->_acl = new Zwei_Admin_Acl($userInfo->user_name);

        $this->count = count($this->layout);

        $out = '';

        if (isset($this->layout[0]['SEARCH']) && $this->layout[0]['SEARCH'] != 'false' && $this->_acl->isUserAllowed($this->page, 'LIST'))
        {
            if (@$this->layout[0]['SEARCH_TYPE'] == 'multiple') {
                $out .= $this->searchMultiple();
            }else{
                $out .= $this->searcher();
            }
        }

        if (isset($this->layout[0]['SEARCH_TABLE']) && @$this->layout[0]['SEARCH_TYPE'] != 'multiple' && $this->_acl->isUserAllowed($this->page, 'LIST')) {
            $out .= $this->searchInTable();
        }

        $params=$this->getRequested_params();

        if (isset($this->layout[0]['LIST']) && $this->layout[0]['LIST']=="true" && $this->_acl->isUserAllowed($this->page, 'LIST') && (!isset($this->layout[0]['SEARCH_HIDE_SUBMIT']))) {
            $out .= "\r\n <span dojoType=\"dojo.data.ItemFileReadStore\" id=\"store_grid\" jsId=\"store_grid\" url=\"".BASE_URL."objects?model={$this->layout[0]['TARGET']}&format=json$params\"></span>";
            $store = "store=\"store_grid\"";
        } else {
            $store = '';
        }

        $count = count($this->layout);
        $width_col = 120;
        $width_table = 0;

        for ($i=1; $i<$count; $i++) {
            if (isset($this->layout[$i]['VISIBLE']) && $this->layout[$i]['VISIBLE'] == "true") {
                $width_table += (isset($this->layout[$i]['WIDTH'])) ? $this->layout[$i]['WIDTH'] : $width_col;
            }
        }
        $width_table += 40;

        $dojotype = @$this->layout[0]['TABLE_DOJO_TYPE'] ? "dojoType=\"{$this->layout[0]['TABLE_DOJO_TYPE']}\"" : "dojoType=\"dojox.grid.EnhancedGrid\"";
        $plugins = @$this->layout[0]['PLUGINS'] ? "plugins=\"{$this->layout[0]['PLUGINS']}\"" : "plugins=\"{pagination: {defaultPageSize:25, maxPageStep: 5 } }\"";

        if (!isset($this->layout[0]['SEARCH_HIDE_SUBMIT'])) {
            $out .= "\r\n<table $dojotype $plugins id=\"main_grid\" jsId=\"main_grid\" $store clientSort=\"true\" style=\"width:{$width_table}px; height: 320px;\" selectable=\"true\" rowSelector=\"20px\" rowsPerPage=\"10\" noDataMessage=\"Sin datos.\">\r\n<thead><tr>\r\n";
    
            for ($i=1; $i<$count; $i++) {
                $target = (!isset($this->layout[$i]['FIELD'])) ? @$this->layout[$i]['TARGET'] : $this->layout[$i]['FIELD'];
                $formatter = isset($this->layout[$i]['TYPE']) && $this->layout[$i]['TYPE'] == 'dojo_yes_no' ? "formatter=\"formatYesNo\"":'';
                if(isset($this->layout[$i]['FORMATTER'])) $formatter = "formatter=\"{$this->layout[$i]['FORMATTER']}\"";
                $width = (isset($this->layout[$i]['WIDTH'])) ? $this->layout[$i]['WIDTH'] : $width_col;
    
                if (isset($this->layout[$i]['VISIBLE']) && $this->layout[$i]['VISIBLE'] == "true") {
                    $out .= "\t\t<th field=\"$target\" editable=\"false\" width=\"{$width}px\" $formatter>". str_replace('\\n', '<br/>', $this->layout[$i]['NAME']) ."</th>\r\n";
                }
            }
            $out .= "\t</tr>\r\n</thead></table>\r\n";
        }
        if(isset($form->id)){
            $out.="<input type=\"hidden\" name=\"id\" id=\"id\" value=\"$form->id\">\r\n";
        }
        
        return $out;
    }

    /**
     * Generación del buscador
     * @return HTML
     */
    private function searcher()
    {
        $out = "";
        $this->format_date = (@$this->layout[0]['SEARCH_DOJO_TYPE']=="dijit.form.DateTextBox")? 'true' : 'false';
        $this->search_format = (@$this->layout[0]['SEARCH_FORMAT'])? $this->layout[0]['SEARCH_FORMAT'] : 'false';
        $this->between = 'false';
        $this->search_in_fields='false';
        $dojotype = @$this->layout[0]['SEARCH_DOJO_TYPE'] ? "dojoType=\"{$this->layout[0]['SEARCH_DOJO_TYPE']}\"" : "dojoType=\"dijit.form.ValidationTextBox\"";
        $constrains = @$this->layout[0]['SEARCH_CONSTRAINTS']? "constraints=\"{$this->layout[0]['SEARCH_CONSTRAINTS']}\"" : '';
        $invalid_message = @$this->layout[0]['SEARCH_INVALID_MESSAGE']? "invalidMessage=\"{$this->layout[0]['SEARCH_INVALID_MESSAGE']}\"" : '';
        $prompt_message = @$this->layout[0]['SEARCH_PROMPT_MESSAGE']? "promptMessage=\"{$this->layout[0]['SEARCH_PROMPT_MESSAGE']}\"" : '';
        $required = @$this->layout[0]['SEARCH_REQUIRED'] == "true"? "required=\"true\"" : '';

        if (@$this->layout[0]['SEARCH_DISPLAY']=='between') {
            $label1 = "Desde";
            $label2 = "Hasta";
        }else{
            $label1 = "Buscar";
        }

        $out .= "<div dojoType=\"dijit.form.Form\" id=\"search_form\" jsId=\"search_form\" encType=\"multipart/form-data\" action=\"\" method=\"\">\r\n";
        $out .= "<table cellspacing=\"10\" align=\"center\">\r\n";
        $out .= "<tr><td><label for=\"search\">$label1</label></td>";
        $out .= "<td><input type=\"text\" name=\"search\" placeHolder=\"Ingresar\" $dojotype trim=\"true\" id=\"search\" $constrains $invalid_message $prompt_message $required /></td></tr>\r\n";

        if($this->layout[0]['SEARCH'] != "true")
        {
            if(!isset($this->layout[0]['SEARCH_DISPLAY'])) $out.="<tr><td colspan=\"2\" style=\"text-align:center;\">";
            if(explode(";",$this->layout[0]['SEARCH'])){
                $fields = explode(";", $this->layout[0]['SEARCH']);
            }else{
                $fields = array($fields);
            }


            //buscamos los títulos de los parametros "search" en el xml
            $j = 0;
            for($i=1; $i<$this->count; $i++)
            {
                if(in_array(@$this->layout[$i]['FIELD'], $fields) && $this->layout[$i]['TYPE'] != 'pk_original')
                {
                    $checked = ($j==0) ? "checked" : "";
                    $out.="\t\t<input dojoType=\"dijit.form.RadioButton\" id=\"search_fields[$j]\" name=\"search_fields\" $checked value=\"{$this->layout[$i]['FIELD']}\" type=\"radio\" /><label for=\"{$this->layout[$i]['FIELD']}\">{$this->layout[$i]['NAME']}</label>\r\n";

                    if(isset($this->layout[$i]['SEARCH_FORMAT']))
                    {
                        $out.= "<input type=\"hidden\" id=\"search_format[$j]\" name=\"search_format[$j]\" value=\"{$this->layout[$i]['SEARCH_FORMAT']}\">\r\n";
                    }
                    $j++;
                }
                else if(in_array($this->layout[$i]['TARGET'], $fields)  && $this->layout[$i]['TYPE'] != 'pk_original')
                {
                    $checked = ($j==0) ? "checked" : "";
                    $out.="\t\t<input dojoType=\"dijit.form.RadioButton\" id=\"search_fields[$j]\" name=\"search_fields\" $checked value=\"{$this->layout[$i]['TARGET']}\" type=\"radio\" /><label for=\"{$this->layout[$i]['TARGET']}\">{$this->layout[$i]['NAME']}</label>\r\n";

                    if(isset($this->layout[$i]['SEARCH_FORMAT']))
                    {
                        $out.= "<input type=\"hidden\" id=\"search_format[$j]\" name=\"search_format[$j]\" value=\"{$this->layout[$i]['SEARCH_FORMAT']}\">\r\n";
                    }
                    $j++;
                }
            }
            $out .= "<div style=\"display:none\"><input dojoType=\"dijit.form.RadioButton\" id=\"search_fields[$j]\" name=\"search_fields\" value=\"\" type=\"radio\" /></div>";
            if ($j <= 1) $out .= "<script>function hideRadio(){dijit.byId('search_fields[0]').domNode.style.display='none';} setTimeout('hideRadio()', 1000)</script>";
            if (!isset($this->layout[0]['SEARCH_DISPLAY'])) $out.="</td></tr>\r\n";
            $this->search_in_fields='true';
        }

        if (@$this->layout[0]['SEARCH_DISPLAY']=='between') {
            $out .= "<tr><td><label for=\"search2\">$label2</label></td>";
            $out .= "<td><input type=\"text\" name=\"search2\" placeHolder=\"Ingresar\" $dojotype trim=\"true\" id=\"search2\" $constrains $invalid_message $prompt_message/></td></tr>\r\n";
            $this->search_in_fields = 'true';
            $this->between = 'true';
        }

        if (isset($this->layout[0]['SEARCH_ADDITIONAL_VALIDATION'])) {
            $modelname = Zwei_Utils_String::toClassWord($this->layout[0]['TARGET'])."Model";
            $Model = new $modelname();
            $additional_validation = (isset($this->layout[0]['SEARCH_ADDITIONAL_VALIDATION'])) ? $Model->getSearchValidation() :'';
        } else {
            $additional_validation = '';
        }

        $out .= "<tr><td colspan=\"2\" align=\"center\">";
        $out .= "<button type=\"submit\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconSearch\" id=\"btnBuscar\">Buscar</button>";
        $out .= "</td></tr>";
        $out .= "</table>\r\n";
        $out .= "
            <script type=\"dojo/method\" event=\"onSubmit\">
                if (this.validate()) {
                $additional_validation
                    cargarDatos('{$this->layout[0]['TARGET']}', $this->search_in_fields, $this->format_date, $this->search_format, $this->between);
                    return false;
                } else {
                    alert('Por favor corrija los campos marcados.');
                    return false;
                }
                return true;
            </script>\r\n";             
                $out .="</div>\n<br/>\r\n";
                return $out;

    }


    private function searchMultiple()
    {
        Debug::write("Search Multiple");
        
        if(isset($this->layout[0]['SEARCH_ADDITIONAL_VALIDATION'])){
            $modelname=Zwei_Utils_String::toClassWord($this->layout[0]['TARGET'])."Model";
            $Model=new $modelname();
            $additional_validation=(isset($this->layout[0]['SEARCH_ADDITIONAL_VALIDATION'])) ? $Model->getSearchValidation() :'';
        }else{
            $additional_validation='';
        }

        $this->format_date = (@$this->layout[0]['SEARCH_DOJO_TYPE']=="dijit.form.DateTextBox")? 'true' : 'false';
        $this->search_format = (@$this->layout[0]['SEARCH_FORMAT'])? $this->layout[0]['SEARCH_FORMAT'] : 'false';
        $this->between = 'false';
        $this->search_in_fields='false';
        $node = @$this->layout[0];
        $fields = explode(";", $this->layout[0]['SEARCH']);
        $search = explode(";",$node['SEARCH']);
        $search_display=explode(";",@$node['SEARCH_DISPLAY']);
        $dojotype = explode(";",@$node['SEARCH_DOJO_TYPE']);
        $betweened = false;
        $out = "";

        if (!empty($node['SEARCH_TABLE'])) {
            $search_table = explode(";", $node['SEARCH_TABLE']);
            if (!is_array($search_table)) $search_table = array($node['SEARCH_TABLE']);

            $search_table_pk = explode(";", @$node['SEARCH_TABLE_PK']);
            if (!is_array($search_table_pk)) $search_table_pk = array(@$node['SEARCH_TABLE_PK']);

            $search_table_field = explode(";", @$node['SEARCH_TABLE_FIELD']);
            if (!is_array($search_table_field)) $search_table_field = array(@$node['SEARCH_TABLE_FIELD']);

            $search_table_target = explode(";", $node['SEARCH_TABLE_TARGET']);
            if (!is_array($search_table_target)) $search_table_target = array($node['SEARCH_TABLE_TARGET']);

            $fields = array_merge($fields, $search_table_target);
        } else {
            $node['SEARCH_TABLE'] = '';
            $search_table = '';
        }
        $out .= "<div dojoType=\"dijit.form.Form\" id=\"search_form\" jsId=\"search_form\" encType=\"multipart/form-data\" action=\"\" method=\"\">\r\n";
        $out .= "<table cellspacing=\"10\" align=\"center\">\r\n";


        $label = array();
        $constraints = array();
        $required = array();
        $invalid_message = array();
        $prompt_message = array();
        $search_format = '';

        for ($i=1; $i<$this->count; $i++) {
            //Buscamos si el campo de busqueda del nodo 0 está declarado en algunos nodos hijos para asociar otras propiedades auxiliares
            if (in_array(@$this->layout[$i]['FIELD'], $fields) || in_array(@$this->layout[$i]['TARGET'], $fields) && $this->layout[$i]['TYPE'] != 'pk_original') {
                $label[] = $this->layout[$i]['NAME'];
                $constraints[] = !empty($this->layout[$i]['SEARCH_CONSTRAINTS'])?  "constraints=\"{$this->layout[$i]['SEARCH_CONSTRAINTS']}\"" : "";
                $required[] = !empty($this->layout[$i]['SEARCH_REQUIRED'])?  "required=\"{$this->layout[$i]['SEARCH_REQUIRED']}\"" : "";
                $invalid_message[] = !empty($this->layout[$i]['SEARCH_INVALID_MESSAGE'])?  "invalidMessage=\"{$this->layout[$i]['SEARCH_INVALID_MESSAGE']}\"" : "";
                $prompt_message[] = !empty($this->layout[$i]['SEARCH_PROMPT_MESSAGE'])?  "promptMessage=\"{$this->layout[$i]['SEARCH_PROMPT_MESSAGE']}\"" : "";
                $search_onchange[] = !empty($this->layout[$i]['SEARCH_ONCHANGE'])?  "onchange=\"{$this->layout[$i]['SEARCH_ONCHANGE']}\"" : "";
                $between = !empty($this->layout[$i]['BETWEEN'])?  "'{$this->layout[$i]['SEARCH_ONCHANGE']}'" : 'false';

                if (!empty($this->layout[$i]['SEARCH_FORMAT'])) {
                    $search_format .= $this->layout[$i]['SEARCH_FORMAT'].';';
                } else {
                    $search_format .= 'null;';
                }
            }
        }
        //Zwei_Utils_Debug::write($constraints);

        $i=0;
        foreach ($search as $s){

            $dojotype[$i]=(empty($dojotype[$i]) || $dojotype[$i]=='null') ? 'dojoType="dijit.form.ValidationTextBox"': 'dojoType="'.$dojotype[$i].'"';
            $current_label=(@$search_display[$i]=='between') ? 'Desde' : $label[$i];
            $out .="<tr><td><label for=\"search\">$current_label</label></td>";

            $out .="<td><input type=\"text\" name=\"search$i\" placeHolder=\"Ingresar\" ".@$dojotype[$i]." trim=\"true\" id=\"search$i\" ".$constraints[$i].@$invalid_message[$i].@$prompt_message[$i].@$required[$i]." onchange=\"loadDataUrl('{$this->layout[0]['TARGET']}', '{$node['SEARCH']};".@$node['SEARCH_TABLE_TARGET']."', '$search_format',  $between)\"  /></td></tr>\r\n";

            if (@$search_display[$i]=='between') {
                $j=$i+1;
                $out.="<tr><td><label for=\"search$i\">Hasta</label></td>";
                $out.="<td><input type=\"text\" name=\"search$j\" placeHolder=\"Ingresar\" ".@$dojotype[$i]." trim=\"true\" id=\"search$j\" ".$constraints[$i].@$invalid_message[$i].@$prompt_message[$i].@$required[$i]." onchange=\"loadDataUrl('{$this->layout[0]['TARGET']}', '{$node['SEARCH']};".@$node['SEARCH_TABLE_TARGET']."', '$search_format',  $between)\" /></td></tr>\r\n";

                $this->search_in_fields='true';
                $this->between="'$s'";
                $i=$j;
                $betweened=true;
            } 
            $i++;
        }

        $j=0;
        if (is_array($search_table)) {
            foreach ($search_table as $s) {
                //si está "betweened" $i se ha incrementado 2 veces, uno por cada input "desde" "hasta"
                $auxI = ($betweened) ? $i-1 : $i;
                $current_label = $label[$auxI];
                $onchange=(!empty($search_onchange[$auxI])) ? $search_onchange[$auxI] : '';
                //$search_name=!empty($node['SEARCH_NAME'])?$node['SEARCH_NAME']:'';
                $out .= "<tr><td><label for=\"search\">$current_label</label></td>";
                $out .= "<td>
                <select id=\"search$i\" name=\"search$i\" dojoType=\"dijit.form.FilteringSelect\" $onchange onLoad=\"dijit.byId('search$i').set('value', dijit.byId('search$i').get('value'))\" onchange=\"loadDataUrl('{$this->layout[0]['TARGET']}', '{$node['SEARCH']};".@$node['SEARCH_TABLE_TARGET']."', '$search_format',  $between)\"  >";

                $ClassModel = Zwei_Utils_String::toClassWord($s)."Model";
                $Model = new $ClassModel();
                $select = $Model->select();
                //Zwei_Utils_Debug::write($select->__toString());
                $result = $Model->fetchAll($select);
                $table_pk = (!empty($search_table_pk[$j])) ? $search_table_pk[$j] :'id';
                $table_field = (!empty($search_table_field[$j])) ? $search_table_field[$j] :'title';
                //$table_target = (!empty($search_table_target[$j])) ? $search_table_target[$j] :$s."_".$search_table_pk;


                if(trim($required[$auxI])=='required="false"'){
                    // como es string vacío se ignora este filtro
                    $out .= "<option value=\"\">Todo</option>";
                }

                foreach ($result as $v){
                    $out .= "<option value=\"{$v[$table_pk]}\">{$v[$table_field]}</option>";
                }

                $out .="</select></td></tr>\r\n";
                $j++;
                $i++;
            }
        }
        $this->search_in_fields="'".$node['SEARCH'].";".@$node['SEARCH_TABLE_TARGET']."'";
        $this->search_format="'$search_format'";
        
        if (!isset($node['SEARCH_HIDE_SUBMIT']) || $node['SEARCH_HIDE_SUBMIT'] == 'false') {
            $out .="<tr><td colspan=\"2\" align=\"center\">";
            $out .="<button type=\"submit\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconSearch\" id=\"btnBuscar\">Buscar</button>";
            $out .="</td></tr>";
        }
        
        $out .="</table>\r\n";
        $out .="
            <script type=\"dojo/method\" event=\"onSubmit\">
                if (this.validate()) {
                $additional_validation
                    searchMultiple('{$node['TARGET']}','{$node['SEARCH']};".@$node['SEARCH_TABLE_TARGET']."', '{$search_format}' , $this->between);
                    return false;
                } else {
                    alert('Por favor corrija los campos marcados.');
                    return false;
                }
                return true;
            </script>\r\n";             
                $out .="</div>\n<br/>\r\n";
                return $out;
    }


    /**
     * Genera un Combo asociado a una tabla para filtrar el listado
     * @return HTML
     */
    private function searchInTable() 
    {
        Zwei_Utils_Debug::write("Search in Table");
        
        $out = "";
        $node=@$this->layout[0];
        $this->format_date = (@$this->layout[0]['SEARCH_DOJO_TYPE']=="dijit.form.DateTextBox")? 'true' : 'false';
        $this->search_format = (@$this->layout[0]['SEARCH_FORMAT'])? $this->layout[0]['SEARCH_FORMAT'] : "'equals'";
        $this->between = 'false';
        $this->search_in_fields='false';

        if (isset($this->layout[0]['SEARCH_ADDITIONAL_VALIDATION'])) {
            $modelname=Zwei_Utils_String::toClassWord($this->layout[0]['TARGET'])."Model";
            $Model=new $modelname();
            $additional_validation=(isset($this->layout[0]['SEARCH_ADDITIONAL_VALIDATION'])) ? $Model->getSearchValidation() :'';
        }
        else {
            $additional_validation='';
        }

        $required= @$node['SEARCH_REQUIRED']=="true"? "required=\"true\"" : '';

        $out .="<div dojoType=\"dijit.form.Form\" id=\"search_form\" jsId=\"search_form\" encType=\"multipart/form-data\" action=\"\" method=\"\">\r\n";
        $out .="<table cellspacing=\"10\" align=\"center\">\r\n";
        $search_name =! empty($node['SEARCH_NAME'])?$node['SEARCH_NAME']:'';//[TODO] search_name debiera leerse de los elements xml
        $out .="<tr><td><label for=\"search\">$search_name</label></td>";


        $out .="<td>
        <select id=\"search\" name=\"search\" dojoType=\"dijit.form.FilteringSelect\">";
        $ClassModel = Zwei_Utils_String::toClassWord($node['SEARCH_TABLE'])."Model";
        $Model = new $ClassModel();
        $select = $Model->select();
        $result = $Model->fetchAll($select);
        $search_table_pk = (isset($node['SEARCH_TABLE_PK'])) ? $node['SEARCH_TABLE_PK'] :'id';
        $search_table_field = (isset($node['SEARCH_TABLE_FIELD'])) ? $node['SEARCH_TABLE_FIELD'] :'title';
        $search_table_target = (isset($node['SEARCH_TABLE_TARGET'])) ? $node['SEARCH_TABLE_TARGET'] :$node['SEARCH_TABLE']."_".$search_table_pk;
        $this->search_in_fields = "'$search_table_target'";
        
        foreach ($result as $v){
            $out.="<option value=\"{$v[$search_table_pk]}\">{$v[$search_table_field]}</option>";
        }

        $out .="</select></td></tr>\r\n";
        $out .="<tr><td colspan=\"2\" align=\"center\">";
        $out .="<button type=\"submit\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconSearch\" id=\"btnBuscar\">Buscar</button>";


        $out .="</td></tr>";
        $out .="</table>\r\n";
        $out .="
            <script type=\"dojo/method\" event=\"onSubmit\">
                if (this.validate()) {
                $additional_validation
                    searchMultiple('{$node['TARGET']}','$search_table_target', 'equals' , false);
                    return false;
                } else {
                    alert('Por favor corrija los campos marcados.');
                    return false;
                }
                return true;
            </script>\r\n";      
                $out .="</div>\n<br/>\r\n";
                return $out;
    }
}

