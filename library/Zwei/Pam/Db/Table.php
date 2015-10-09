<?php
/**
 * 
 * Table Gateway para notificar a PAM despues de operaciones CRUD - R
 *
 */
class Zwei_Pam_Db_Table extends Zwei_Db_Table
{
    /**
     * 
     * @var Zend_Config
     */
    private $_config;
    
    /**
     * 
     * @var string
     */
    private $_platformHost;
    
    /**
     * @return void
     * @see Zwei_Db_Table::init()
     */
    public function init()
    {
        $this->_config = Zwei_Controller_Config::getOptions();
        $this->_platformHost = $this->_config->zwei->platform->host;
        parent::init();
    }
    
    /**
     * Inserts a new row.
     *
     * @param  array  $data  Column-value pairs.
     * @return mixed         The primary key of the row inserted.
     * @see Zwei_Db_Table::insert()
     */
    public function insert(array $data) 
    {
        $insert = parent::insert($data);
        if ($insert) {
            $this->notifyGda(__FUNCTION__, null, $insert);
        }
        return $insert;
    }
    /**
     * Updates existing rows.
     *
     * @param  array        
     * @param  array|string 
     * @return int          
     * @see Zwei_Db_Table::update()
     */
    public function update(array $data, $where)
    {
        $update = parent::update($data, $where);
        if ($update) {
            $notifyGda = $this->notifyGda(__FUNCTION__, $where);
        }
        return $update;
    }
    
    /**
     * Deletes existing rows.
     *
     * @param  array|string $where SQL WHERE clause(s).
     * @return int          The number of rows deleted.
     * @see Zwei_Db_Table::delete()
     */
    public function delete($where)
    {
        $delete = parent::delete($where);
        if ($delete) {
            $notifyGda = $this->notifyGda(__FUNCTION__, $where);
        }
        return $delete;
    }
    /**
     * 
     * @param string $operation
     * @param string $where
     * @param string $lastInsertedId
     * @return array
     */
    public function notifyGda($operation, $where = null, $lastInsertedId = null)
    {
        $arrKeys = array();
        if ($lastInsertedId) {
            if (is_array($lastInsertedId)) {
                $j = 0;
                foreach ($lastInsertedId as $i => $v) {
                    //$arrKeys[] = "keys[$i]=$v&";
                    $j++;
                    $arrKeys[] = "keys$j=$i";
                    $arrKeys[] = "values$j=$v";
                }
            } else {
                $primary = array_values($this->info(self::PRIMARY));
                //$arrKeys[] = "keys[{$primary[0]}]=$lastInsertedId";
                $arrKeys[] = "keys1={$primary[0]}";
                $arrKeys[] = "values1=$lastInsertedId";
            }
        } else {
            $aWhere = self::whereToArray($where);
            $j=0;
            foreach ($aWhere as $i => $v) {
                //$arrKeys[] = "keys[$i]=$v&";
                $j++;
                $arrKeys[] = "keys$j=$i";
                $arrKeys[] = "values$j=$v";
            }
        }
        $keys = implode("&", $arrKeys);
        $url = $this->_platformHost.'/FactNotify';
        $params = "table={$this->info(self::NAME)}&{$keys}&operation=$operation";
        
        $response = Zwei_Utils_File::getResponseFromService( 
            $url, 
            $params 
        );
        try {
            libxml_use_internal_errors(true);
            $xml = (array) new SimpleXMLElement($response['response']);
        } catch (Exception $e) {
            Console::error(array("Error XML en respuesta de $url?$params a notificación.", libxml_get_errors(), $response['response']));
            $this->setMessage("Datos Guardardos. Ha ocurrido un error XML en respuesta de notificación.");
            return null;
        }
        
        try {
            $json = Zend_Json::decode($xml['p']);
        } catch (Zend_Json_Exception $e) {
            Console::error(array("Error JSON en respuesta de $url?$params a notificación.", $xml['p']));
            $this->setMessage("Datos Guardardos. Ha ocurrido Error JSON en respuesta de notificación.");
            return null;
        }
        
        return $json;
        
    }
}