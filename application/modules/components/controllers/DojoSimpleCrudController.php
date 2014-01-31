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

    /**
     * Modulo asociado a archivo XML
     * @var Zend_Db_Table_Row
     */
    private $_module = null;
    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action::init()
     */
    public function init()
    {
        if (!Zwei_Admin_Auth::getInstance()->hasIdentity()) $this->_redirect('admin/login');
        $this->_acl = new Zwei_Admin_Acl(Zend_Auth::getInstance());
        
        $this->_config = Zwei_Controller_Config::getOptions();
        $this->view->multiForm = isset($this->_config->zwei->form->multiple) && !empty($this->_config->zwei->form->multiple) ? true : false;
        
        
        if ($this->getRequest()->getParam('p')) {
            $this->_component = $this->getRequest()->getParam('p');
            $file = Zwei_Admin_Xml::getFullPath($this->_component);
            $this->_xml = new Zwei_Admin_Xml($file, 0, 1);
        }
        
        $this->view->mainPane = isset($this->_config->zwei->layout->mainPane) ? $this->_config->zwei->layout->mainPane : 'undefined';
        $this->view->domPrefix = isset($this->view->mainPane) && $this->view->mainPane == 'dijitTabs' ? Zwei_Utils_String::toVarWord($this->getRequest()->getParam('p')) : '';
        
        if ($this->_acl->userHasRoleAllowed($this->_component, 'EDIT')) {
            $this->view->validateGroupEdit = false;
        } else {
            $this->view->validateGroupEdit = true;
        }
        
        if ($this->_acl->userHasRoleAllowed($this->_component, 'DELETE')) {
            $this->view->validateGroupDelete = false;
        } else {
            $this->view->validateGroupDelete = true;
        }
        
        $className = $this->_xml->getAttribute('target');
        //Se agrega nombre de modelo a mensaje de Exception
        try {
            $this->_model = new $className();
        } catch (Zend_Application_Resource_Exception $e) {
            throw new Zend_Application_Resource_Exception("$className: {$e->getMessage()}", $e->getCode());
        } catch (Zend_Db_Exception $e) {
            throw new Zend_Db_Exception("$className: {$e->getMessage()}", $e->getCode());
        }
    }

    public function indexAction()
    {
        $this->view->name = $this->_xml->getAttribute('name');
        $this->view->menus = $this->_config->zwei->layout->menus;
        $this->view->includeJs = $this->_xml->getAttribute('js') ? "<script src=\"".BASE_URL.'js/'.$this->_xml->getAttribute('js')."?nocache=8\"></script>\n" : '';
        if ($this->_xml->xpath("//component/forms")) $forms = $this->_xml->xpath("//component/forms");
        if ($this->_xml->xpath("//component/helpers")) $helpers = $this->_xml->xpath("//component/helpers");
        
        $this->view->styleDialog = $this->_xml->xpath("//component/forms[@style]") ? "style=\"{$forms[0]->getAttribute('style')}\"" : '';
        $this->view->onloadDialog = $this->_xml->xpath("//component/forms[@onload]") ? "onload=\"{$forms[0]->getAttribute('onload')}\"" : '';
        $this->view->onshowDialog = $this->_xml->xpath("//component/forms[@onshow]") ? "onshow=\"{$forms[0]->getAttribute('onshow')}\"" : '';
        $this->view->onhideDialog = $this->_xml->xpath("//component/forms[@onhide]") ? "onhide=\"{$forms[0]->getAttribute('onhide')}\"" : '';
        $this->view->ajax = $this->_xml->xpath("//component/forms[@ajax='true']") ? true : false;
        $this->view->changePassword = $this->_xml->xpath("//component/forms/changePassword") ? true : false;
        $this->view->containerDojoType = $this->_xml->getAttribute('containerDojoType') ? $this->_xml->getAttribute('containerDojoType') : 'dijit/layout/BorderContainer';
        $this->view->subContainerDojoType = $this->view->containerDojoType == 'dijit/layout/BorderContainer' ? '' : 'dijit/layout/ContentPane';
        $this->view->searchersOutsideContent = $this->_xml->xpath("//component/searchers[@outsideContent='true']") ? true : false;
        $this->view->panes = $this->_xml->xpath("//component/pane") ? $this->_xml->xpath("//component/pane") : array();
        
        if ($this->view->changePassword) {
            $this->view->primary = $this->_model->info(Zend_Db_Table::PRIMARY);
        }
    }

    public function searchAction()
    {
        if (isset($this->_xml->searchers)) {
            $this->view->p = $this->_component;
            $this->view->model = $this->_xml->getAttribute('target');
            $this->view->xml = $this->_xml;
            $this->view->groups = $this->_xml->getSearchers(true);
            $this->view->hideSubmit = $this->_xml->searchers->getAttribute('hideSubmit');
            $this->view->searchRequest = $this->getRequest()->getParam('search', array());
            $this->view->storeType = $this->_xml->getAttribute('serverPagination') === 'true' ? 'query' : '';
            $this->view->onSubmit = $this->_xml->xpath('//component/searchers/onSubmit') ? dom_import_simplexml($this->_xml->searchers->onSubmit)->textContent : '';
            
            $customFunctions = $this->_xml->xpath("//searchers/helpers/customFunction");
            $this->view->customFunctions = $customFunctions && $this->_acl->isUserAllowed($this->_component, 'LIST') ? $customFunctions : array();
        }
    }

    public function initForm($mode)
    {
        $r = $this->getRequest();
        $this->view->p = $this->_component;
        $this->view->xml = $this->_xml;
        $this->view->mode = $mode;
        $this->view->loadPartial = $r->getParam('loadPartial', false);
        $this->view->dialogIndex = $r->getParam('dialogIndex', '');

        $this->view->onShow = $this->_xml->xpath('//component/forms/onShow') ? dom_import_simplexml($this->_xml->forms->onShow)->textContent : '';
        $this->view->onSubmit = $this->_xml->xpath('//component/forms/onSubmit') ? dom_import_simplexml($this->_xml->forms->onSubmit)->textContent : '';
        $this->view->onPostSubmit = $this->_xml->xpath('//component/forms/onPostSubmit') ? dom_import_simplexml($this->_xml->forms->onPostSubmit)->textContent : '';

        $this->view->model = $this->_xml->getAttribute('target');
        $this->view->modelPrimary = $this->_model->info('primary');
        $this->view->tabs = $this->_xml->getTabsWithElements(true, "@$mode='true' or @$mode='readonly' or @$mode='disabled'");
        
        if (($mode == 'edit' || $mode == 'clone') && $this->view->ajax) {
            $a = $this->_model->getAdapter();
            
            $select = $this->_model->select();

            foreach ($r->getParam('primary', array()) as $i => $v) { 
                //Concatenar nombre de campo con tabla principal en caso de que campo exista en la tabla,
                //esto previene posibles errores de ambiguedad de nombre de campo cuando se use join
                if (in_array($i, $this->_model->info(Zend_Db_Table::COLS))) { 
                    $i = $this->_model->info(Zend_Db_Table::NAME) . ".$i";
                }
                $select->where($a->quoteInto($a->quoteIdentifier($i). " = ?", $v));
            }
            Zwei_Utils_Debug::writeBySettings($select->__toString(), "query_log");
            $data = $this->_model->fetchRow($select);
            if ($this->view->multiForm) {
                $this->view->dialogIndex = '';
                foreach ($this->_model->info('primary') as $primary) {
                    $this->view->dialogIndex .= $data[$primary];
                }
                $this->view->dialogIndex = Zwei_Utils_String::toVarWord($this->view->dialogIndex);
            }
            //Es posible añadir más valores al retorno de la query principal sobrecargando este método.
            $this->view->data = $this->_model->overloadDataForm($data);
        }
        $this->view->includeJs = $this->_xml->getAttribute('jsForm') ? "<script src=\"".BASE_URL.'js/'.$this->_xml->getAttribute('jsForm')."?nocache=5\"></script>" : '';
    }
    
    public function initKeys()
    {
        $this->view->model = $this->_xml->getAttribute('target');
        $this->view->primary = implode(";", $this->_model->info('primary'));
        $this->view->jsPrimary = "['".implode("','",  $this->_model->info('primary'))."']";
        
        
        $this->view->name = $this->_xml->getAttribute('name');
        
        $this->view->add = $this->_xml->getAttribute('add') && $this->_xml->getAttribute("add") == 'true' && $this->_acl->isUserAllowed($this->_component, 'ADD') ? true : false;
        $this->view->edit = $this->_xml->getAttribute('edit') && $this->_xml->getAttribute("edit") == 'true'  && $this->_acl->isUserAllowed($this->_component, 'EDIT') ? true : false;
        $this->view->clone = $this->_xml->getAttribute('clone') && $this->_xml->getAttribute("clone") == 'true'  && $this->_acl->isUserAllowed($this->_component, 'ADD') ? true : false;
        $this->view->delete = $this->_xml->getAttribute('delete') && $this->_xml->getAttribute("delete") == 'true' && $this->_acl->isUserAllowed($this->_component, 'DELETE') ? true : false;
        $this->view->onPostSubmit = $this->_xml->xpath('//component/forms/onPostSubmit') ? dom_import_simplexml($this->_xml->forms->onPostSubmit)->textContent : '';
        
        
        $this->view->component = $this->_component;
        
        $ajax = $this->_xml->xpath("//component/forms[@ajax='true']") ? 'true' : 'false';
        $customFunctions = $this->_xml->xpath("//component/helpers/customFunction");
        $this->view->customFunctions = $customFunctions && $this->_acl->isUserAllowed($this->_component, 'EDIT') ? $customFunctions : array();
        $excel = $this->_xml->xpath("//component/helpers/excel");
        $this->view->excel = $excel ? $excel : array();
        $this->view->zweiExcelVersion = $this->_config->zwei->excel->version ? $this->_config->zwei->excel->version : 'csv';
        
        //if (!$ajax === 'false' && $this->_xml->xpath("//forms/edit[@ajax='true']")) $ajax = 'true';
        $this->view->ajax = $ajax === 'true' ? 'true' : 'false';
        $this->view->queryParams = $_SERVER["QUERY_STRING"];
        //$this->view->changePassword = $this->_xml->getAttribute("changePassword") && $this->_xml->getAttribute("changePassword") == "true"  && $this->_acl->isUserAllowed($this->page, 'EDIT');
    }

    public function editAction()
    {
        $ajax = $this->_xml->xpath("//component/forms[@ajax='true']") ? 'true' : 'false';
        $this->view->changePassword = $this->_xml->xpath("//component/forms/changePassword") ? true : false;
        if (!$ajax === 'false' && $this->_xml->xpath("//component/forms/edit[@ajax='true']")) $ajax = 'true';
        $this->view->ajax = $ajax === 'true' ? true : false;
        $this->view->queryParams = http_build_query($this->getRequest()->getParams());
        
        $this->initForm('edit');
    }

    public function addAction()
    {
        $this->initForm('add');
        $this->render('edit');
    }

    public function cloneAction()
    {
        $ajax = $this->_xml->xpath("//component/forms[@ajax='true']") ? 'true' : 'false';
        $this->view->ajax = $ajax === 'true' ? true : false;
        $this->initForm('clone');
        $this->render('edit');
    }

    public function listAction()
    {
        $this->view->p = $this->_component;
        $this->view->model = $this->_xml->getAttribute('target');
        $primary = $this->_model->info(Zend_Db_Table::PRIMARY);
        $jsPrimary = "['".implode("','", $primary)."']";
        $this->view->primary = implode(";", $this->_model->info('primary'));
        
        $this->view->list = $this->_xml->getAttribute('list') === 'true' ? true : false;
        $this->view->store = $this->view->list ? "store: {$this->view->domPrefix}storeGrid," : "";
        $this->view->noDataMessage = 'Sin datos.';
        $this->view->canSort = $this->_xml->getAttribute('canSort') === 'false' ? ',canSort: function(){return false}' : '';
        
        $this->view->dataDojoType = $this->_xml->getAttribute('serverPagination') === "true" ? 'dojox/data/QueryReadStore' : 'dojo/data/ItemFileReadStore';
        $this->view->gridDojoType = $this->_xml->getAttribute('gridDojoType') ? $this->_xml->getAttribute('gridDojoType') : 'dojox/grid/EnhancedGrid';
        $menus = in_array($this->_config->zwei->layout->menus, array('contextMenu', 'both')) ? "menus:{selectedRegionMenu: menu{$this->view->domPrefix}}," : '';
        $this->view->plugins = $this->_xml->getAttribute('plugins') ? $this->_xml->getAttribute('plugins') : "{ $menus pagination: {defaultPageSize:25, maxPageStep: 5 }}";
        $this->view->onRowClick = $this->_xml->getAttribute('onRowClick') ? "onRowClick:{$this->_xml->getAttribute('onRowClick')}," : "";
        $this->view->searchHideSubmit = $this->_xml->getAttribute('searchHideSubmit') === "true" ? true : false;
        $this->view->elements = $this->_xml->getElements("@visible='true'");
        
        $ajax = $this->_xml->xpath("//component/forms[@ajax='true']") ? 'true' : 'false';
        
        if ($this->_xml->getAttribute('edit') === 'true') {
            if (!$this->view->validateGroupEdit) {
                $this->view->onRowClick = "
                    if (dijit.byId('{$this->view->domPrefix}btnEdit') != undefined) dijit.byId('{$this->view->domPrefix}btnEdit').set('disabled', false);
                    if (dijit.byId('{$this->view->domPrefix}btnChangePassword') != undefined) dijit.byId('{$this->view->domPrefix}btnChangePassword').set('disabled', false);
                ";
            } else {
                $this->view->onRowClick = " var items = this.selection.getSelected();
                    if (items[0].i != undefined && items[0].r._items != undefined) items[0] = items[0].i;//workaround, a Dojo bug?
                    if (dijit.byId('{$this->view->domPrefix}btnEdit') != undefined) dijit.byId('{$this->view->domPrefix}btnEdit').set('disabled', items[0].magicPortalIsAllowedEDIT!='1');
                    if (dijit.byId('{$this->view->domPrefix}MenuItemEdit')) dijit.byId('{$this->view->domPrefix}MenuItemEdit').set('disabled', items[0].magicPortalIsAllowedEDIT!='1');
                ";
            }
        }
        
        if ($this->_xml->getAttribute('add') === 'true') {
            if ($this->_acl->userHasRoleAllowed($this->_component, 'ADD')) {
                $this->view->onRowClick .= "
                if (dijit.byId('{$this->view->domPrefix}btnClone') != undefined) dijit.byId('{$this->view->domPrefix}btnClone').set('disabled', false);
                ";
            } 
        }
        
        if ($this->_xml->getAttribute('delete') === 'true') {
            if (!$this->view->validateGroupDelete) {
                $this->view->onRowClick .= "
                if (dijit.byId('{$this->view->domPrefix}btnDelete') != undefined) dijit.byId('{$this->view->domPrefix}btnDelete').set('disabled', false);
                ";
            } else {
                $this->view->onRowClick .= " var items = this.selection.getSelected();
                if (items[0].i != undefined && items[0].r._items != undefined) items[0] = items[0].i;//workaround, a Dojo bug?
                if (dijit.byId('{$this->view->domPrefix}btnDelete') != undefined) dijit.byId('{$this->view->domPrefix}btnDelete').set('disabled', items[0].magicPortalIsAllowedDELETE!='1');
                ";
            }
        }
        
        if ($this->_xml->getAttribute('onRowClick')) {
            $this->view->onRowClick .= $this->_xml->getAttribute('onRowClick');
        }
        
        
        $this->view->onRowDblClick = '';
        if ($this->_xml->getAttribute('onRowDblClick')) {
            $this->view->onRowDblClick = $this->_xml->getAttribute('onRowDblClick');
        } else if ($this->_xml->getAttribute('edit')) {
            if (!$this->view->validateGroupEdit) {
                if (isset($this->_config->zwei->form->multiple) && !empty($this->_config->zwei->form->multiple)) {
                    $this->view->onRowDblClick = "
                        var form = new zwei.Form({
                            ajax: $ajax,
                            component: '{$this->_component}',
                            action: 'edit',
                            title: 'Editar {$this->_xml->getAttribute("name")}',
                            queryParams: '{$_SERVER['QUERY_STRING']}',
                            prefix: '{$this->view->domPrefix}',
                            dijitDataGrid: dijit.byId('{$this->view->domPrefix}dataGrid'),
                            keys : $jsPrimary
                        }); 
                        form.showMultipleDialogs()
                        ";
                } else {
                    $this->view->onRowDblClick = "
                        var form = new zwei.Form({
                            ajax: $ajax,
                            component: '{$this->_component}',
                            queryParams: '{$_SERVER['QUERY_STRING']}',
                            action: 'edit',
                            dijitDialog: dijit.byId('{$this->view->domPrefix}dialog_edit'),
                            dijitForm: dijit.byId('{$this->view->domPrefix}form_edit'),
                            dijitDataGrid: dijit.byId('{$this->view->domPrefix}dataGrid')
                        });
                        form.showDialog()";
                }
            } else {
                $this->view->onRowDblClick = "
                    var items = this.selection.getSelected();
                    if (items[0].i != undefined && items[0].r._items != undefined) items[0] = items[0].i;//workaround, a Dojo bug?
                    if (items[0].magicPortalIsAllowedEDIT=='1') {
                        var form = new zwei.Form({
                            ajax: $ajax,
                            component: '{$this->_component}',
                            action: 'edit',
                            queryParams: '{$_SERVER['QUERY_STRING']}',
                            dijitDialog: dijit.byId('{$this->view->domPrefix}dialog_edit'), 
                            dijitForm: dijit.byId('{$this->view->domPrefix}form_edit'), 
                            dijitDataGrid: dijit.byId('{$this->view->domPrefix}dataGrid')
                        }); 
                        form.showDialog();
                    }
                ";
            }
        }
        
        $numElements = count($this->view->elements);
        if ($numElements) {
            $widthCol = round((100/$numElements), 1) . "%";//Se le asigna a cada columna de la grilla un ancho proporcional a su cantidad en porcentaje.
            $elements = $this->_xml->getElements();
            for ($i = 1; $i < $numElements; $i++) {
                //Para cada columna se permite sobreescribir el ancho asignado por defecto, se sugiere trabajar con porcentajes para aprovechar toda la pantalla.
                if (!$elements[$i]->getAttribute('width')) {
                    $elements[$i]->addAttribute('width', $widthCol);
                }
                
            }
        }
        $this->view->queryParams = $_SERVER["QUERY_STRING"];
        
    }

    public function keypadAction()
    {
        $this->initKeys();
    }

    public function changePasswordAction()
    {
        $this->view->p = $this->_component;
        
        
        $this->view->targetPass = 'password';
        $this->view->namePass = 'Contrase&ntilde;a';
        
        $this->view->inputParams = array('target' => "data[{$this->view->targetPass}]", 'password' => 'true', 'value' => '', 'required' => 'true');
        $this->view->inputConfirmParams = array('target' => "confirm[{$this->view->targetPass}]", 'password' => 'true', 'value' => '', 'required' => 'true');
        
        $elements = $this->_xml->xpath('//forms/changePassword/element');
        $element = $elements[0];
        
        foreach ($element->attributes() as $i => $attr) {
            if ($i != 'target') {
                $this->view->inputParams[$i] = $attr;
            }
        }
        
        $this->view->ajax = 'false';
        $this->initForm('edit');
    }

    public function contextMenuAction()
    {
        $this->initKeys();
    }
}

