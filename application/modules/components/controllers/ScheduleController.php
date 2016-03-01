<?php
/**
 * Clase candidata para franjas horarias genericas en Admportal
 * @author rodrigo
 */
class Components_ScheduleController extends Zend_Controller_Action
{
    /**
     *
     * @var Zwei_Db_Table
     */
    private $_model;

    /**
     * Nombre de campo foreign key.
     * 
     * @var string 
     */
    protected $_fkName;

    public function init()
    {
        $model = $this->getRequest()->getParam('model');
        $this->_model = new $model();
        $this->_fkName = $this->getRequest()->getParam('fkName', $this->_model->getFkName());
    }

    public function indexAction()
    {
        $r = $this->getRequest();
        $select = $this->_model->select()
            ->where($this->_model->getAdapter()->quoteInto("$this->_fkName = ?", $r->getParam($this->_fkName)));

        Debug::writeBySettings($select->__toString(), 'query_log');
        

        $feriadoModel = new FeriadoModel();

        $rowset = $feriadoModel->fetchAll();
        $feriados = array();
        foreach ($rowset as $row) {
            $feriados[] = $row->id;
        }

        $rows = $this->_model->fetchAll($select);
        $this->view->franja = $franja = array();

        foreach ($rows as $i => $f) {
            $franja['id'] = $f->id;
            $datesPeriod = new DatePeriod(
                new DateTime($f['fecha_inicio'] . " " . $f['hora_inicio']),
                new DateInterval('P1D'),
                new DateTime($f['fecha_termino']. " " . $f['hora_termino'])
            );

            /**
             * @var $dateTime DateTime 
            */
            foreach ($datesPeriod as $dateTime) {

                $date      = $dateTime->format('Y-m-d');
                $dayOfWeek = $dateTime->format('w') == 0 ? 6 : $dateTime->format('w') - 1;

                $dateTime1 = $dateTime->format('Y-m-d-H-i-s');//Esto no es un typo, usamos '-' en lugar de ':' para hacer explode(...).
                $dateTime2 = $dateTime->format('Y-m-d') . "-" . str_replace(':', '-', $f['hora_termino']);


                //0:No mostrar feriados || 1:mostrar solo feriados || 2:no importa
                $show = (($f->manejo_feriados == 0 && !in_array($date, $feriados)) ||
                (($f->manejo_feriados == 1) && (in_array($date, $feriados))) ||
                ($f->manejo_feriados == 2)) && $f->dias & pow(2, $dayOfWeek);

                if ($show) {
                    list($franja['Y1'], $franja['m1'], $franja['d1'], $franja['H1'], $franja['i1'], $franja['s1']) = explode('-', $dateTime1);
                    list($franja['Y2'], $franja['m2'], $franja['d2'], $franja['H2'], $franja['i2'], $franja['s2']) = explode('-', $dateTime2);

                    $franja['m1']--;// los meses para javascript Date() son desde 0 hasta 11
                    $franja['m2']--;
                    $this->view->franja[] = (object) $franja;
                }
            }
        }
    }
}
