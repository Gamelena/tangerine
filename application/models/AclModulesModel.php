<?php

/**
 * Modelo de datos para módulos ACL del admin
 *
 *
 * @category Zwei
 * @package Models
 * @version $Id:$
 * @since 0.1
 *
 */

class AclModulesModel extends DbTable_AclModules
{
    /**
     * Tabla de íconos.
     * 
     * @var string
     */
    protected $_nameIcons = "web_icons";
    /**
     * 
     * @var string
     */
    protected $_label = "title";
    /**
     * 
     * @var array
     */
    protected $_dataActions = array();
    /**
     * Contenido archivo xml.
     * 
     * @var string
     */
    protected $_content = '';
    
    /**
     * (non-PHPdoc)
     * @see Zwei_Db_Table::update()
     */
    public function update(array $data, $where)
    {
        $data    = $this->cleanDataParams($data);
        $myWhere = $this->whereToArray($where);
        $update = false;
        
        $saveActions = $this->saveDataActions($myWhere['id']);
        
        $this->_ajax_todo = 'cargarArbolMenu';
        
        try {
            $update = parent::update($data, $where);
        } catch (Zend_Db_Exception $e) {
            if ($e->getCode() == 23000) {
                $this->setMessage("Clave repetida. COMPONENTE debe ser único.");
                return false;
            }
        }
        Zwei_Utils_File::clearRecursive(ROOT_DIR . "/cache");
        $writeContent = $this->writeContent($data);
        
        return $saveActions || $update || $writeContent;
    }
    
    /**
     * (non-PHPdoc)
     * @see Zwei_Db_Table::insert()
     */
    public function insert(array $data)
    {
        $data             = $this->cleanDataParams($data);
        $this->_ajax_todo = 'cargarArbolMenu';
        
        try {
            $lastInsertedId = parent::insert($data);
        } catch (Zend_Db_Exception $e) {
            if ($e->getCode() == 23000) {
                $this->setMessage("Clave repetida. COMPONENTE debe ser único.");
                return false;
            }
        }
        $this->writeContent($data);
        $this->saveDataActions($lastInsertedId);
        
        return $lastInsertedId;
        
    }
    /**
     * Escribe archivo xml.
     * 
     * @param array $data
     * @return number
     */
    public function writeContent($data)
    {
        $write = false;
        if (!empty($this->_content) && is_writable(COMPONENTS_ADMIN_PATH . '/' . $data['module'])) {
            $handle = fopen(COMPONENTS_ADMIN_PATH . '/' . $data['module'], "w+");
            $write = fwrite($handle, html_entity_decode(str_replace('&amp;', '&', $this->_content)));
            fclose($handle);
            @chmod(COMPONENTS_ADMIN_PATH . '/' . $data['module'], 0777);
        }
        return $write;
    }
    
    /**
     * Se separa entre datos de modulo y datos de acciones asociadas.
     * 
     * @param array
     * @return array
     * @see Zwei_Db_Table::cleanDataParams()
     */
    protected function cleanDataParams($data)
    {
        //Si el tipo de archivo es xml se tratará de crear archivo xml base, el que se pueda hacer esto dependerá de los permisos de escritura para COMPONENTS_ADMIN_PATH.
        if ($data['type'] == 'xml') {
            if (!file_exists(COMPONENTS_ADMIN_PATH . '/' . $data['module'])) {
                if (is_writable(COMPONENTS_ADMIN_PATH)) {
                    $handle = fopen(COMPONENTS_ADMIN_PATH . '/' . $data['module'], "w+");
                    fwrite($handle, "<?xml version=\"1.0\"?>\n"
                        . "<component name=\"M&amp;oacute;dulos\" type=\"dojo-simple-crud\" target=\"EjemploModel\" list=\"true\""
                        . " edit=\"true\" add=\"true\" delete=\"true\" clone=\"true\""
                        . " xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"components.xsd\">\n"
                        . "\t<elements>\n"
                        . "\t\t<element target=\"id\" visible=\"true\" type=\"dijit-form-validation-text-box\" edit=\"true\" add=\"true\" clone=\"true\"></element>\n"
                        . "\t</elements>\n"
                        . "\t<!--@todo esta es sólo una base de ejemplo, ver components.xsd para valores de component.type disponibles -->\n"
                        . "</component>\n");
                    fclose($handle);
                    @chmod(COMPONENTS_ADMIN_PATH . '/' . $data['module'], 0777);
                    Console::log("Creado archivo " . COMPONENTS_ADMIN_PATH . '/' . $data['module']);
                } else {
                    Console::log("No se pudo crear el archivo " . COMPONENTS_ADMIN_PATH . '/' . $data['module']);
                }
            }
        }
        
        if (isset($data['content'])) {
            $this->_content = $data['content'];
            unset($data['content']);
        }
        
        
        if (empty($data['module']))
            $data['module'] = null;
        if (empty($data['parent_id']))
            $data['parent_id'] = null;
        if (isset($data['actions'])) {
            $this->_dataActions = $data['actions'];
            //Encomillar los elementos de $this->_dataActions
            array_walk($this->_dataActions, create_function('&$str', '$str = "\"$str\"";'));
            unset($data['actions']);
        }
        return $data;
    }
    
    /**
     * Guardar acciones. 
     * 
     * @param int $aclModulesId
     * @return boolean
     */
    protected function saveDataActions($aclModulesId)
    {
        $modulesAction = new DbTable_AclModulesActions();
        $ad            = $modulesAction->getAdapter();
        $insert        = false;
        $delete        = false;
        //Borrar Todas las acciones del modulo, excepto los marcados
        $list          = !empty($this->_dataActions) ? implode(",", $this->_dataActions) : false;
        
        $where = array();
        
        $where[] = $ad->quoteInto('acl_modules_id = ?', $aclModulesId);
        if ($list)
            $where[] = "acl_actions_id NOT IN ($list)";
        
        $delete = $modulesAction->delete($where);
        
        foreach ($this->_dataActions as $v) {
            $data = array(
                'acl_actions_id' => str_replace('"', '', $v),
                /*[FIXME] evitar el str_replace() acá, de momento es necesario usarlo */
                'acl_modules_id' => $aclModulesId
            );
            
            try {
                $insert = $modulesAction->insert($data);
            }
            catch (Zend_Db_Exception $e) {
                if ($e->getCode() == '23000') {
                    $printData = print_r($data, 1);
                    Debug::write("Ya existe modulo_accion asociado a $printData");
                }
            }
        }
        return $insert || $delete;
    }
    
    /**
     * @param string $where
     * @return int
     * @see Zwei_Db_Table::delete()
     */
    public function delete($where)
    {
        $modulesActions = new AclModulesActionsModel();
        
        $whereArray        = self::whereToArray($where);
        $whereAclModulesId = "acl_modules_id={$whereArray['id']}";
        $modulesActions->delete($whereAclModulesId);
        
        return parent::delete($where);
    }
    
    
    /**
     * Obtiene arbol de módulos en forma recursiva.
     * 
     * @param $parentId int, id sobre la que se buscaran modulos hijos
     * @param $noTree boolean, Indica que esta funcion no se usara para el Arbol Menu para omitir lógica asociada a este.
     * @return array
     *      
     */
    public function getTree($parentId = null, $noTree = false)
    {
        $this->_acl = new Zwei_Admin_Acl();
        $root = $this->_acl->listGrantedResourcesByParentId($parentId);
        
        $arrNodes = array();
        //$i = 0;
        foreach ($root as $branch) {
            //Buscar si usuario en sesion es owner de algun elemento para desplegar nodo
            
            if ($branch['ownership']) {
                $file      = Zwei_Admin_Xml::getFullPath($branch['module']);
                if (file_exists($file)) {
                    try {
                        $xml       = new Zwei_Admin_Xml($file, null, true);
                    } catch (Exception $e) {
                        Console::error("No se pudo parsear $file.");
                    }
                } else {
                    Console::error("No se pudo encuentra $file.");
                    return $arrNodes;
                }
                
                
                if (!$this->_acl->isUserAllowed($branch['module'])) {
                    return $arrNodes;
                }
            }
            
            if ($branch['tree'] == '1' || $noTree) {
                $key                               = $branch['id'];
                $arrNodes[$key]['id']              = $branch['id'];
                $arrNodes[$key]['parent_id']       = $branch['parent_id'];
                $arrNodes[$key]['type']            = $branch['type'];
                $arrNodes[$key]['image']           = $branch['image'];
                $arrNodes[$key]['refresh_on_load'] = $branch['refresh_on_load'];
                $arrNodes[$key]['module']          = $branch['module'];
                
                $arrNodes[$key]['label'] = PHP_VERSION_ID >= 50400 ? html_entity_decode($branch['title']) : utf8_encode(html_entity_decode($branch['title']));
                
                $prefix = "";
                if ($branch['type'] == 'zend_module') {
                    $prefix = "";
                } else if ($branch['type'] == 'xml') {
                    $prefix = "admin/components?p=";
                } else if ($branch['type'] == 'legacy') {
                    $prefix = "admin/legacy?p=";
                } else if ($branch['type'] == 'iframe') {
                    $prefix = "admin/iframe?p=";
                }
                
                
                if ($prefix != "")
                    $branch['module'] = urlencode($branch['module']);
                if ($branch['type'])
                    $arrNodes[$key]['url'] = $prefix . $branch['module'];
                
                
                $childrens = $this->getTree($branch['id']);
                if ($childrens) {
                    $arrNodes[$key]['children'] = array_values($childrens);
                    //$i++;
                }
            }
        }
        return $arrNodes;
    }
    
    /**
     * Se consulta relación recursiva a traves de parent_id.
     * 
     * @return Zend_Db_Table_Select
     */
    
    public function select()
    {
        $select = new Zend_Db_Table_Select($this);
        $select->setIntegrityCheck(false); //de lo contrario no podemos hacer JOIN
        $select->from($this->_name)->joinLeft(array(
            'parent' => $this->_name
        ), "$this->_name.parent_id = parent.id", array(
            "parent_title" => "title",
            "parent_module" => "module"
        ))->joinLeft($this->_nameIcons, "$this->_name.icons_id=$this->_nameIcons.id", array(
            'icon_title' => 'title',
            'image'
        ))->where("$this->_name.id != ?", 0);
        
        //Si no pertenece al role_id 1, no puede ver módulos root
        if ($this->_user_info->acl_roles_id != ROLES_ROOT_ID) {
            $select->where("$this->_name.root != ?", "1");
        }
        
        return $select;
    }
    
    /**
     * @param $data Zend_Db_Table_Rowset
     * @return array
     * @see Zwei_Db_Table::overloadDataForm()
     */
    public function overloadDataForm($data)
    {
        $data           = $data->toArray();
        $modulesActions = new DbTable_AclModulesActions();
        $select         = $modulesActions->select()->where($modulesActions->getAdapter()->quoteInto("acl_modules_id = ?", $data['id']));
        
        Debug::writeBySettings($select->__toString(), 'query_log');
        
        $data['actions'] = array();
        $actions         = $modulesActions->fetchAll($select);
        if ($actions->count() > 0) {
            foreach ($actions as $a) {
                $data['actions'][] = $a['acl_actions_id'];
            }
        }
        
        if ($data['type'] == 'xml') {
            $data['content'] = file_get_contents(COMPONENTS_ADMIN_PATH . '/' . $data['module']);
        }
        
        return $data;
    }
    
    
    /**
     * Selecciona diferentes módulos, mostrando modulo padre .
     * 
     * @return Zend_Db_Table_Select
     */
    public function selectDistinct()
    {
        $select = new Zend_Db_Table_Select($this);
        $select->setIntegrityCheck(false); //de lo contrario no podemos hacer JOIN
        $select->from($this->_name, array(
            'id',
            'title' => 'title'
        ))->joinLeft(array(
            'parent' => $this->_name
        ), "$this->_name.parent_id = parent.id", array(
            "parent_title" => new Zend_Db_Expr("IF($this->_name.parent_id > 0, CONCAT(parent.title, '->', $this->_name.title), $this->_name.title)")
        ))->order("parent.id")->order("title");
        
        if ($this->_user_info->acl_roles_id != ROLES_ROOT_ID) {
            $select->where("$this->_name.root != ?", "1");
        }
        return $select;
    }
    
    /**
     * Selecciona los diferentes módulos para uso general.
     * 
     * @return Zend_Db_Table_Select 
     */
    
    public function getModules()
    {
        $select = new Zend_Db_Table_Select($this);
        $select->setIntegrityCheck(false); //de lo contrario no podemos hacer JOIN
        $select->from($this->_name, array(
            'id',
            'title' => 'title'
        ))->joinLeft(array(
            'parent' => $this->_name
        ), "$this->_name.parent_id = parent.id", array(
            "module_title" => new Zend_Db_Expr("IF($this->_name.parent_id > 0, CONCAT(parent.title, '->', $this->_name.title), $this->_name.title)")
        ))->where("$this->_name.id != ?", 0)->order("parent.id")->order("title");
        
        if ($this->_user_info->acl_roles_id != ROLES_ROOT_ID) {
            $select->where("$this->_name.root != ?", "1");
        }
        return $select;
    }
    
    /**
     * Retorna el id asociado a module.
     * 
     * @param string $module
     * @return int
     */
    public function getModuleId($module)
    {
        $select = new Zend_Db_Table_Select($this);
        $select->from($this->_name, array(
            'id'
        ))->where($this->getAdapter()->quoteInto('module = ?', $module));
        
        $row = $this->fetchRow($select);
        if (!$row) {
            Console::error("No se encuentra módulo $module");
            return false;
        } else {
            return $this->fetchRow($select)->id;
        }
    }
    
    /**
     * Obtiene la fila asociada al nombre del módulo.
     * 
     * @param string $module
     * @param array $fields
     * @return Zend_Db_Table_Row
     */
    public function findModule($module, $fields = array('*'))
    {
        $select = new Zend_Db_Table_Select($this);
        $select->from($this->_name, $fields)->where($this->getAdapter()->quoteInto('module = ?', $module));
        return $this->fetchRow($select);
    }
    
    /**
     * Obtiene las acciones asociadas al módulo.
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getActions($id)
    {
        $model  = new AclModulesActionsModel();
        $select = $model->select();
        $select->where($model->getAdapter()->quoteInto('acl_modules_id = ?', $id));
        Debug::writeBySettings($select->__toString(), 'query_log');
        return $model->fetchAll($select);
    }
}
