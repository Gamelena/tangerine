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
    
    public function fetchAll();
    
    public function fetchRow();
<<<<<<< HEAD
    
    public function info($key);
=======
>>>>>>> f306af8cbc860e73b2c8de2e6c526d3db946b5d4

}
