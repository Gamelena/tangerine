<?php
/**
 * 
 * Clase auxiliar para modelos que trabajen con datos con cardinalidad muchos a muchos. 
 * 
 * @example
 * <code>
 * $manyToMany = new Helpers_ManyToManyCrud('usuarios_id', 'permisos_id', array(new UsuariosPermisosModel()));
 * </code>
 *  
 * @see DatosDeCobroModel
 * @see ServicioModel
 *
 */
class Helpers_ManyToManyCrud
{
    /**
     * 
     * @var string
     */
    protected $_idNameFrom;
    
    /**
     * @var string
     */
    protected $_idNameTo;
    
    /**
     * 
     * @var array[string]=>Zend_Db_Table
     */
    protected $_modelsMap = array();
    
    /**
     * 
     * @var array
     */
    protected $_data = array();
    
    /**
     * 
     * @param string $idNameFrom
     * @param string $idNameTo
     * @param Zwei_Db_Table[] $modelsMap - ('FieldName' => new Zwei_Db_Table()) 
     * @return void
     */
    public function __construct($idNameFrom, $idNameTo, $modelsMap)
    {
        $this->_idNameFrom = $idNameFrom;
        $this->_idNameTo = $idNameTo;
        
        $this->_modelsMap = $modelsMap;
        
        foreach ($this->_modelsMap as $index => $model) {
            $this->_data[$index] = array();
        }
    }
    
    /**
     * @param array $data
     * @return array
     * @see Zwei_Db_Table::cleanDataParams()
     */
    public function cleanDataParams(array $data)
    {
        foreach ($this->_modelsMap as $index => $model) {
            if (isset($data[$index])) {
                $this->_data[$index] = $data[$index];
                unset($data[$index]);
            }
        }
        return $data;
    }
    
    /**
     * @param $data Zend_Db_Table_Row|array
     * @return array
     */
    public function overloadDataForm($data)
    {
        if ($data instanceof Zend_Db_Table_Row) $data = $data->toArray();
        /**
         * @var $model Zwei_Db_Table
        */
        foreach ($this->_modelsMap as $index => $model) {
            $ad = $model->getAdapter();
            $where = array(
                $ad->quoteInto("{$this->_idNameFrom} = ?", $data['id'])
            );
    
            $data[$index] = array();
            $plataformas     = $model->fetchAll($where);
            if ($plataformas->count() > 0) {
                foreach ($plataformas as $plat) {
                    $data[$index][] = $plat[$this->_idNameTo];
                }
            }
        }
        return $data;
    }
    
    /**
     *
     * @param string $idFrom
     * @return boolean
     */
    public function saveAll($idFrom)
    {
        $save = false;
        foreach ($this->_modelsMap as $index => $model) {
            if ($this->save($model, $idFrom, $this->_data[$index])) {
                $save = true;
            }
        }
        return $save;
    }
    
    
    /**
     *
     * @param Zwei_Db_Table $model
     * @param string $idFrom
     * @param array $data
     * @return boolean
     */
    public function save($model, $idFrom, $data)
    {
        $ad = $model->getAdapter();
    
        //(1) Inicialización clausula SQL WHERE para borrar 
        $where = array();
    
        //(2) Se deben borrar todas los item asociados a este elemento ...
        $where[] = $ad->quoteInto("{$this->_idNameFrom}= ?", $idFrom);
    
        $whereOr = array();
    
        $permissionsRows = array();
        foreach ($data as $idTo) {
            $whereOr[] = $ad->quote($idTo);
        }
    
        //(3) ... excepto los elementos que se encuentren chequeados en formulario
        if (!empty($data)) {
            $list = implode(",", $whereOr);
            $where[] = "($this->_idNameTo) NOT IN ($list)";
        }
    
        //(4) Clausula WHERE lista, ahora borremos.
        $delete = $model->delete($where);
    
        if (!empty($data)) $return = $delete;
    
        //(5) Agregar los elementos que fueron chequeadas.
        $insert =  false;
        foreach ($data as $idTo) {
            $data = array(
                    $this->_idNameFrom => $idFrom,
                    $this->_idNameTo => $idTo
            );
    
            try {
                $insert = $model->insert($data);
            } catch (Zend_Db_Exception $e) {
                if ($e->getCode() != 23000) {
                    Console::error($e->getMessage());
                }
            }
        }
        return $insert || $delete;
    }
    
    public function getData($index = null)
    {
        return $index ? $this->_data[$index] : $this->_data;
    }
}

