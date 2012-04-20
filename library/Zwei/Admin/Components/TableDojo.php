<?php

/**
 * Tabla HTML Dojo para un Dojo Layout, Interfaz para operaciones CRUD
 *
 * Ejemplo:
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

class Zwei_Admin_Components_TableDojo extends Zwei_Admin_Controller
{
	public $page;
    private $_version = 9;//actualizar para forzar update de javascript [TODO] hacer administrable

	/**
	 * Despliegue para mostrar listados
	 * @return HTML
	 */
	function display()
	{
		$form = new Zwei_Utils_Form();
		$request = array();
		foreach (get_object_vars($form) as $var=>$val){
			$request[$var] = $val;
		}

		$start = isset($request['start']) ? (int)$request['start'] : 0;
		$search = isset($request['search']) ? $request['search'] : "";

		$viewtable = new Zwei_Admin_Components_Helpers_ViewTableDojo($this->page);
		$viewtable->getLayout();
		//Zwei_Utils_Debug::write($viewtable->layout);
		$out = "<h2>{$viewtable->layout[0]['NAME']}</h2>\r\n";
		if(!empty($viewtable->layout[0]['JS'])) $out.="<script type=\"text/javascript\" src=\"".BASE_URL."js/".$viewtable->layout[0]['JS']."?version={$this->_version}\"></script>";
		$out .= "
        <div id=\"content_dojo\" style=\"width:100%\">\r\n";

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

		$params = $this->getRequested_params();

		$out .= $viewtable->display();
		$out .= "\r\n<table align=\"center\"><tr>";

		if ($viewtable->layout[1]['_name'] == 'TAB') {
			if(isset($viewtable->layout[0]['ADD']) && $viewtable->layout[0]['ADD'] == "true" && $this->_acl->isUserAllowed($this->page, 'ADD')){
				$out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconNewTask\" id=\"btnNuevoUsr\" onClick=\"cargarTabsPanelCentral('$this->page','add');\">";
				$out .= "Agregar ".$viewtable->layout[0]['NAME'];
				$out .= "</button></td>";
			}

			if (isset($viewtable->layout[0]['EDIT']) && $viewtable->layout[0]['EDIT'] == "true"  && $this->_acl->isUserAllowed($this->page, 'EDIT')){
				$out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconEdit\" id=\"btnEditarUsr\" onClick=\"cargarTabsPanelCentral('$this->page','edit');\">";
				$out .= "Editar ".$viewtable->layout[0]['NAME'];
				$out .= "</button></td>";
			}

		} else {
			if (isset($viewtable->layout[0]['ADD']) && $viewtable->layout[0]['ADD'] == "true" && $this->_acl->isUserAllowed($this->page, 'ADD')){
				$out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconNewTask\" id=\"btnNuevoUsr\" onClick=\"showDialog('add');\">";
				$out .= "Agregar ".$viewtable->layout[0]['NAME'];
				$out .= "</button></td>";
			}

			if (isset($viewtable->layout[0]['EDIT']) && $viewtable->layout[0]['EDIT'] == "true"  && $this->_acl->isUserAllowed($this->page, 'EDIT')){
				$out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconEdit\" id=\"btnEditarUsr\" onClick=\"showDialog('edit');\">";
				$out .= "Editar ".$viewtable->layout[0]['NAME'];
				$out .= "</button></td>";
			}
		}

		if(isset($viewtable->layout[0]['CHANGE_PASSWORD']) && $viewtable->layout[0]['CHANGE_PASSWORD'] == "true"  && $this->_acl->isUserAllowed($this->page, 'EDIT')){
			$out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconEdit\" id=\"btnPswd\" onClick=\"showDialogPass();\">";
			$out .= "Cambiar Contrase&ntilde;a";
			$out .= "</button></td>";
		}


		if (isset($viewtable->layout[0]['DELETE']) && $viewtable->layout[0]['DELETE'] == "true" && $this->_acl->isUserAllowed($this->page, 'DELETE')) {
			$out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconDelete\" id=\"btnEliminarUsr\" onClick=\"eliminar('{$viewtable->layout[0]['TARGET']}');\">";
			$out .= "Eliminar ".$viewtable->layout[0]['NAME'];
			$out .= "</button></td>";
		}

		if (isset($viewtable->layout[0]['EXCEL']) && $viewtable->layout[0]['EXCEL'] == "true") {
			$out .= "<td>";
			if(@$viewtable->layout[0]['SEARCH_TYPE']=='multiple'){
				$out .= "<button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconEdit\" id=\"btnExport\" onClick=\"searchMultiple('{$viewtable->layout[0]['TARGET']}', $viewtable->search_in_fields, $viewtable->search_format, $viewtable->between, 'excel', '$this->page');\">";
			}else{
				$out .= "<button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconEdit\" id=\"btnExport\" onClick=\"cargarDatos('{$viewtable->layout[0]['TARGET']}', $viewtable->search_in_fields, $viewtable->format_date, $viewtable->search_format, $viewtable->between, 'excel', '$this->page');\">";
			}
			$out .= "Exportar a Excel";
			$out .= "</button></td>";
		}

		if (isset($viewtable->layout[0]['FUNCTIONS'])) {
			$CustomFunctions = new Zwei_Utils_CustomFunctions();
			$params = '';
			$component = $this->page;
			$functions = explode(";",(@$viewtable->layout[0]['FUNCTIONS']));
			$permissions = explode(";",(@$viewtable->layout[0]['FUNCTIONS_PERMISSIONS']));
			$i = 0;
			foreach ($functions as $f) {
				//Zwei_Utils_Debug::write($permissions[$i]);
				if(empty($permissions[$i]) || $this->_acl->isUserAllowed($this->page, strtoupper($permissions[$i]))){
					$foo=Zwei_Utils_String::toFunctionWord($f);
					$out.="<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"{$CustomFunctions->getIcon()}\" id=\"btn$foo\" onClick=\"execFunction('$f', '$params','$component');\">";
					$out.=$CustomFunctions->getName($foo);
					$out.="</button></td>";
				}
				$i++;
			}
		}
		$permissions = false;
		if (isset($viewtable->layout[0]['LINKS'])) {
			$CustomFunctions = new Zwei_Utils_CustomFunctions();
			$params = '';
			$model = $this->page;
			$items = explode(";",(@$viewtable->layout[0]['LINKS']));
			$permissions = explode(";",(@$viewtable->layout[0]['LINKS_PERMISSIONS']));
			$titles = explode(";",(@$viewtable->layout[0]['LINKS_TITLE']));
			$i=0;
			foreach ($items as $f) {
				//Zwei_Utils_Debug::write($permissions[$i]);
				if (empty($permissions[$i]) || $this->_acl->isUserAllowed($this->page, strtoupper($permissions[$i]))) {
					$out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"{$CustomFunctions->getIcon()}\" id=\"btnlink$i\" onClick=\"redirectToModule('$i');\">";
					$out .= $CustomFunctions->getName($foo);
					$out .= "</button></td>";
				}
				$i++;
			}
		}

		$permissions = false;


		if (isset($viewtable->layout[0]['POPUPS'])) {
			$CustomFunctions = new Zwei_Utils_CustomFunctions();
			$params = '';
			$model = $this->page;
			$items = explode(";",(@$viewtable->layout[0]['POPUPS']));
			$permissions = explode(";",(@$viewtable->layout[0]['POPUPS_PERMISSIONS']));
			$titles = explode(";",(@$viewtable->layout[0]['POPUPS_TITLE']));
			$iframes = explode(";",(@$viewtable->layout[0]['POPUPS_IFRAME']));
			$i = 0;
			foreach ($items as $f) {
				//$href=str_replace("{id}",$this->params['ID'],$f);
				//$href=str_replace("{value}",$this->value,$href);
				$sIframe = (!empty($iframes[$i]) && $iframes[$i]=="true") ? 'true' : 'false';
				//Zwei_Utils_Debug::write($permissions[$i]);
				if (empty($permissions[$i]) || $this->_acl->isUserAllowed($this->page, strtoupper($permissions[$i]))) {
					$out .= "<td><button type=\"button\" dojoType=\"dijit.form.Button\" iconClass=\"{$CustomFunctions->getIcon()}\" id=\"btnlink$i\" onClick=\"popupGrid('$f', $sIframe);\">";
					$out .= $titles[$i];
					$out .= "</button></td>";
				}
				$i++;
			}
		}


		$out .= "</tr></table>\r\n";

		if ((isset($viewtable->layout[0]['ADD']) && $viewtable->layout[0]['ADD'] == 'true')
		&& ($this->_acl->isUserAllowed($this->page, 'ADD') || $this->_acl->isUserAllowed($this->page, 'EDIT'))
		&& $viewtable->layout[1]['_name']!='TAB')
		{
			 
			$out .= "<div dojoType=\"dijit.Dialog\" id=\"formDialogo\" title=\"Agregar {$viewtable->layout[0]['NAME']};\" execute=\"modify('{$viewtable->layout[0]['TARGET']}',arguments[0]);\">\r\n";
			$out .= "\t".$edittable->display('ADD');
			$out .= "\n</div>\r\n";

		}

		if ((isset($viewtable->layout[0]['EDIT']) && $viewtable->layout[0]['EDIT']=='true')
		&& ($this->_acl->isUserAllowed($this->page, 'EDIT'))
		&& $viewtable->layout[1]['_name']!='TAB')
		{
			 
			$out .= "<div dojoType=\"dijit.Dialog\" id=\"formDialogoEditar\" title=\"Agregar {$viewtable->layout[0]['NAME']};\" execute=\"modify('{$viewtable->layout[0]['TARGET']}',arguments[0]);\">\r\n";
			$out .= "\t".$edittable->display('EDIT');
			$out .= "\n</div>\r\n";
		}

		$out .= "</div>\r\n";
		$out .="<div id=\"output_grid\"></div>";


		if (@$viewtable->layout[0]['CHANGE_PASSWORD']=='true' && $this->_acl->isUserAllowed($this->page,'EDIT')) {
			$out .= "<div dojoType=\"dijit.Dialog\" id=\"formPassword\" title=\"Cambio de password\" execute=\"changePassword('{$viewtable->layout[0]['TARGET']}',arguments[0]);\">\r\n";
			$out .= "<br/><br/>\r\n";
			$out .= "
                <table style=\"border: 1px solid #0066cc;\" cellspacing=\"10\" align=\"center\">
                    <tr>
                        <td>
                            <label for=\"txtNvoPass\">Nueva contrase&ntilde;a</label>
                        </td>
                        <td>
                            <input type=\"password\" name=\"txtNvoPass\" placeHolder=\"Ingresar nueva contrase&ntilde;a\" dojoType=\"dijit.form.ValidationTextBox\"
                                   trim=\"true\" required=\"true\" id=\"password[0]\" pwType=\"new\"  />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for=\"txtNvoPassConf\">Re-ingrese contrase&ntilde;a</label>
                        </td>
                        <td>
                            <input type=\"password\" name=\"txtNvoPassConf\" placeHolder=\"Confirmar nueva contrase&ntilde;a\" dojoType=\"dijit.form.ValidationTextBox\"
                                   trim=\"true\" required=\"true\" id=\"password_confirm[0]\" pwType=\"verify\" />
                        </td>
                    </tr>
                    <tr>
                        <td colspan=\"2\" align=\"center\">
                            <button type=\"submit\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconSave\" id=\"btnGuardarDatosPass\"
                                    onClick=\"return dijit.byId('formPassword').validate();\">
                                Guardar Contrase&ntilde;a
                            </button>
                        </td>

                    </tr>
                </table>\r\n";
			$out.="</div>";
		}

		$out .= "<input type=\"hidden\" id=\"data_url\" value=\"\" />";

		if (!empty($viewtable->layout[0]['JS'])) {
			$functionInit = str_replace('.js','', $viewtable->layout[0]['JS']).'Init';
			$out .= "
            <script type=\"text/javascript\">
            $functionInit();
            </script>
            ";
		}

		return $out;
	}
}
