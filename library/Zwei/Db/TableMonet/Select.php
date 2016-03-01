<?php
class Zwei_Db_TableMonet_Select extends Zend_Db_Select
{
    /**
     * Se adapta string SQL select a SQL Monet DB
     * @see Zend_Db_Select::__toString()
     */
    public function __toString()
    {
        $select = str_replace('`', '"', parent::__toString());
        return $select;
    }
    
}