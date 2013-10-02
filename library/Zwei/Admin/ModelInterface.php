<?php 
interface Zwei_Admin_ModelInterface
{
    const PRIMARY          = 'primary';
    
    public function select();
    
    public function fetchAll();
    
    public function fetchRow();


}
