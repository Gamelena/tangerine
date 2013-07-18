<?php

/**
 * Interfaz para operaciones CRUD
 *
 * @example
 * @package Components
 * @version 2013-05-09
 * @since 1.0
 * @author rodrigo.riquelme@zweicom.com
 *
 * <code>
 * <?xml version="1.0"?> 
 * <component xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 * xsi:noNamespaceSchemaLocation="components.xsd" 
 * name="Usuarios" type="dojo-simple-crud" target="AclUsersModel" list="true"
 * edit="true" add="true" delete="true">
 *     <elements>
 *         <element name="ID" target="id" type="id-box" visible="false"
 * edit="false" add="false"/>
 *         <element name="Usuario" target="user_name"
 * type="dijit-form-validation-text-box" visible="true" edit="false" add="true"/>
 *         <element name="Nombres" target="first_names"
 * type="dijit-form-validation-text-box" visible="true" edit="true" add="true"/>
 *         <element name="Apellidos" target="last_names"
 * type="dijit-form-validation-text-box" visible="true" edit="true" add="true"/>
 *         <element name="E-Mail" target="email"
 * regExp="[\w-\.]{3,}@([\w-]{2,}\.)*([\w-]{2,}\.)[\w-]{2,4}" invalidMessage="mail
 * no valido" type="dijit-form-validation-text-box" visible="true" edit="true"
 * add="true" />
 *         <element name="Perfil" target="acl_roles_id" defaultValue=""
 * type="dijit-form-filtering-select" table="AclRolesModel" field="role_name"
 * visible="true" edit="true" add="true"/>
 *         <element name="Activo" target="approved" type="dijit-form-check-box"
 * formatter="formatYesNo" visible="true" edit="true" add="true"/>
 *     </elements>
 *     <searchers>
 *         <group>
 *             <element target="user_name"/>
 *             <element target="first_names"/>
 *             <element target="last_names"/>
 *             <element target="email"/>
 *         </group>
 *         <element target="acl_roles_id" defaultText="Todo"/>
 *     </searchers>
 * </component>
 * </code>
 *
 *
 *
 *
 */

class Components_DojoSimpleCrudController extends Zend_Controller_Action
{

    /**
     * Objecto XML
     * @var Zwei_Admin_Xml
     */
    private $_xml = null;

    /**
     * Nombre archivo XML
     * @var string
     */
    private $_component = null;

    /**
     * Configuración global.
     * @var Zend_Config
     */
    private $_config = null;

    /**
     * Control de acceso a recursos.
     * @var Zwei_Admin_Acl
     */
    private $_acl = null;

    /**
     * Modelo sobre el cual se opera.
     * @var Zwei_Db_Table
     */
    private $_model = null;

    public function init()
    {
        if (!Zwei_Admin_Auth::getInstance()->hasIdentity()) $this->_redirect('admin/login');
        $userInfo = Zend_Auth::getInstance()->getStorage()->read();
        $this->_acl = new Zwei_Admin_Acl($userInfo->user_name);
        
        $configParams = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getApplication()->getOptions();
        $this->_config = new Zend_Config($configParams);
        
        if ($this->getRequest()->getParam('p')) {
            $this->_component = $this->getRequest()->getParam('p');
            $file = Zwei_Admin_Xml::getFullPath($this->_component);
            $this->_xml = new Zwei_Admin_Xml($file, 0, 1);
        }
        $this->view->mainPane = isset($this->_config->zwei->layout->mainPane) ? $this->_config->zwei->layout->mainPane : 'undefined';
        $this->view->domPrefix  = (isset($this->view->mainPane) && $this->view->mainPane == 'dijitTabs') ? Zwei_Utils_String::toVarWord($this->getRequest()->getParam('p')) : '';
    }

    public function indexAction()
    {
        $className = $this->_xml->getAttribute('target');
        $this->_model = new $className();
        
        $this->view->name = $this->_xml->getAttribute('name');
        $this->view->includeJs = $this->_xml->getAttribute('js') ? "<script src=\"".BASE_URL.'js/'.$this->_xml->getAttribute('js')."\"></script>" : '';
        if ($this->_xml->xpath("//forms")) $forms = $this->_xml->xpath("//forms");
        
        $this->view->styleDialog = $this->_xml->xpath("//forms[@style]") ? "style=\"{$forms[0]->getAttribute('style')}\"" : '';
        $this->view->onloadDialog = $this->_xml->xpath("//forms[@onload]") ? "onload=\"{$forms[0]->getAttribute('onload')}\"" : '';
        $this->view->onshowDialog = $this->_xml->xpath("//forms[@onshow]") ? "onshow=\"{$forms[0]->getAttribute('onshow')}\"" : '';
        $this->view->onhideDialog = $this->_xml->xpath("//forms[@onhide]") ? "onhide=\"{$forms[0]->getAttribute('onhide')}\"" : '';
        $this->view->ajax = $this->_xml->xpath("//forms[@ajax='true']") ? true : false;
        $this->view->changePassword = $this->_xml->xpath("//forms/changePassword") ? true : false;
        if ($this->view->changePassword) {
            $this->view->targetPass = 'password';
            $this->view->namePass = 'Contrase&ntilde;a';
            $this->view->primary = $this->_model->getPrimary();
        }
    }

    public function searchAction()
    {
        $this->view->p = $this->_component;
        $this->view->model = $this->_xml->getAttribute('target');
        $this->view->xml = $this->_xml;
        $this->view->groups = $this->_xml->getSearchers(true);
    }

    public function initForm($mode)
    {

        $r = $this->getRequest();
        $this->view->p = $this->_component;
        $this->view->xml = $this->_xml;
        $this->view->mode = $mode;
        $this->view->loadPartial = $r->getParam('loadPartial', false);
        $this->view->dialogIndex = $r->getParam('dialogIndex', '');

        $this->view->onPostSubmit = $this->_xml->xpath('//forms/onPostSubmit') ? dom_import_simplexml($this->_xml->forms->onPostSubmit)->textContent : '';
        $this->view->onSubmit = $this->_xml->xpath('//forms/onSubmit') ? dom_import_simplexml($this->_xml->forms->onSubmit)->textContent : '';

        $className = $this->_xml->getAttribute('target');
        $this->view->model = $className;
        $this->_model = new $className();
        $this->view->modelPrimary = $this->_model->info('primary');
        $this->view->tabs = $this->_xml->getTabsWithElements(true, "@$mode='true' or @$mode='readonly' or @$mode='disabled'");
        
        if (($mode == 'edit' || $mode == 'clone') && $this->view->ajax && !$this->view->loadPartial) {
            $a = $this->_model->getAdapter();
            
            $select = $this->_model->select();
            $primaries = $r->getParam('primary');

            foreach ($r->getParam('primary') as $i => $v) { 
                //Concatenar nombre de campo con tabla principal en caso de que campo exista en la tabla,
                //esto previene posibles errores de ambiguedad de nombre de campo cuando se use join
                if (in_array($i, $this->_model->info(Zend_Db_Table::COLS))) { 
                    $i = $this->_model->info(Zend_Db_Table::NAME) . ".$i";
                }
                $select->where($a->quoteInto($a->quoteIdentifier($i). " = ?", $v));
            }
            Zwei_Utils_Debug::writeBySettings($select->__toString(), "query_log");
            $data = $this->_model->fetchRow($select);
            //Es posible añadir más valores al retorno de la query principal sobrecargando este método.
            $this->view->data = $this->_model->overloadDataForm($data);
        }
        
    }

    public function editAction()
    {
        $ajax = $this->_xml->xpath("//forms[@ajax='true']") ? 'true' : 'false';
        $this->view->changePassword = $this->_xml->xpath("//forms/changePassword") ? true : false;
        if (!$ajax === 'false' && $this->_xml->xpath("//forms/edit[@ajax='true']")) $ajax = 'true';
        $this->view->ajax = $ajax === 'true' ? true : false;
        $this->initForm('edit');
    }

    public function addAction()
    {
        $this->initForm('add');
        $this->render('edit');
    }

    public function cloneAction()
    {
        $ajax = $this->_xml->xpath("//forms[@ajax='true']") ? 'true' : 'false';
        if (!$ajax === 'false' && $this->_xml->xpath("//forms/edit[@ajax='true']")) $ajax = 'true';
        $this->view->ajax = $ajax === 'true' ? true : false;
        $this->initForm('clone');
        $this->render('edit');
    }

    public function listAction()
    {
        $this->view->p = $this->_component;
        $this->view->model = $this->_xml->getAttribute('target');
        $this->view->dataDojoType = $this->_xml->getAttribute('serverPagination') === "true" ? 'dojox/data/QueryReadStore' : 'dojo/data/ItemFileReadStore';
        $this->view->gridDojoType = $this->_xml->getAttribute('gridDojoType') ? $this->_xml->getAttribute('gridDojoType') : 'dojox/grid/EnhancedGrid';
        $this->view->plugins = $this->_xml->getAttribute('plugins') ? $this->_xml->getAttribute('plugins') : "{pagination: {defaultPageSize:25, maxPageStep: 5 }}";
        $this->view->onRowClick = $this->_xml->getAttribute('onRowClick') ? "onRowClick:{$this->_xml->getAttribute('onRowClick')}," : "";
        $this->view->searchHideSubmit = $this->_xml->getAttribute('searchHideSubmit') === "true" ? true : false;
        $this->view->elements = $this->_xml->getElements("@visible='true'");
        
        $ajax = $this->_xml->xpath("//forms[@ajax='true']") ? 'true' : 'false';
        if (!$ajax === 'false' && $this->_xml->xpath("//forms/edit[@ajax='true']")) $ajax = 'true';
        
        if ($this->_acl->isUserAllowed($this->_component, 'EDIT')) {
            $this->view->onRowClick = "
                if (dijit.byId('{$this->view->domPrefix}btnEdit') != undefined) dijit.byId('{$this->view->domPrefix}btnEdit').set('disabled', false);
                if (dijit.byId('{$this->view->domPrefix}btnDelete') != undefined) dijit.byId('{$this->view->domPrefix}btnDelete').set('disabled', false);
                if (dijit.byId('{$this->view->domPrefix}btnChangePassword') != undefined) dijit.byId('{$this->view->domPrefix}btnChangePassword').set('disabled', false);
            ";
        }
        
        if ($this->_acl->isUserAllowed($this->_component, 'ADD')) {
            $this->view->onRowClick .= "
            if (dijit.byId('{$this->view->domPrefix}btnClone') != undefined) dijit.byId('{$this->view->domPrefix}btnClone').set('disabled', false);
            ";
        }
        
        if ($this->_xml->getAttribute('onRowClick')) {
            $this->view->onRowClick .= $this->_xml->getAttribute('onRowClick');
        }
        
        
        $this->view->onRowDblClick = '';
        if ($this->_xml->getAttribute('onRowDblClick')) {
            $this->view->onRowDblClick = $this->_xml->getAttribute('onRowDblClick');
        } else if ($this->_acl->isUserAllowed($this->_component, 'EDIT')) {
            $this->view->onRowDblClick = "var form = new zwei.Form({
                    ajax: $ajax,
                    component: '{$this->_component}',
                    action: 'edit',
                    dijitDialog: dijit.byId('{$this->view->domPrefix}dialog_edit'), 
                    dijitForm: dijit.byId('{$this->view->domPrefix}form_edit'), 
                    dijitDataGrid: dijit.byId('{$this->view->domPrefix}dataGrid')
                }); 
                form.showDialog()";
        }
        
        $numElements = count($this->view->elements);
        $widthCol = round((100/$numElements), 1) . "%";//Se le asigna a cada columna de la grilla un ancho proporcional a su cantidad en porcentaje.
        for ($i = 1; $i < $numElements; $i++) {
            $elements = $this->_xml->getElements();
            //Para cada columna se permite sobreescribir el ancho asignado por defecto, se sugiere trabajar con porcentajes para aprovechar toda la pantalla.
            if (!$elements[$i]->getAttribute('width')) {
                $elements[$i]->addAttribute('width', $widthCol);
            }
        } 
        
    }

    public function keypadAction()
    {
        $className = $this->_xml->getAttribute('target');
        $this->view->model = $className;
        $this->_model = new $className();
        $this->view->primary = implode(";", $this->_model->info('primary'));
        
        $this->view->name = $this->_xml->getAttribute('name');
        $this->view->add = $this->_xml->getAttribute("add") && $this->_xml->getAttribute("add") == "true" && $this->_acl->isUserAllowed($this->_component, 'ADD') ? true : false;
        $this->view->edit = $this->_xml->getAttribute("edit") && $this->_xml->getAttribute("edit") == "true"  && $this->_acl->isUserAllowed($this->_component, 'EDIT') ? true : false;
        $this->view->clone = $this->_xml->getAttribute("clone") && $this->_xml->getAttribute("clone") == "true"  && $this->_acl->isUserAllowed($this->_component, 'ADD') ? true : false;
        $this->view->delete = $this->_xml->getAttribute("delete") && $this->_xml->getAttribute("delete") == "true" && $this->_acl->isUserAllowed($this->_component, 'DELETE') ? true : false;
        $this->view->component = $this->_component;
        
        $ajax = $this->_xml->xpath("//forms[@ajax='true']") ? 'true' : 'false';
        $customFunctions = $this->_xml->xpath("//helpers/customFunction");
        $this->view->customFunctions = $customFunctions && $this->_acl->isUserAllowed($this->_component, 'EDIT') ? $customFunctions : array();
        $excel = $this->_xml->xpath("//helpers/excel");
        $this->view->excel = $excel ? $excel : array();
        $this->view->zweiExcelVersion = $this->_config->zwei->excel->version ? $this->_config->zwei->excel->version : 'csv';
        
        
        //if (!$ajax === 'false' && $this->_xml->xpath("//forms/edit[@ajax='true']")) $ajax = 'true';
        $this->view->ajax = $ajax === 'true' ? 'true' : 'false';
        //$this->view->changePassword = $this->_xml->getAttribute("changePassword") && $this->_xml->getAttribute("changePassword") == "true"  && $this->_acl->isUserAllowed($this->page, 'EDIT');
    }

    public function changePasswordAction()
    {
        $this->view->p = $this->_component;
        $this->initForm('edit');
    }

    public function contextMenuAction()
    {
        // action body
    }


}

