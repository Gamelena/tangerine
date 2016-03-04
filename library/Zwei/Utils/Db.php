<?php
/**
 * 
 * @author rodrigo.riquelme@gamelena.com
 */
class Zwei_Utils_Db
{
    /**
     * Obtiene un array con nombres de tablas rotadas, tipo "transacciones_20121222".
     * 
     * @param  datetime|null - fecha desde 'Y-m-d h:i:s'
     * @param  datetime|null - fecha hasta 'Y-m-d h:i:s'
     * @param  string - alias de columna (ejecutar "SHOW TABLES" en MySQL para saber cual es este alias)
     * @param  string - prefijo de tabla que es seguido por fecha resultante 
     * @param  string "days"|"hours"
     * @param  Zend_Db_Table                                                                             $table
     * @return array
     */
    public static function getRotatedTables($from, $to, $whereAlias, $prefix, $interval = "days", Zend_Db_Table_Abstract $table=null)
    {
        //Si se llama esto dentro de un modelo y no es especificado $adapter, ser usará el adapter del modelo.
        $adapter = $table ? $table->getAdapter() : $this->getAdapter();
        
        $possibleTables = array();
        $realTables = array();
        if (!is_null($from) && !is_null($to)) {
        
            $interval = Zwei_Utils_Time::createInterval(
                Zwei_Utils_Time::datetimeToTimestamp($from), 
                Zwei_Utils_Time::datetimeToTimestamp($to),
                $interval
            );
            
            foreach ($interval as $stamp)
            {
                $possibleTables[] = "'$prefix".date("Ymd", $stamp)."'";
            }
            
            $possibleTables = implode(",", $possibleTables);
    
            $query = "SHOW TABLES WHERE $whereAlias IN ($possibleTables)";
        } else {
            $query = "SHOW TABLES LIKE '$prefix'";
            $whereAlias .= " ($prefix)";
        }
        Debug::writeBySettings($query, 'query_log');
        
        $realTables = $adapter->fetchAll($query);

        $return = array();
        foreach ($realTables as $v) {
            $return[] = $v[$whereAlias];
        }
        
        return $return; 
    }
    
    /**
     * Hace un backup de estructura y datos de tabla $table MySQL.
     * 
     * @param  Zend_Db_Table_Abstract $table 
     * @return string|false - Nombre tabla creada
     */
    public static function backupTable(Zend_Db_Table_Abstract $table = null, $sufix = '_bkp')
    {
        //Si se llama esto dentro de un modelo y no es especificado $adapter, ser usará el adapter del modelo.
        $adapter = $table ? $table->getAdapter() : $this->getAdapter;
        
        $name = $table->info(Zend_Db_Table::NAME);
        $adapter->query("CREATE TABLE `$name$sufix` LIKE `$name`");
        return $adapter->query("INSERT INTO `$name$sufix` SELECT * FROM `$name`") ? $name . $sufix : false;
    }
}