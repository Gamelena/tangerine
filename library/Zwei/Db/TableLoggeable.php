<?php
class Zwei_Db_TableLoggeable extends Zwei_Db_Table
{
    public function insert($data)
    {
    	$last_insert_id = parent::insert($data);  
        if ($last_insert_id) $this->log("ADD", $last_insert_id);   
        return true;
    }
    
    public function update($data, $where)
    {
        $this->log("EDIT", $where);
        return parent::update($data, $where);
    }
    
    public function delete($where)
    {
        $this->log("DELETE", $where);
        return parent::delete($where);
    }   
    
    public function log($action, $condition) {
        if (self::$_defaultLogMode) {
            $logBook = new LogBookModel();
            
            $logData = array (
                "user" => $this->_user_info->user_name,
                "acl_roles_id" => $this->_user_info->acl_roles_id,
                "table" => $this->_name,
                "action" => $action
            ); 
            
            if ($condition) $logData["condition"] = (string) $condition; 
            $logBook->insert($logData);
        }       
    }   
}
