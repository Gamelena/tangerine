<?php
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
     * @param Zwei_Utils_Form
     * @param Zwei_Db_Select
     */
	public function __construct($form, $select=false)
	{
        $this->_form = $form;
        $model = Zwei_Utils_String::toClassWord($form->model)."Model";
        $this->_model = new $model;
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
	    if (isset($this->_form->search) && (!empty($this->_form->search) || $this->_form->search === "0") && !$oModel->isFiltered()) {
            if (!empty($this->_form->search_fields) || @$this->_form->search_fields === "0") {
                $search_fields = @explode(";",$this->_form->search_fields);
                if (!is_array($search_fields))
                    $search_fields = array($this->_form->search_fields);
                if (@$this->_form->search_type == 'multiple') {
                    $aSearchKeys = explode(';',$this->_form->search);
                    $search_format = explode(';',@$this->_form->search_format);
                    $search_between = explode(';',@$this->_form->search_between);
                }
                $i = 0;
                foreach ($search_fields as $sSearchField) {
                    if ((!empty($sSearchField) || $sSearchField === "0") && (!empty($aSearchKeys[$i]) || @$aSearchKeys[$i] === "0" || empty($this->_form->search_type))) {
                        if (@$this->_form->search_type == 'multiple') {
                            if (preg_match("/^date_format(.*)/", @$search_format[$i], $match)) {
                                $mask='%Y-%m-%d';
                                if (@$this->_form->between == $sSearchField) {
                                    $oSelect->where($oModel->getAdapter()->quoteInto("DATE_FORMAT($sSearchField,'$mask') >= ?", $aSearchKeys[$i]));
                                    $i++;
                                    $oSelect->where($oModel->getAdapter()->quoteInto("DATE_FORMAT($sSearchField,'$mask') <= ?", $aSearchKeys[$i]));
                                } else if (!empty($aSearchKeys[$i]) || $aSearchKeys[$i] === "0") {
                                    $oSelect->where($oModel->getAdapter()->quoteInto("DATE_FORMAT($sSearchField,'$mask') = ?", $aSearchKeys[$i]));
                                }
                            } else if ($search_format[$i] == 'equals') {
                                $oSelect->where($oModel->getAdapter()->quoteInto("$sSearchField = ?", $aSearchKeys[$i]));
                            } else if ($search_format[$i] == 'lesserorequals') {
                                $oSelect->where($oModel->getAdapter()->quoteInto("$sSearchField <= ?", $aSearchKeys[$i]));
                            } else if ($search_format[$i] == 'greaterorequals') {
                                $oSelect->where($oModel->getAdapter()->quoteInto("$sSearchField >= ?", $aSearchKeys[$i]));
                            } else if (!empty($aSearchKeys[$i]) || $aSearchKeys[$i] === "0") {
                                $oSelect->where($oModel->getAdapter()->quoteInto("$sSearchField LIKE ?", "%{$aSearchKeys[$i]}%"));
                            }
                            $i++;
                        } else {
                            if (method_exists($oModel,"getName")) {
                                $sSearchField = $oModel->getName().".".$sSearchField;
                            }
                            if (preg_match("/^date_format(.*)/", @$this->_form->search_format, $match)) {
                                //$mask=($match[1]) ? $match[1] : '%Y-%m-%d';//[FIXME] esto debiera ser parametrizable pero hay que solucionar el url_encode de "%"
                                $mask = '%Y-%m-%d';
                                if (@$this->_form->between === '1') {
                                    $aSearchKeys=explode(";",$this->_form->search);
                                    $oSelect->where($oModel->getAdapter()->quoteInto("DATE_FORMAT($sSearchField,'$mask') >= ?", $aSearchKeys[0]));
                                    $oSelect->where($oModel->getAdapter()->quoteInto("DATE_FORMAT($sSearchField,'$mask') <= ?", $aSearchKeys[1]));
                                } else {
                                    $oSelect->where($oModel->getAdapter()->quoteInto("DATE_FORMAT($sSearchField,'$mask') = ?", $this->_form->search));
                                }
                            } else {
                                $oSelect->where($oModel->getAdapter()->quoteInto("$sSearchField LIKE ?", "%{$this->_form->search}%"));
                            }
                        }
                    }
                }//foreach
            } else {
                if ($oModel->getSearchFields()) {
                    $search_fields = $oModel->getSearchFields();
                    foreach ($search_fields as $sSearchField) {
                        if (@$this->_form->search_type == "multiple") {
                            foreach ($aSearchKeys as $f) {
                                $oSelect->where($oModel->getAdapter()->quoteInto("$sSearchField = ? ", $f));
                            }
                        } else {
                            //Zwei_Utils_Debug::write($sSearchField);
                            if ($sSearchField == 'id' || preg_match("/^id_/", $sSearchField) ||  preg_match("/_id$/", $sSearchField)) {
                                $oSelect->where($oModel->getAdapter()->quoteInto("$sSearchField = ? ", $this->_form->search));
                            } else {
                                $oSelect->orWhere($oModel->getAdapter()->quoteInto("$sSearchField LIKE ?", "%{$this->_form->search}%"));
                            }
                        }
                    }
                }
            }

        }//if (isset($this->_form->search) && (!empty($this->_form->search) || $this->_form->search === "0"))

        if (isset($this->_form->id)) {
            if (method_exists($oModel,"getPk")) {
                $my_id = $oModel->getPk();
            } else {
                $my_id = "id";
                if (method_exists($oModel, "getName")) $my_id = $oModel->getName().".".$my_id;
            }
        }

        //Se imprime query en log debug según configuración del sitio
        Zwei_Utils_Debug::writeBySettings($oSelect->__toString(), 'query_log');
        return $oSelect;
    }
}
