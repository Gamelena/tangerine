<?php 
/**
 * Interface que debieran implementar los modelos no db 
 * para ser compatible con los modelos Zend_Db_Table que espera AdmPortal
 * @author rodrigo
 *
 */
interface Zwei_Admin_ModelInterface
{
    const PRIMARY          = 'primary';
    
    public function select();
    
    public function insert(array $data);
    
    public function update(array $data, $where);
    
    public function fetchAll();
    
    public function fetchRow();

}
