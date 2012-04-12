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
	private $approved = null;


	public function setApproved($value=1){
		$this->approved=($value==1)?'1':'0';
	}

	/**
	 * Obtiene los módulos hijos a partir de una id 
	 * @param $parent_id
	 * @return Array()
	 */

	private function getChildrens($parent_id)
	{
		$childrens = $this->_acl->listGrantedResourcesByParentId($parent_id);
		if ($parent_id != 0){
			foreach ($childrens as $i => $child){
				$childrens[$i]['label'] = utf8_encode(html_entity_decode($child['title']));
				unset($childrens[$i]['title']);
				$childrens[$i]['url'] = "index/components?p=".utf8_encode(html_entity_decode($child['module']));
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
	public function getTree($parent_id = 0)
	{
		$root = $this->getChildrens($parent_id);

		$arrNodes = array();
	  
		$i = 0;
		foreach($root as $branch)
		{
			if($branch['tree'] == '1'){
				$key = ($branch['parent_id'] == '0') ? $branch['id'] : $i;
				$arrNodes[$key]['id']  = $branch['id'];
				$arrNodes[$key]['label'] = utf8_encode(html_entity_decode($branch['title']));
				if ($branch['linkable'] == '1') {
					$arrNodes[$key]['url'] = "index/components?p=".$branch['module'];
				}
				if ($this->getChildrens($branch['id'])) {
					$arrNodes[$key]['children'] = $this->getChildrens($branch['id']);
					$i++;
				}
			}
		}
		return $arrNodes;
	}

	/**
	 * Se rescribe select por defecto para mostrar relacion recursiva a traves de parent_id
	 * @return Zend_Db_Table_Select
	 */

	public function select(){
		$select=new Zend_Db_Table_Select($this);
		$select->setIntegrityCheck(false); //de lo contrario no podemos hacer JOIN
		$select->from($this->_name, array('id','parent_id','title','module','tree','linkable','approved'))
		->joinLeft(array('parent'=>$this->_name), "$this->_name.parent_id = parent.id", array("parent_title"=>"title", "parent_module"=>"module"))
		//->joinLeft($this->_name_approved, "a.approved=$this->_name_approved.id",array("approved_title"=>"title"))
		;
		//Zwei_Utils_Debug::write($select->__toString());
		return $select;
	}

	/**
	 * Selecciona diferentes módulos, para combobox de módulo padre 
	 * evitando colisiones de nombre dada la relación recursiva 
	 * en módulo "módulos"
	 * @return Zend_Db_Table_Select
	 */

	public function selectDistinct()
	{
		$select=new Zend_Db_Table_Select($this);
		$select->distinct()
		->from($this->_name, array('id','parent_title'=>'title'))
		;

		if($this->_user_info->acl_roles_id!='1'){
				
		}

		return $select;
	}

	/**
	 * Selecciona diferentes módulos para uso general
	 * @return Zend_Db_Table_Select 
     * [TODO] redunda la funcionalidad de selectDistinct() pero cambia 'parent_title' por 'module_title'
	 */

	public function getModules()
	{
		$select=new Zend_Db_Table_Select($this);
		$select->distinct()
		->from($this->_name, array('id','module_title'=>'title'))
		;
		return $select;
	}
}
?>
