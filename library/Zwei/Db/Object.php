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
     * Operadores permitidos para usar en $_REQUEST
     * @var array
     */
    private $_allowedOperators = array('like', 'between', '=', '!=', '<>', '<', '>', '<=', '>=');
    
    
    /**
     * 
     * @param Zwei_Utils_Form | array
     * @param Zwei_Db_Select
     */
    public function __construct($form, $select = false)
    {
        if (is_a($form, 'Zwei_Utils_Form')) {
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
        $this->_select = isset($this->_select) ? $this->_select : $this->_model->select();
        
        if (isset($this->_form->search) && !$this->_model->isFiltered()) {
            $search = $this->_form->search;
            $sufix = array();
            $iterated = array();
            //Iteración por cada item del buscador
            foreach ($search as $i => $s) {
                if (!isset($s['value']) && is_array($s)) {
                    //Iteración por cada item duplicado de buscador, esto se usa para diferentes operadores (ej: '<' y '>')
                    foreach ($s as $s2) {
                        $this->iterateSearcher($i, $s2);
                    }
                } else {
                    $this->iterateSearcher($i, $s);
                }
            }
        }

        if (isset($this->_form->group)) {
            $groups = explode(';', $this->_form->group);
            if (!is_array($groups)) {
                $groups = array($groups);
            } 
            
            foreach ($groups as $g) {
                $g = preg_replace('/[^(\x20-\x7F)]*/', '', $g);//Truco para eliminar caracteres no texto
                $this->_select->group($g);
            }
        }

        $start = (isset($this->_form->start)) ? $this->_form->start : 0; 
        $count = (isset($this->_form->count)) ? $this->_form->count : false;
        $sort = (isset($this->_form->sort)) ? $this->_form->sort : false;
        
        if ($sort && $this->_select instanceof Zend_Db_Select) {
            $this->_select->reset(Zend_Db_Select::ORDER);
            if (preg_match("/^-(.*)/", $sort)) {
                $sort = substr($sort, 1);
                $this->_select->order("$sort DESC");
            } else {
                $this->_select->order($sort);
            }
        }
        
        if ($count && $this->_select instanceof Zend_Db_Select) {
            $this->_select->limit($count, $start);
        }
        
        //Se imprime query en log debug según configuración del sitio
        if ($this->_select instanceof Zend_Db_Select) {
            Zwei_Utils_Debug::writeBySettings($this->_select->__toString(), 'query_log');
        }
        return $this->_select;
    }
    /**
     * Recarga objeto Zend_Db_Select con parametros generados en buscador de Admportal
     * 
     * @param string $i nombre del campo
     * @param array('operator', 'prefix', 'sufix') $s contiene operador, valor, sufijo, prefijo
     * @return void
     */
    protected function iterateSearcher($i, $s)
    {
        $field = !strstr($i, ".") && !empty($i) ? "`$i`" : $i;
        $op = 'like';//Operador por defecto
        $sufix = '%';//Sufijo por defecto
        $prefix = '%';//Prefijo por defecto
        $s['value'] = htmlentities($s['value']);
        
        if (!empty($s['value']) || $s['value'] === '0') {
            if (!empty($s['operator'])) {
                if (in_array($s['operator'], $this->_allowedOperators)) {
                    $op = $s['operator'];
                    $sufix = isset($s['sufix'][0]) ? $s['sufix'][0] : '';
                    $prefix = isset($s['prefix'][0]) ? $s['prefix'][0] : '';
                }
            } else {
                if (isset($s['sufix'][0])) $sufix = $s['sufix'][0];
                if (isset($s['prefix'][0])) $sufix = $s['prefix'][0];
            }
        
        
            /**
             * 'between' se aplica sobre un campo único, la diferencia en los valores las hacen los sufijos y prefijos concatenados al valor del campo
             * La razón del soporte de BETWEEN es poder usar un CAMPO ÚNICO + sufijos y prefijos,
             * esto es preferible a usar funciones SQL sobre columnas (por ejemplo DATE_FORMAT) ya que al transformar la columna inutilizamos sus índices.
             *
             * NO se puede usar 'between' entre campos diferentes, para esto deben usarse los operadores <, >, <=, >= que hacen lo mismo con la misma performance y son compatibles con Zend_Db_Select.
             *
             * @example
             * "WHERE fecha >= '$fecha 00:00:00' AND fecha <= '$fecha 23:59:59' ",
             * <group operator="between">
             *     <element target="fecha" sufix=" 00:00:00"/>
             *     <element target="fecha" sufix=" 23:59:59"/>
             * </group>
             */
            if ($op == 'between') {
                $sufix0 = isset($s['sufix'][0]) ? $s['sufix'][0] : '';
                $prefix0 = isset($s['prefix'][0]) ? $s['prefix'][0] : '';
                $sufix1 = isset($s['sufix'][1]) ? $s['sufix'][1] : '';
                $prefix1 = isset($s['prefix'][1]) ? $s['prefix'][1] : '';
                
                
                
                $this->_select->where($this->_model->getAdapter()->quoteInto("$field >= ?", "{$prefix0}{$s['value']}{$sufix0}"));
                $this->_select->where($this->_model->getAdapter()->quoteInto("$field <= ?", "{$prefix1}{$s['value']}{$sufix1}"));

            } else {
                $this->_select->where($this->_model->getAdapter()->quoteInto("$field $op ?", "{$prefix}{$s['value']}{$sufix}"));
            }
        }
    }
}
