<?php
require_once ('Zwei/Db/Table.php');
class Zwei_Db_TableLoggeable extends Zwei_Db_Table
{
    public function insert($data)
    {
    	$last_insert_id = parent::insert($data);  
        if ($last_insert_id) $this->log("ADD", $last_insert_id);   
        return $last_insert_id;
    }
    
    public function update($data, $where)
    {
    	$update = parent::update($data, $where);
    	if ($update) $this->log("EDIT", $where);
        return $update;
    }
    
    public function delete($where)
    {
    	$delete = parent::delete($where);
        if ($delete) $this->log("DELETE", $where);
        return $delete;
    }   
    
    public function log($action, $condition) {
        if (self::$_defaultLogMode) {
            $logBook = new LogBookModel();
            
            $logData = array (
                "user" => $this->_user_info->user_name,
                "acl_roles_id" => $this->_user_info->acl_roles_id,
                "table" => $this->_name,
                "action" => $action,
                "ip" => $_SERVER['REMOTE_ADDR'],
                "stamp" => date("Y-m-d H:i:s")
            ); 
            
            if ($condition) $logData["condition"] = (string) $condition; 
            $logBook->insert($logData);
        }       
    }   
}
