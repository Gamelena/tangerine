<?php
/**
 * 
 * @author rodrigo.riquelme@zweicom.com
 *
 */
class Zwei_Utils_Db
{
    /**
     * Obtiene un array con nombres de tablas rotadas, tipo "transacciones_20121222"  
     * 
     * @param datetime - fecha desde 'Y-m-d h:i:s'
     * @param datetime - fecha hasta 'Y-m-d h:i:s'
     * @param string - alias de columna (ejecutar "SHOW TABLES" en MySQL para saber cual es este alias)
     * @param string - prefijo de tabla que es seguido por fecha resultante 
     * @param string "days"|"hours"
     * @return array
     */
    public function getRotatedTables($from, $to, $whereAlias, $prefix, $interval="days")
    {
        $possibleTables = array();
        $realTables = array();
        $interval = Zwei_Utils_Time::createInterval (
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

        Debug::writeBySettings($query, 'query_log', "SI");
        $realTables = $this->getAdapter()->fetchAll($query);

        return $realTables; 
    }
    
}