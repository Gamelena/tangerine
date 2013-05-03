<?php
/**
 * Crea un objeto Zend_Db_Select o Zend_Db_Table_Select según parámetros enviados por Zwei_Utils_Form ($_REQUEST),
 * generalmente es instanciado por ObjectsController pero puede instanciado en cualquier lugar, 
 * por ejemplo para sobrecargar manualmente Zwei_Db_Table::select() usando parámetros equivalentes.
 * @example
 * <code>
 * //Recibe parametros $_REQUEST, debe existir $_REQUEST['model'] esto equivale a $form->model. 
 * $form = new Zwei_Utils_Form();
 * $object = new Zend_Db_Object($form);
 * $select = $object->select(); 
 * </code>
 * <code>
 * //Recibe parametros $_REQUEST como primer param y un Zend_Db_Select como segundo param, el array $form, filtrará ese objecto Zend_Db_Select.
 * $form = new Zwei_Utils_Form();
 * $config = new Zend_Config_Ini(ROOT_DIR.'/application/configs/application.ini', APPLICATION_ENV);
 * $db = Zend_Db::factory($config->resources->multidb->dn);
 * $select = $db->select();
 * $object = new Zwei_Db_Object($form, $select);
 * $select = $object->select();  
 * </code>
 * @category Zwei
 * @package Zwei_Db
 * @version $Id:$
 * @since 0.1
 */
class Zwei_Db_Object
{
    /**
     * 
     * @var Zwei_Utils_Form
     */
    private $_form;
    
    /**
     * @var Zwei_Db_Table
     */
    private $_model;

    /**
     * @var Zend_Db_Select
     */
    private $_select;
    
    /**
     * 
     * @param Zwei_Utils_Form | array
     * @param Zwei_Db_Select
     */
    public function __construct($form, $select=false)
    {
        if (is_a($form, 'Zwei_Db_Object')) {
            $this->_form = $form;
        } else { //en este punto $form debe ser array
            $this->_form = new Zwei_Utils_Form($form);
        }    
        if (!empty($this->_form->model)) {
            $model = $this->_form->model;
            $this->_model = new $model();
        }
        if ($select) $this->_select = $select;
    }
    /**
     * 
     * @return Zend_Db_Select
     */
    public function select()
    {
        $oModel = $this->_model;
        $oSelect = isset($this->_select) ? $this->_select : $oModel->select();
        
        if (isset($this->_form->search) && !$oModel->isFiltered()) {
            $search = $this->_form->search;
            $allowedOperators = array('LIKE', '=', '<>', '<', '>', '<=', '>=', '<>', '!=', 'BETWEEN');

            foreach ($search as $i => $s) {
                $field = !strstr($i, ".") && !empty($i) ? "`$i`" : $i;
                if (isset($s['operator'])) {
                    $op = in_array($s['operator'], $allowedOperators) ? $s['operator'] : 'LIKE';
                } else {
                    $op = 'LIKE';
                    $sufix = '%';
                    $prefix = '%';
                }
                
                /**
                 * BETWEEN se aplica sobre un campo único, la diferencia en los valores las hacen los sufijos y prefijos concatenados al valor del campo
                 * @example " BETWEEN $fecha 00:00:00 AND $fecha 23:59:59 ", 
                 * la unica razón del soporte de BETWEEN es poder sacar un rango de valores o intérvalo 
                 * a partir de un campo único + sufijos y prefijos.
                 * 
                 * NO se puede usar BETWEEN entre campos diferentes, para esto deben usarse los operadores <, >, <=, >= que hacen lo mismo.
                 */
                if ($op == 'BETWEEN') {
                    $sufix0 = isset($s['sufix'][0]) ? $s['sufix'][0] : '';
                    $prefix0 = isset($s['prefix'][0]) ? $s['prefix'][0] : '';
                    $sufix1 = isset($s['sufix'][1]) ? $s['sufix'][1] : '';
                    $prefix1 = isset($s['prefix'][1]) ? $s['prefix'][1] : '';
                    
                    $oSelect->where($oModel->getAdapter()->quoteInto("$field >= ?", "{$prefix0}{$s['value']}{$sufix0}%"));
                    $oSelect->where($oModel->getAdapter()->quoteInto("$field <= ?", "{$prefix1}{$s['value']}{$sufix1}%"));
                } else {
                    $sufix = isset($s['sufix']) ? $s['sufix'] : '';
                    $prefix = isset($s['prefix']) ? $s['prefix'] : '';
                    
                    $oSelect->where($oModel->getAdapter()->quoteInto("$field $op ?", "{$prefix}{$s['value']}{$sufix}%"));
                }
            }
        }

        if (isset($this->_form->group)) {
            $groups = explode(';', $this->_form->group);
            if (!is_array($groups)) {
                $groups = array($groups);
            } 
            
            foreach ($groups as $g) {
                $g = preg_replace('/[^(\x20-\x7F)]*/', '', $g);
                $oSelect->group($g);
            }

        }

        $start = (isset($this->_form->start)) ? $this->_form->start : 0; 
        $count = (isset($this->_form->count)) ? $this->_form->count : 20000;//dojo.data.QueryReadStore usa count en lugar de limit
        $sort = (isset($this->_form->sort)) ? $this->_form->sort : false;
        
        if ($sort && (is_a($oSelect, "Zend_Db_Table_Select") || is_a($oSelect, "Zend_Db_Select"))) {
            $oSelect->reset(Zend_Db_Select::ORDER);
            if (preg_match("/^-(.*)/", $sort)) {
                $sort = substr($sort, 1);
                $oSelect->order("$sort DESC");
            } else {
                $oSelect->order($sort);
            }
        }
        
        if (is_a($oSelect, "Zend_Db_Table_Select") || is_a($oSelect, "Zend_Db_Select")) $oSelect->limit($count, $start);
        
        //Se imprime query en log debug según configuración del sitio
        if (is_a($oSelect, "Zend_Db_Table_Select") || is_a($oSelect, "Zend_Db_Select")) Zwei_Utils_Debug::writeBySettings($oSelect->__toString(), 'query_log');
        if (is_a($oSelect, "Zend_Db_Table_Select") || is_a($oSelect, "Zend_Db_Select")) Zwei_Utils_Debug::writeBySettings($oSelect->getAdapter()->getConfig(), 'query_log');
        return $oSelect;
    }
}
