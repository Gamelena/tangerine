<?php

/**
 * Modelo de datos para variables generales de configuración<br/>
 * Ejemplo:
 * <code>
 * -- Estructura Tabla
 * CREATE TABLE IF NOT EXISTS `web_settings` (
 `id` varchar(255) NOT NULL DEFAULT '',
 `enum` varchar(255) NOT NULL,
 `value` varchar(255) NOT NULL DEFAULT '0',
 `type` varchar(255) NOT NULL DEFAULT '',
 `description` text NOT NULL,
 `ord` int(11) NOT NULL DEFAULT '0',
 `group` varchar(255) NOT NULL,
 `function` varchar(255) NOT NULL,
 `approved` enum('0','1') NOT NULL,
 PRIMARY KEY (`id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=latin1;

 --
 -- Datos de ejemplo
 --

 INSERT INTO `web_settings` (`id`, `enum`, `value`, `type`, `description`, `ord`, `group`, `function`, `approved`) VALUES
 ('debug_path', '', 'log/debug', 'dojo_validation_textbox', '', 4, 'Debug', 'cleanDebugFile', '1'),
 ('titulo_adm', '', 'Administrador Bonos de Consumo', 'dojo_validation_textbox', '', 1, 'Admin', '', '1'),
 ('url_logo_oper', '', 'images/logo_movistar26x19.jpg', 'dojo_validation_textbox', '', 2, 'Admin', '', '1'),
 ('url_logo_gamelena', '', 'images/logo_gamelena26x15.png', 'dojo_validation_textbox', '', 3, 'Admin', '', '1');
 </code>
 *
 * @category Gamelena
 * @package  Models
 * @version  $Id:$
 * @since    0.1
 */

class SettingsModel extends Gamelena_Db_Table
{
    /**
     * Tabla principal.
     * 
     * @var string
     */
    protected $_name = "web_settings";
    
    /**
     * Selecciona grupos.
     * 
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function loadGroups()
    {
        $select = new Zend_Db_Table_Select($this);
        $select->distinct();
        $select->from($this->_name, 'group');
        Debug::writeBySettings($select->__toString(), 'query_log');
        return $this->fetchAll($select)->toArray();
    }
    
    /**
     * 
     * @param string $group.
     * 
     * @return Zend_Db_Table_Rowset_Abstract:
     */
    public function getSubGroups($group)
    {
        $select = new Zend_Db_Table_Select($this);
        $select->where($this->getAdapter()->quoteInto('`group` = ?', $group))->order('ord ASC');
        Debug::writeBySettings($select->__toString(), 'query_log');
        return $this->fetchAll($select)->toArray();
    }

}
