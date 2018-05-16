<?php 
/**
 * Interface que debieran implementar los modelos no db 
 * para ser compatible con los modelos Zend_Db_Table que espera Tangerine
 * @author rodrigo
 */
interface Gamelena_Admin_ModelInterface
{
    const PRIMARY          = 'primary';
    
    public function select();
    
    public function fetchAll();
    
    public function fetchRow();

}
