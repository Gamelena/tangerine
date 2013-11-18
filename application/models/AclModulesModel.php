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
    protected $_name = "acl_modules";
    protected $_nameIcons = "web_icons";
    protected $_label = "title";
    protected $_dataActions = array();
    
    /**
     * (non-PHPdoc)
     * @see Zwei_Db_Table::update()
     */
    public function update($data, $where) 
    {
        $data = $this->cleanDataParams($data);
        $myWhere = $this->whereToArray($where);
        
        $saveActions = $this->saveDataActions($myWhere['id']);
        
        $this->_ajax_todo = 'cargarArbolMenu';
        
        $update = parent::update($data, $where);
        Zwei_Utils_File::clearRecursive(ROOT_DIR ."/cache");
        
        return $saveActions || $update;
    }
    
    /**
     * (non-PHPdoc)
     * @see Zwei_Db_Table::insert()
     */
    public function insert($data)
    {
        $data = $this->cleanDataParams($data);
        $this->_ajax_todo = 'cargarArbolMenu';
        
        $lastInsertedId = parent::insert($data);
        $saveActions = $this->saveDataActions($lastInsertedId);
        
        return $lastInsertedId;
        
    } 
    
    /**
     * (non-PHPdoc)
     * @see Zwei_Db_Table::cleanDataParams()
     */
    protected function cleanDataParams($data)
    {
        if (empty($data['module'])) $data['module'] = null;
        if (empty($data['parent_id'])) $data['parent_id'] = null;
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
        $ad = $modulesAction->getAdapter();
        $insert = false;
        $delete = false;
        //Borrar Todas las acciones del modulo, excepto los marcados
        $list = !empty($this->_dataActions) ?
            implode(",", $this->_dataActions) :
            false;
        
        $where = array();
        
        $where[] =$ad->quoteInto('acl_modules_id = ?', $aclModulesId);
        if ($list) $where[] = "acl_actions_id NOT IN ($list)";    
            
        $delete = $modulesAction->delete($where);
        
        foreach ($this->_dataActions as $v) {
            $data = array(
                    'acl_actions_id' => str_replace('"', '',$v), /*[FIXME] evitar el str_replace() acá, de momento es necesario usarlo */
                    'acl_modules_id' => $aclModulesId
            );
        
            try {
                $insert = $modulesAction->insert($data);
            } catch (Zend_Db_Exception $e) {
                if ($e->getCode() == '23000') {
                    $printData = print_r($data, 1);
                    Debug::write("Ya existe modulo_accion asociado a $printData");
                }
            }
        }
        return $insert || $delete;
    }
    
    /**
     * (non-PHPdoc)
     * @see Zwei_Db_Table::delete()
     */
    public function delete($where)
    {
        $modulesActions = new AclModulesActionsModel();
        
        $whereArray = self::whereToArray($where);
        $whereAclModulesId = "acl_modules_id={$whereArray['id']}";
        $modulesActions->delete($whereAclModulesId);
        
        return parent::delete($where);
    }
    
    
    /**
     * Obtiene arbol de módulos en forma recursiva
     * @param $parent_id
     * @return Array()
     */
    public function getTree($parent_id = null, $noTree = false)
    {
        $root = $this->_acl->listGrantedResourcesByParentId($parent_id);
        
        $arrNodes = array();
        //$i = 0;
        foreach ($root as $branch) {
            if ($branch['tree'] == '1' || $noTree) {
                $key = $branch['id'];
                $arrNodes[$key]['id']  = $branch['id'];
                $arrNodes[$key]['type']  = $branch['type'];
                $arrNodes[$key]['image']  = $branch['image'];
                $arrNodes[$key]['refresh_on_load']  = $branch['refresh_on_load'];
                
                $arrNodes[$key]['label'] = PHP_VERSION_ID >= 50400 ? html_entity_decode($branch['title']) : utf8_encode(html_entity_decode($branch['title']));

                $prefix = "";
                if ($branch['type'] == 'zend_module') {$prefix = "";}
                else if ($branch['type'] == 'xml') {$prefix = "admin/components?p=";}
                else if ($branch['type'] == 'legacy') {$prefix = "admin/legacy?p=";}
                else if ($branch['type'] == 'iframe') {$prefix = "admin/iframe?p=";}
                
                
                if ($prefix != "") $branch['module'] = urlencode($branch['module']);
                if ($branch['type']) $arrNodes[$key]['url'] = $prefix.$branch['module'];

                
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
     * Se rescribe select por defecto para mostrar relacion recursiva a traves de parent_id
     * @return Zend_Db_Table_Select
     */

    public function select()
    {
        $select=new Zend_Db_Table_Select($this);
        $select->setIntegrityCheck(false); //de lo contrario no podemos hacer JOIN
        $select->from($this->_name)
            ->joinLeft(array('parent'=>$this->_name), "$this->_name.parent_id = parent.id", array("parent_title"=>"title", "parent_module"=>"module"))
            ->joinLeft($this->_nameIcons, "$this->_name.icons_id=$this->_nameIcons.id", array('icon_title' => 'title', 'image'))
            ->where("$this->_name.id != ?", 0)
        ;
        
        //Si no pertenece al role_id 1, no puede ver módulos root
        if ($this->_user_info->acl_roles_id != ROLES_ROOT_ID) {
            $select->where("$this->_name.root != ?", "1");
        }
        
        return $select;
    }
    
    /**
     * @param $data Zend_Db_Table_Rowset
     * (non-PHPdoc)
     * @see Zwei_Db_Table::overloadDataForm()
     */
    public function overloadDataForm($data)
    {
        $data = $data->toArray();
        $modulesActions = new DbTable_AclModulesActions();
        $select = $modulesActions->select()->where($modulesActions->getAdapter()->quoteInto("acl_modules_id = ?", $data['id']));
        
        Debug::writeBySettings($select->__toString(), 'query_log');
        
        $data['actions'] = array();
        $actions = $modulesActions->fetchAll($select);
        if ($actions->count() > 0) {
            foreach ($actions as $a) {
                $data['actions'][] = $a['acl_actions_id'];
            }
        }
        return $data;
    }
    

    /**
     * Selecciona diferentes módulos, mostrando modulo padre 
     * @return Zend_Db_Table_Select
     */
    public function selectDistinct()
    {
        $select=new Zend_Db_Table_Select($this);
        $select->setIntegrityCheck(false); //de lo contrario no podemos hacer JOIN
        $select->from($this->_name, array('id','title'=>'title'))
        ->joinLeft(array('parent'=>$this->_name), "$this->_name.parent_id = parent.id",
            array("parent_title"=>new Zend_Db_Expr("IF($this->_name.parent_id > 0, CONCAT(parent.title, '->', $this->_name.title), $this->_name.title)")))
        ->order("parent.id")  
        ->order("title")
        ;

        if ($this->_user_info->acl_roles_id != ROLES_ROOT_ID) {
            $select->where("$this->_name.root != ?", "1");
        }
        return $select;
    }

    /**
     * Selecciona diferentes módulos para uso general
     * @return Zend_Db_Table_Select 
     */

    public function getModules()
    {
        $select=new Zend_Db_Table_Select($this);
        $select->setIntegrityCheck(false); //de lo contrario no podemos hacer JOIN
        $select->from($this->_name, array('id','title'=>'title'))
        ->joinLeft(array('parent'=>$this->_name), "$this->_name.parent_id = parent.id",
            array("module_title"=>new Zend_Db_Expr("IF($this->_name.parent_id > 0, CONCAT(parent.title, '->', $this->_name.title), $this->_name.title)")))
        ->where("$this->_name.id != ?", 0)
        ->order("parent.id")  
        ->order("title")
        ;

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
    public function getModuleId($module) {
        $select = new Zend_Db_Table_Select($this);
        $select->from($this->_name, array('id'))->where($this->getAdapter()->quoteInto('module = ?', $module));
        Debug::writeBySettings($select->__toString(), 'query_log');
        return $this->fetchRow($select)->id;
    }
    
    /**
     * Obtiene la fila asociada al nombre del modulo
     * 
     * @param string $module
     * @param array() $fields
     * @return Zend_Db_Table_Row
     */
    public function findModule($module, $fields = array('*')) {
        $select = new Zend_Db_Table_Select($this);
        $select->from($this->_name, $fields)->where($this->getAdapter()->quoteInto('module = ?', $module));
        Debug::writeBySettings($select->__toString(), 'query_log');
        return $this->fetchRow($select);
    }
    
    public function getActions($id) {
        $model = new AclModulesActionsModel();
        $select = $model->select(); 
        $select->where($model->getAdapter()->quoteInto('acl_modules_id = ?', $id));
        Debug::writeBySettings($select->__toString(), 'query_log');
        return $model->fetchAll($select);
    }
}
