<?php
/**
 * Extiende Zend_Db_Table guardando en log las acciones crear, modificar, eliminar
 *
 * @package Zwei_Db
 * @version $Id:$
 * @since 0.1
 */
require_once ('Zwei/Db/Table.php');
class Zwei_Db_TableLoggeable extends Zwei_Db_Table
{
    /**
     * (non-PHPdoc)
     * @see Zwei_Db_Table::insert()
     */
    public function insert($data)
    {
        $last_insert_id = parent::insert($data);
        if ($last_insert_id !== false && self::$_defaultLogMode) {
            self::log("Agregar", $last_insert_id);
        }   
        return $last_insert_id;
    }
    
    /**
     * (non-PHPdoc)
     * @see Zwei_Db_Table::update()
     */
    public function update($data, $where)
    {
        $update = parent::update($data, $where);
        Debug::write($update);
        if (is_array ($where)) {
            $where = array_values($where);
            $where = (count($where) == 1) ? $where[0] : print_r($where, true);
            $where = print_r($where, true);
        }
        if ($update && self::$_defaultLogMode) {
            self::log("Editar", $where);
        }
        return $update;
    }
    
    /**
     * (non-PHPdoc)
     * @see Zwei_Db_Table::delete()
     */
    public function delete($where)
    {
    	$delete = parent::delete($where);
        if ($delete && self::$_defaultLogMode){
            self::log("Eliminar", $where);
        }
        return $delete;
    }   
}
