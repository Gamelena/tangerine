<?php 
interface Zwei_Admin_ModelInterface
{
    const PRIMARY          = 'primary';
    
    public function select();
    
    public function fetchAll();
    
    public function fetchRow();
    /**
     * Returns table information.
     *
     * You can elect to return only a part of this information by supplying its key name,
     * otherwise all information is returned as an array.
     *
     * @param  string $key The specific info part to return OPTIONAL
     * @return mixed
     * @throws Zend_Db_Table_Exception
     */
    public function info($key=null);

}
