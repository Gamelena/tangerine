<?php
class Gamelena_Db_Table_Row extends Zend_Db_Table_Row_Abstract
{
    protected function _update()
    {
        $update = parent::_update();
        Console::log($this->_modifiedFields);
        return $update;
    }
}