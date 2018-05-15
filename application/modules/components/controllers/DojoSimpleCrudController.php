<?php

/**
 * Interfaz para operaciones CRUD
 *
 * @example
 * @package Components
 * @version 2014-03-19
 * @since   1.0
 * @author  rodrigo.riquelme@gamelena.com
 * @example
 * <code>
    <?xml version="1.0"?> 
    <component xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="components.xsd" 
    name="Informaci&amp;oacute;n" type="dojo-simple-crud" target="PersonalInfoModel" list="true" plugins="{}" edit="true" add="false" delete="false" serverPagination="true"
    >
        <elements>
               <element name="ID" target="id" type="id-box" visible="false" edit="false" add="false"/>
            <element name="Usuario" target="user_name" type="dijit-form-validation-text-box" visible="true" edit="false" add="true"/>
            <element name="Nombres" target="first_names" type="dijit-form-validation-text-box" visible="true" edit="true" add="true"/>
            <element name="Apellidos" target="last_names" type="dijit-form-validation-text-box" visible="true" edit="true" add="true"/>
            <element name="E-Mail" target="email" type="dijit-form-validation-text-box" regExp="[\w-\.]{3,}@([\w-]{2,}\.)*([\w-]{2,}\.)[\w-]{2,4}" invalidMessage="mail no valido" visible="true" edit="true" add="true" />
        </elements>
        <forms>
            <changePassword>
                <element target="password" regExp="^[0-9a-zA-Z_]{6,25}$" maxlength="25" invalidMessage="Sólo letras y números sin espacios, mínimo 6 caracteres"/>
            </changePassword>
        </forms>
    </component>
 * </code>
 */

class Components_DojoSimpleCrudController extends Zend_Controller_Action
{

    /**
     * Objecto XML
     * @var Gamelena_Admin_Xml
     */
    private $_xml = null;

    /**
     * Nombre archivo XML
     * @var string
     */
    private $_component = null;
    
    /**
     * Nombre de archivo XML que debe usarse para validar permisos.
     * @var string 
     */
    private $_aclComponent = null;
    /**
     * Configuración global.
     * @var Zend_Config
     */
    private $_config = null;

    /**
     * Control de acceso a recursos.
     * @var Gamelena_Admin_Acl
     */
    private $_acl = null;

    /**
     * Modelo sobre el cual se opera.
     * @var Gamelena_Db_Table
     */
    private $_model = null;

    /**
     * Modulo asociado a archivo XML
     * @var Zend_Db_Table_Row
     */
    private $_module = null;
    
    /**
     * Post constructor
     * @see Zend_Controller_Action::init()
     */
    public function init()
    {
        if (!Gamelena_Admin_Auth::getInstance()->hasIdentity()) {
            $this->_redirect('admin/login');
        }
        $this->_acl = new Gamelena_Admin_Acl(Zend_Auth::getInstance());
        
        $this->_config = Gamelena_Controller_Config::getOptions();
        $this->view->noCache = isset($this->_config->gamelena->resources) ? $this->_config->gamelena->resources->noCache : '';
        $this->view->menus = $this->_config->gamelena->layout->menus;
        $this->view->gamelenaExcelVersion = $this->_config->gamelena->excel->version ? $this->_config->gamelena->excel->version : 'csv';
        
        if ($this->getRequest()->getParam('p')) {
            $file = Gamelena_Admin_Xml::getFullPath($this->getRequest()->getParam('p'));
            $this->_xml = new Gamelena_Admin_Xml($file, 0, 1);
            $this->_component = $this->getRequest()->getParam('p');
            $this->_aclComponent = $this->_xml->getAttribute('aclComponent') ? $this->_xml->getAttribute('aclComponent') : $this->_component;
            $this->view->aclComponent = $this->_aclComponent;
        }
        
        $this->view->mainPane = isset($this->_config->gamelena->layout->mainPane) ? $this->_config->gamelena->layout->mainPane : 'undefined';
        $this->view->domPrefix = isset($this->view->mainPane) && $this->view->mainPane == 'dijitTabs' ? Gamelena_Utils_String::toVarWord($this->getRequest()->getParam('p')) : '';
        
        if ($this->_acl->userHasRoleAllowed($this->_aclComponent, 'EDIT')) {
            $this->view->validateGroupEdit = false;
        } else {
            $this->view->validateGroupEdit = true;
        }
        
        if ($this->_acl->userHasRoleAllowed($this->_aclComponent, 'DELETE')) {
            $this->view->validateGroupDelete = false;
        } else {
            $this->view->validateGroupDelete = true;
        }
        
        $className = $this->_xml->getAttribute('target');
        $this->ajax = $this->_xml->xpath("//component/forms[@ajax='true']") ? true : false;
        $this->view->multiForm = $this->ajax ? true : false;
        //Se agrega nombre de modelo a mensaje de Exception
        if (!empty($className)) {
            try {
                $this->_model = new $className();
            } catch (Zend_Application_Resource_Exception $e) {
                throw new Zend_Application_Resource_Exception("$className: {$e->getMessage()}", $e->getCode());
            } catch (Zend_Db_Exception $e) {
                throw new Zend_Db_Exception("$className: {$e->getMessage()}", $e->getCode());
            }
        }
    }

    /**
     * Layout principal
     * @return void
     */
    public function indexAction()
    {
        $this->view->name = $this->_xml->getAttribute('name');
        $this->view->includeJs = $this->_xml->getAttribute('js') ? "<script src=\"".BASE_URL.'js/'.$this->_xml->getAttribute('js')."?noCache={$this->view->noCache}\"></script>\n" : '';
        $this->view->onShow = $this->_xml->getAttribute('onShow') ? $this->_xml->getAttribute('onShow') : '';
        
        if ($this->_xml->xpath("//component/forms")) {
            $forms = $this->_xml->xpath("//component/forms");
        }
        
        $this->view->styleDialog = $this->_xml->xpath("//component/forms[@style]") ? "style=\"{$forms[0]->getAttribute('style')}\"" : '';
        $this->view->onloadDialog = $this->_xml->xpath("//component/forms[@onload]") ? "onload=\"{$forms[0]->getAttribute('onload')}\"" : '';
        $this->view->onshowDialog = $this->_xml->xpath("//component/forms[@onshow]") ? "onshow=\"{$forms[0]->getAttribute('onshow')}\"" : '';
        $this->view->onhideDialog = $this->_xml->xpath("//component/forms[@onhide]") ? "onhide=\"{$forms[0]->getAttribute('onhide')}\"" : '';
        $this->view->ajax = $this->_xml->xpath("//component/forms[@ajax='true']") ? true : false;
        $this->view->changePassword = $this->_xml->xpath("//component/forms/changePassword") ? true : false;
        $this->view->containerDojoType = $this->_xml->getAttribute('containerDojoType') ? $this->_xml->getAttribute('containerDojoType') : 'dijit/layout/BorderContainer';
        $this->view->subContainerDojoType = $this->view->containerDojoType == 'dijit/layout/BorderContainer' ? '' : 'dijit/layout/ContentPane';
        $this->view->panes = $this->_xml->xpath("//component/pane") ? $this->_xml->xpath("//component/pane") : array();
        $this->view->script = $this->_xml->xpath("//component/script") ? "<script>\n" . dom_import_simplexml($this->_xml->script)->textContent . "</script>\n" : '';
        $this->view->hasElements = $this->_xml->xpath("//component/elements/element") ? true : false;
        $this->view->hasSearchers = $this->_xml->xpath("//component/searchers/element") ? true : false;
        $this->view->model = $this->_xml->getAttribute('target');
        $this->view->searchersOutsideContent = $this->_xml->xpath("//component/searchers[@outsideContent='true']") && $this->view->hasElements ? true : false;
        
        if ($this->view->changePassword) {
            $this->view->primary = $this->_model->info(Zend_Db_Table::PRIMARY);
        }
    }

    /**
     * Buscador
     * @return void
     */
    public function searchAction()
    {
        $this->view->p = $this->_component;
        $userAgent = new Gamelena_UserAgent();
        $this->view->isInternetExplorer = $userAgent->getBrowser() === Gamelena_UserAgent::BROWSER_IE;
        $this->view->model = $this->_xml->getAttribute('target');
        $this->view->hide = '';
        if (isset($this->_xml->searchers)) {
            $this->view->xml = $this->_xml;
            $this->view->groups = $this->_xml->getSearchers(true);
            $this->view->hideSubmit = $this->_xml->searchers->getAttribute('hideSubmit');
            $this->view->searchRequest = $this->getRequest()->getParam('search', array());
            $this->view->storeType = $this->_xml->getAttribute('serverPagination') === 'true' ? 'query' : '';
            $this->view->onSubmit = $this->_xml->xpath('//component/searchers/onSubmit') ? dom_import_simplexml($this->_xml->searchers->onSubmit)->textContent : '';
            $this->view->onPostSubmit = $this->_xml->xpath('//component/searchers/onPostSubmit') ? dom_import_simplexml($this->_xml->searchers->onPostSubmit)->textContent : '';
            
            $customFunctions = $this->_xml->xpath("//searchers/helpers/customFunction");
            $this->view->customFunctions = $customFunctions && $this->_acl->isUserAllowed($this->_aclComponent, 'LIST') ? $customFunctions : array();
            
            $this->view->enableActions = "";
            //Activar botones y menu de acciones al hacer submit
            if (!$this->_xml->getAttribute('list') || $this->_xml->getAttribute("list") == 'false') {
                $excel = $this->_xml->xpath("//component/helpers/excel");
                $myExcel = $excel ? $excel : array();
                foreach ($myExcel as $i => $f) {
                    $formatter = $f->getAttribute('formatter') ? $f->getAttribute('formatter') : $this->view->gamelenaExcelVersion;
                    if ($this->view->menu = 'keypad' || $this->view->menu = 'both') {
                        $this->view->enableActions = "dijit.byId('{$this->view->domPrefix}MenuExcel{$formatter}').set('disabled', false);";
                    }
                    
                    if ($this->view->menu = 'contextMenu' || $this->view->menu = 'both') {
                        $this->view->enableActions .= "dijit.byId('{$this->view->domPrefix}btnExcel{$formatter}').set('disabled', false);";
                    }
                }
            }
            
        } else {
            $this->view->groups = array();
            $this->view->hideSubmit = true;
            $this->view->hide = !$this->view->isInternetExplorer ? 'style="display:none;"' : '';
            $this->view->customFunctions = array();
        }
    }
    
    /**
     * Inicializa formulario
     * @param string $mode
     * @return void
     */
    public function initForm($mode)
    {
        $r = $this->getRequest();
        $this->view->p = $this->_component;
        $this->view->xml = $this->_xml;
        $this->view->acl = $this->_acl;
        $this->view->mode = $mode;
        $this->view->loadPartial = $r->getParam('loadPartial', false);
        $this->view->dialogIndex = $r->getParam('dialogIndex', '');
        
        $this->view->onShow = $this->_xml->xpath('//component/forms/onShow') ? dom_import_simplexml($this->_xml->forms->onShow)->textContent : '';
        $this->view->onHide = $this->_xml->xpath('//component/forms/onHide') ? dom_import_simplexml($this->_xml->forms->onHide)->textContent : '';//FIXME deaf listener onHide
        $this->view->onSubmit = $this->_xml->xpath('//component/forms/onSubmit') ? dom_import_simplexml($this->_xml->forms->onSubmit)->textContent : '';
        $this->view->onPostSubmit = $this->_xml->xpath('//component/forms/onPostSubmit') ? dom_import_simplexml($this->_xml->forms->onPostSubmit)->textContent : '';
        $this->view->tabs = $this->_xml->getTabsWithElements(true, "@$mode='true' or @$mode='readonly' or @$mode='disabled'");
        $this->view->model = $this->_xml->getAttribute('target');
        
        $this->view->modelPrimary = array();
        if ($this->view->model) {
            $this->view->modelPrimary = $this->_model->info(Zend_Db_Table::PRIMARY);
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
                if (method_exists($select, '__toString')) { Gamelena_Utils_Debug::writeBySettings($select->__toString(), "query_log"); 
                }
                $data = $this->_model->fetchRow($select);
                if ($this->view->multiForm) {
                    $this->view->dialogIndex = '';
                    foreach ($this->_model->info('primary') as $primary) {
                        $this->view->dialogIndex .= $data[$primary];
                    }
                    $this->view->dialogIndex = Gamelena_Utils_String::toVarWord($this->view->dialogIndex);
                }
                
                //Es posible añadir más valores al retorno de la query principal sobrecargando este Gamelena_Db_Table::overloadDataForm.
                $this->view->data = $data;
                if (method_exists($this->_model, 'overloadDataForm')) { $this->view->data = $this->_model->overloadDataForm($data); 
                }
            }
        }
        $this->view->dijitDialogId = $r->getParam('dijitDialogId', $this->view->domPrefix . 'dialog_' . $mode . $this->view->dialogIndex);
        $this->view->dijitDataGridId = $r->getParam('dijitDataGridId', $this->view->domPrefix . 'dataGrid');
        $this->view->dijitFormSearchId = $r->getParam('dijitFormSearchId', $this->view->domPrefix . 'formSearch');
        
        $customFunctions = $this->_xml->xpath("//component/forms/helpers/customFunction");
        $this->view->customFunctions = $customFunctions ? $customFunctions : array();
        
        $this->view->includeJs = $this->_xml->getAttribute('jsForm') ? "<script src=\"".BASE_URL.'js/'.$this->_xml->getAttribute('jsForm')."?noCache={$this->view->noCache}\"></script>" : '';
    }
    
    /**
     * Inicialización de acciones para botones y menús.
     * 
     * @return void
     */
    public function initKeys()
    {
        if ($this->_xml->getAttribute('target')) {
            $this->view->model = $this->_xml->getAttribute('target');
            if (!$this->_model->info('primary')) {
                Console::error("Se debe implementar {$this->view->model}::info('primary')", true);
            }
            
            $this->view->primary = implode(";", $this->_model->info('primary'));
            $this->view->jsPrimary = "['".implode("','",  $this->_model->info('primary'))."']";
        }
        
        $this->view->name = $this->_xml->getAttribute('name');
        
        $this->view->add = $this->_xml->getAttribute('add') && $this->_xml->getAttribute("add") == 'true' && $this->_acl->isUserAllowed($this->_aclComponent, 'ADD') ? true : false;
        $this->view->list = $this->_xml->getAttribute('list') && $this->_xml->getAttribute("list") == 'true' && $this->_acl->isUserAllowed($this->_aclComponent, 'LIST') ? true : false;
        $this->view->edit = $this->_xml->getAttribute('edit') && $this->_xml->getAttribute("edit") == 'true'  && $this->_acl->isUserAllowed($this->_aclComponent, 'EDIT') ? true : false;
        $this->view->clone = $this->_xml->getAttribute('clone') && $this->_xml->getAttribute("clone") == 'true'  && $this->_acl->isUserAllowed($this->_aclComponent, 'ADD') ? true : false;
        $this->view->delete = $this->_xml->getAttribute('delete') && $this->_xml->getAttribute("delete") == 'true' && $this->_acl->isUserAllowed($this->_aclComponent, 'DELETE') ? true : false;
        $this->view->onPostSubmit = $this->_xml->xpath('//component/forms/onPostSubmit') ? dom_import_simplexml($this->_xml->forms->onPostSubmit)->textContent : '';
        
        $this->view->component = $this->_component;
        
        $ajax = $this->_xml->xpath("//component/forms[@ajax='true']") ? 'true' : 'false';
        $customFunctions = $this->_xml->xpath("//component/helpers/customFunction");
        $uploaders = $this->_xml->xpath("//component/helpers/uploader");
        
        $this->view->customFunctions = array();
        /**
         * @var $function Gamelena_Admin_Xml
         */
        foreach ($customFunctions as $function) {
            $action = $function->getAttribute('aclAction') ? $function->getAttribute('aclAction') : 'EDIT';
            if ($this->_acl->isUserAllowed($this->_aclComponent, $action)) {
                $this->view->customFunctions[] = $function;
            }
        }
        
        $filterUploaders = $uploaders ? $uploaders : array();
        
        $this->view->uploaders = array();
        if (count($filterUploaders)) {
            
            /**
             * @var $uploader Gamelena_Admin_Xml
             */
            foreach ($filterUploaders as $i => $uploader) {
                $uploaderAction = $uploader->getAttribute('action') ? $uploader->getAttribute('action') : null;
                if ($uploaderAction) {
                    if ($uploaderAction === 'load') {
                        if ($this->_acl->isUserAllowed($this->_aclComponent, 'EDIT') && $this->_acl->isUserAllowed($this->_aclComponent, 'ADD')) {
                            $this->view->uploaders[] = $uploader;
                        }
                    } else if ($uploaderAction === 'edit') {
                        if ($this->_acl->isUserAllowed($this->_aclComponent, 'EDIT')) {
                            $this->view->uploaders[] = $uploader;
                        }
                    } else if ($uploaderAction === 'add') {
                        if ($this->_acl->isUserAllowed($this->_aclComponent, 'ADD')) {
                            $this->view->uploaders[] = $uploader;
                        }
                    } else if ($uploaderAction === 'delete') {
                        if ($this->_acl->isUserAllowed($this->_aclComponent, 'DELETE')) {
                            $this->view->uploaders[] = $uploader;
                        }
                    } else {
                        //Habilitar permisos para otras acciones acá de ser necesario en el futuro.
                        $this->view->uploaders[] = $uploader;
                    }
                }
            }
        }
        
        
        $excel = $this->_xml->xpath("//component/helpers/excel");
        $this->view->excel = $excel ? $excel : array();
        
        
        //if (!$ajax === 'false' && $this->_xml->xpath("//forms/edit[@ajax='true']")) $ajax = 'true';
        $this->view->ajax = $ajax === 'true' ? 'true' : 'false';
        $this->view->queryParams = $_SERVER["QUERY_STRING"];
        //$this->view->changePassword = $this->_xml->getAttribute("changePassword") && $this->_xml->getAttribute("changePassword") == "true"  && $this->_acl->isUserAllowed($this->page, 'EDIT');
    }
    
    /**
     * Diálogo editar.
     * 
     * @return void
     */
    public function editAction()
    {
        $ajax = $this->_xml->xpath("//component/forms[@ajax='true']") ? 'true' : 'false';
        $this->view->changePassword = $this->_xml->xpath("//component/forms/changePassword") ? true : false;
        if (!$ajax === 'false' && $this->_xml->xpath("//component/forms/edit[@ajax='true']")) { $ajax = 'true'; 
        }
        $this->view->ajax = $ajax === 'true' ? true : false;
        $this->view->queryParams = http_build_query($this->getRequest()->getParams());
        
        $this->initForm('edit');
    }
    
    /**
     * Diálogo agregar.
     * 
     * @return void
     */
    public function addAction()
    {
        $this->initForm('add');
        $this->render('edit');
    }
    
    /**
     * Diálogo clonar.
     * 
     * @return void
     */
    public function cloneAction()
    {
        $ajax = $this->_xml->xpath("//component/forms[@ajax='true']") ? 'true' : 'false';
        $this->view->ajax = $ajax === 'true' ? true : false;
        $this->initForm('clone');
        $this->render('edit');
    }
    
    /**
     * Listado.
     * 
     * @return void
     */
    public function listAction()
    {
        $userAgent = new Gamelena_UserAgent();
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
        $menus = in_array($this->_config->gamelena->layout->menus, array('contextMenu', 'both')) ? "menus:{selectedRegionMenu: menu{$this->view->domPrefix}}," : '';
        
        $pagination = !$userAgent->isMobile() && $this->_xml->getAttribute('serverPagination') === "true" ? "pagination: {defaultPageSize:25, maxPageStep: 5, id: '{$this->view->domPrefix}gridPaginator', style: {position: 'relative'}}" : '';
        
        $this->view->plugins = $this->_xml->getAttribute('plugins') ? $this->_xml->getAttribute('plugins') : "{ $menus $pagination}";
        $this->view->onRowClick = $this->_xml->getAttribute('onRowClick') ? $this->_xml->getAttribute('onRowClick') : false;
        $this->view->onFetchComplete = $this->_xml->getAttribute('onFetchComplete') ? $this->_xml->getAttribute('onFetchComplete') : false;
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
                    if (dijit.byId('{$this->view->domPrefix}btnEdit') != undefined) dijit.byId('{$this->view->domPrefix}btnEdit').set('disabled', items[0].admPortalIsAllowedEDIT!='1');
                    if (dijit.byId('{$this->view->domPrefix}MenuItemEdit')) dijit.byId('{$this->view->domPrefix}MenuItemEdit').set('disabled', items[0].admPortalIsAllowedEDIT!='1');
                ";
            }
        }
        
        if ($this->_xml->getAttribute('add') === 'true') {
            if ($this->_acl->userHasRoleAllowed($this->_aclComponent, 'ADD')) {
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
                if (dijit.byId('{$this->view->domPrefix}btnDelete') != undefined) dijit.byId('{$this->view->domPrefix}btnDelete').set('disabled', items[0].admPortalIsAllowedDELETE!='1');
                ";
            }
        }
        
        if ($this->_xml->getAttribute('onRowClick')) {
            $this->view->onRowClick .= $this->_xml->getAttribute('onRowClick');
        }
        
        
        $this->view->onRowDblClick = '';
        if ($this->_xml->getAttribute('onRowDblClick')) {
            $this->view->onRowDblClick = $this->_xml->getAttribute('onRowDblClick');
        } else if ($this->_xml->getAttribute('edit') == "true") {
            if (!$this->view->validateGroupEdit) {
                if ($this->view->multiForm) {
                    $this->view->onRowDblClick = "
                        var form = new gamelena.Form({
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
                        var form = new gamelena.Form({
                            ajax: $ajax,
                            component: '{$this->_component}',
                            action: 'edit',
                            dijitDialog: dijit.byId('{$this->view->domPrefix}dialog_edit'), 
                            dijitForm: dijit.byId('{$this->view->domPrefix}form_edit'),
                            queryParams: '{$_SERVER['QUERY_STRING']}',
                            dijitDataGrid: dijit.byId('{$this->view->domPrefix}dataGrid')
                        }); 
                        form.showDialog();";
                }
            } else {
                if ($this->view->multiForm) {
                    $this->view->onRowDblClick = "
                        var items = this.selection.getSelected();
                        if (items[0].i != undefined && items[0].r._items != undefined) items[0] = items[0].i;//workaround, a Dojo bug?
                        if (items[0].admPortalIsAllowedEDIT=='1') {
                            var form = new gamelena.Form({
                                ajax: $ajax,
                                component: '{$this->_component}',
                                action: 'edit',
                                title: 'Editar {$this->_xml->getAttribute("name")}',
                                queryParams: '{$_SERVER['QUERY_STRING']}',
                                prefix: '{$this->view->domPrefix}',
                                dijitDataGrid: dijit.byId('{$this->view->domPrefix}dataGrid'),
                                keys : $jsPrimary
                            }); 
                            form.showMultipleDialogs();
                        }
                    ";
                } else {
                    $this->view->onRowDblClick = "
                        var items = this.selection.getSelected();
                        if (items[0].i != undefined && items[0].r._items != undefined) items[0] = items[0].i;//workaround, a Dojo bug?
                        if (items[0].admPortalIsAllowedEDIT=='1') {
                            var form = new gamelena.Form({
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
    
    /**
     * Botonera.
     * 
     * @return void
     */
    public function keypadAction()
    {
        $this->initKeys();
    }
    
    /**
     * Diálogo cambiar password.
     * 
     * @return void
     */
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
                $this->view->inputConfirmParams[$i] = $attr;
            }
        }
        
        $this->view->ajax = 'false';
        $this->initForm('edit');
    }
    
    /**
     * Menú contextual.
     * 
     * @return void
     */
    public function contextMenuAction()
    {
        $this->initKeys();
    }
    
    /**
     * Accion cargas masivas
     * 
     * @return void
     */
    public function uploadAction()
    {
        $r = $this->getRequest();
        $action = $r->getParam('accion');
        
        if ($action) {
            $allowed = 
                ($action === 'load' && $this->_acl->isUserAllowed($this->_component, 'EDIT') && $this->_acl->isUserAllowed($this->_component, 'ADD')) ||
                ($action === 'delete' && $this->_acl->isUserAllowed($this->_component, 'DELETE')) ||
                ($action === 'insert' && $this->_acl->isUserAllowed($this->_component, 'ADD'));
            
            if ($allowed) {
                $this->view->response = array(
                    'error' => '9',
                    'message' => 'No se ha subido archivo.',
                    'size' => '0'
                );
                
                $uploader = new Gamelena_Utils_File_Uploader($this->_xml);
                
                if ($r->getParam('truncate') === 'true' && $this->_acl->isUserAllowed($this->_component, 'DELETE')) {
                    $uploader->truncate();
                }
                
                foreach ($_FILES as $i => $file) { //Se espera solo un archivo
                    $this->view->response = $uploader->process($file, $action);
                }
            }
        }
    }
    
    /**
     * Formulario archivo
     *
     * @return void
     */
    public function uploadFormAction()
    {
        $r = $this->getRequest();
        $this->view->path = $r->getParam('path') ? BASE_URL . $r->getParam('path') : BASE_URL . 'components/dojo-simple-crud/upload';
        $this->view->component = $r->getParam('p');
        $this->view->accion = $r->getParam('accion');
        $this->view->truncate = $r->getParam('truncate');
        
        $this->view->keys = array();
        $keys = $r->getParam('keys', array());
        
        foreach ($keys as $key => $value) {
            $this->view->keys[$key] = $value;
        }
    }
    
}

