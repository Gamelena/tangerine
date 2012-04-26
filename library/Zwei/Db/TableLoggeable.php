<?php
class Zwei_Db_TableLoggeable extends Zwei_Db_Table
{
    public function insert($data)
    {
    	$last_insert_id = parent::insert($data);  
        if ($last_insert_id) $this->log("ADD", $data, $last_insert_id);   
        return true;
    }
    
    public function update($data, $where)
    {
        $this->log("EDIT", $data, $where);
        return parent::update($data, $where);
    }
    
    public function delete($where)
    {
        $this->log("DELETE", false, $where);
        return parent::delete($where);
    }   
    
    public function log($action, $data, $condition=false) {
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
