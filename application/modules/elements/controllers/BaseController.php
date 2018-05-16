<?php
/**
 * Controlador base para la creación de elements.
 */
abstract class Elements_BaseController extends Zend_Controller_Action
{
    public function init()
    {
        $r = $this->getRequest();
        $this->view->i =  $r->getParam('i');
        $this->view->domId =  $r->getParam('domId');
        $this->view->target =  $r->getParam('target');
        $this->view->value =  $r->getParam('value', $r->getParam('defaultValue', ''));
        $this->view->readonly = $r->getParam('readonly') === 'true' || $r->getParam($r->getParam('mode')) == 'readonly' ? " readonly=\"readonly\"" : '';
        $this->view->disabled = $r->getParam('disabled') === 'true' || $r->getParam($r->getParam('mode')) == 'disabled' ? " disabled=\"disabled\"" : '';
        $this->view->onblur = $r->getParam('onblur') ? " onblur=\"{$r->getParam('onblur')}\"" : '';
        $this->view->required = $r->getParam('required', '') === 'true' ? "required=\"true\"" : '';
        $this->view->onchange = $r->getParam('onchange', '');
        $this->view->onclick = $r->getParam('onclick') ? "onclick=\"{$r->getParam('onclick')}\"" : '';
    }
    
    /**
     * Generación de string HTML de options para select
     * @throws Zend_Db_Exception
     * @return string
     */
    function options()
    {
        $r = $this->getRequest();
        $options = "";
    
        $selected = array();
    
        if ($r->getParam('value') === false) {
            $value = $r->getParam($r->getParam('target', ''), null);
        } else {
            $value = $r->getParam('value');
        }
    
        if ($r->getParam('table')) {
            $id = $r->getParam('tablePk', 'id');
            $className = $r->getParam('table');
    
            /**
             * @var $model Gamelena_Db_Table
            */
            $model = new $className();
    
            if ($r->getParam('tableMethod')) {
                $methodName = $r->getParam('tableMethod');
                $select = $model->$methodName();
            } else {
                if ($r->getParam('tableField')) {
                    $select = $model->select(array($r->getParam('tableField'), $id));
                } else if ($r->getParam('field')) {
                    $select = $model->select(array($r->getParam('field'), $id));
                } else {
                    $select = $model->select(array("title", $id));
                }
            }
    
    
            if (method_exists($select, '__toString')) { Gamelena_Utils_Debug::writeBySettings($select->__toString(), 'query_log'); 
            }
    
            try {
                $rows = $model->fetchAll($select);
                if ($r->getParam('overloadDataList')) {
                    $rows = $model->overloadDataList($rows);
                }
            } catch (Zend_Db_Exception $e) {
                if (!isset($methodName)) { $methodName = 'select'; 
                }
                throw new Zend_Db_Exception("$className::$methodName() {$e->getMessage()}", $e->getCode());
            }
    
            if ($r->getParam('defaultValue') || $r->getParam('defaultValue') === '' || $r->getParam('defaultText') || $r->getParam('defaultText') === '') {
                $options .= "<option value=\"{$r->getParam('defaultValue', '')}\" label=\"{$r->getParam('defaultText', '')}\">{$r->getParam('defaultText', '')}</option>\r\n";
            }
    
            foreach ($rows as $row) {
                $selected = $row[$id] == $value ? "selected=\"selected\"" : "";
                if ($r->getParam('tableField')) {
                    $options .= "<option value=\"".$row[$id]."\" ".$selected." label=\"{$row[$r->getParam('tableField')]}\">{$row[$r->getParam('tableField')]}</option>\r\n";
                } else if ($r->getParam('field')) {
                    $options .= "<option value=\"".$row[$id]."\" ".$selected." label=\"{$row[$r->getParam('field')]}\">{$row[$r->getParam('field')]}</option>\r\n";
                } else {
                    $options .= "<option value=\"".$row[$id]."\" ".$selected." label=\"{$row["title"]}\">{$row["title"]}</option>\r\n";
                }
            }
        } else {
            $options = "";
            $rows = explode(";", $r->getParam('list'));
    
            if ($r->getParam('defaultValue') || $r->getParam('defaultValue') === '' || $r->getParam('defaultText') || $r->getParam('defaultText') === '') {
                $options .= "<option value=\"{$r->getParam('defaultValue', '')}\" label=\"{$r->getParam('defaultText', '')}\">{$r->getParam('defaultText', '')}</option>\r\n";
            }
    
            foreach ($rows as $row) {
                $selected = $row == $value ? "selected" : "";
                $options .= "<option value=\"".$row."\" ".$selected." >$row</option>\r\n";
            }
    
        }
        return $options;
    }
}

