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

class AclModulesModel extends Zwei_Db_Table
{
    protected $_name = "acl_modules";
    protected $_label = "title";
    private $_approved = null;

    public function update($data, $where) 
    {
        if (empty($data['module'])) $data['module'] = NULL;
        if (empty($data['parent_id'])) $data['parent_id'] = NULL;
        
        $this->_ajax_todo = 'cargarArbolMenu';
        Zwei_Utils_File::clearRecursive(ROOT_DIR ."/cache");
        
        return parent::update($data, $where);
    }
    
    public function insert($data)
    {
        if (empty($data['module'])) $data['module'] = NULL;
        if (empty($data['parent_id'])) $data['parent_id'] = NULL;
        
        $this->_ajax_todo = 'cargarArbolMenu';
        Zwei_Utils_File::clearRecursive(ROOT_DIR ."/cache");

        return parent::insert($data);        
    } 
    
    public function delete($where)
    {
        return parent::delete($where);
    }
    
    
    public function setApproved($value=1)
    {
        $this->_approved = ($value == 1) ? '1' : '0';
    }

    /**
     * Obtiene los módulos hijos a partir de una id 
     * @param $parent_id
     * @return Array()
     */

    private function getChildrens($parent_id)
    {
        $childrens = $this->_acl->listGrantedResourcesByParentId($parent_id);
        if ($parent_id != 0) {
            foreach ($childrens as $i => $child) {
                $childrens[$i]['label'] = utf8_encode(html_entity_decode($child['title']));
                unset($childrens[$i]['title']);
                $prefix = "";

                if ($child['type'] == 'zend_module') $prefix = "";
                else if ($child['type'] == 'xml') $prefix = "index/components?p=";
                else if ($child['type'] == 'xml_mvc') $prefix = "index/components_mvc?p=";
                else if ($child['type'] == 'legacy') $prefix = "index/legacy?p=";
                else if ($child['type'] == 'iframe') $prefix = "index/iframe?p=";
                    
                if ($prefix != "") $child['module'] = urlencode($child['module']);

                $childrens[$i]['url'] = $prefix.utf8_encode(html_entity_decode($child['module']));
                unset($childrens[$i]['module']);
            }
        }
        return $childrens;
    }

    /**
     * Obtiene arbol de módulos en forma recursiva
     * @param $parent_id
     * @return Array()
     */
    public function getTree($parent_id = null)
    {
        $root = $this->_acl->listGrantedResourcesByParentId($parent_id);

        $arrNodes = array();
      
        //$i = 0;
        foreach ($root as $branch) {
            if ($branch['tree'] == '1') {
                $key = $branch['id'];
                $arrNodes[$key]['id']  = $branch['id'];
                $arrNodes[$key]['type']  = $branch['type'];
                $arrNodes[$key]['linkable']  = $branch['linkable'];
                $arrNodes[$key]['label'] = utf8_encode(html_entity_decode($branch['title']));
                if ($branch['linkable'] == '1') {
                    $prefix = "";
                    if ($branch['type'] == 'zend_module') {$prefix = "";}
                    else if ($branch['type'] == 'xml') {$prefix = "index/components?p=";}
                    else if ($branch['type'] == 'xml_mvc') {$prefix = "index/components_mvc?p=";}
                    else if ($branch['type'] == 'legacy') {$prefix = "index/legacy?p=";}
                    else if ($branch['type'] == 'iframe') {$prefix = "index/iframe?p=";}
                    
                    
                    if ($prefix != "") $branch['module'] = urlencode($branch['module']);
                    $arrNodes[$key]['url'] = $prefix.$branch['module'];
                }
                
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
        ->where("$this->_name.id != ?", 0)
        ;
        
        //Si no pertenece al role_id 1, no puede ver módulos root
        if ($this->_user_info->acl_roles_id != '1') {
            $select->where("$this->_name.root != ?", "1");
        }
        
        return $select;
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

        if ($this->_user_info->acl_roles_id != '1') {
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

        if ($this->_user_info->acl_roles_id != '1') {
            $select->where("$this->_name.root != ?", "1");
        }
        return $select;
    }
}
