<?php
/**
 * 
 * Modelo auxiliar para modelos que trabajen con datos de n:n 
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
     * @param string $idName nombre de la primary
     * @param array $modelsMap array de modelos
     */
    public function __construct($idNameFrom, $idNameTo, array $modelsMap)
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
    public function cleanDataParams($data)
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
     * @param $data Zend_Db_Table_Rowset
     * @return array
     */
    public function overloadDataForm($data)
    {
        $data = $data->toArray();
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
     * @param int $idServicio
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
     * @param int $idServicio
     * @param array $data
     * @return boolean
     */
    public function save($model, $idFrom, $data)
    {
        $ad = $model->getAdapter();
    
        //(1) Inicialización clausula SQL WHERE para borrar permisos
        $where = array();
    
        //(2) Se deben borrar todas las plataformas asociados a este perfil ...
        $where[] = $ad->quoteInto("{$this->_idNameFrom}= ?", $idFrom);
    
        $whereOr = array();
    
        $permissionsRows = array();
        foreach ($data as $idTo) {
            $whereOr[] = $ad->quote($idTo);
        }
    
        //(3) ... excepto las plataformas que se encuentren chequeadas en formulario
        if (!empty($data)) {
            $list = implode(",", $whereOr);
            $where[] = "($this->_idNameTo) NOT IN ($list)";
        }
    
        //(4) Clausula WHERE lista, ahora borremos.
        $delete = $model->delete($where);
    
        if (!empty($data)) $return = $delete;
    
        //(5) Agregar las plataformas que fueron chequeadas.
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
                    $this->setMessage($e->getMessage());
                    return false;
                }
            }
        }
        return $insert || $delete;
    }
}

