<?php

class Elements_DijitFormFilteringSelectController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        
        
    }

    public function indexAction()
    {
        $r = $this->getRequest();
        $this->view->i =  $r->getParam('i');
        $this->view->domId =  $r->getParam('domId');
        $this->view->target =  $r->getParam('target');
        
        $this->view->readonly = $r->getParam('readonly', '') === 'true' ? "readonly=\"readonly\"" : '';
        $this->view->disabled = $r->getParam('disabled', '') === 'true' ? "disabled=\"disabled\"" : '';
        $this->view->required = $r->getParam('required', '') === 'true' ? "required=\"true\"" : '';
        $this->view->onblur = $r->getParam('onblur') ? "onblur=\"{$r->getParam('onblur')}\"" : '';
        $this->view->regExp = $r->getParam('regExp') ? "regExp=\"{$r->getParam('regExp')}\"" : '';
        
        if (preg_match("/^\{BASE_URL\}(.*)$/", $r->getParam('label'), $matches)) {
            //$this->view->label = "url=\"".BASE_URL . $matches[1]."\"";
        }
        
        $this->view->invalidMessage = $r->getParam('invalidMessage') ? "invalidMessage=\"{$r->getParam('invalidMessage')}\"" : '';
        $this->view->promptMessage= $r->getParam('promptMessage') ? "promptMessage=\"{$r->getParam('promptMessage')}\"" : '';
        
        $this->view->value =  $r->getParam('defaultValue') && !$r->getParam('defaultText') ? "value=\"{$r->getParam('defaultValue')}\"" : '';
        $this->view->options = $this->options();
    }

    function options()
    {
        $r = $this->getRequest();
        $options = "";
    
        $selected = array();
    
        if (!$r->getParam('value')) {
            $value = $r->getParam($r->getParam('target', ''), null);
        } else {
            $value = $r->getParam('value');
        }
        
        if ($r->getParam('table')) {
            $id = $r->getParam('tablePk', 'id');
            $className = $r->getParam('table');
            $model = new $className();
    
            if ($r->getParam('tableMethod')) {
                $methodName = $r->getParam('tableMethod');
                $select = $model->$methodName();
            } else {
                if ($r->getParam('tableField')) {
                    $select = $model->select(array($r->getParam('tableField'), $id));
                } else if ($r->getParam('field')){
                    $select = $model->select(array($r->getParam('field'), $id));
                } else {
                    $select = $model->select(array("title", $id));
                }
            }
            
            Zwei_Utils_Debug::writeBySettings($select->__toString(), 'query_log');
            $rows = $model->fetchAll($select);

            if ($r->getParam('defaultValue') !== false || $r->getParam('defaultText') !== false) {
                $options .= "<option value=\"{$r->getParam('defaultValue', '')}\">{$r->getParam('defaultText', '')}</option>\r\n";
            }
    
    
            foreach ($rows as $row) {
                $selected = $row[$id] == $value ? "selected" : "";
                if ($r->getParam('tableField')) {
                    $options .= "<option value=\"".$row[$id]."\" ".$selected." >{$row[$r->getParam('tableField')]}</option>\r\n";
                } else if ($r->getParam('field')) {
                    $options .= "<option value=\"".$row[$id]."\" ".$selected." >{$row[$r->getParam('field')]}</option>\r\n";
                } else {
                    $options .= "<option value=\"".$row[$id]."\" ".$selected." label=\"<i>we</i> <img src='http://placekitten.com/50/70' />\">{$row["title"]}</option>\r\n";
                }
            }
        } else {
            $options = "";
            $rows = explode(";", $r->getParam('list'));
            foreach ($rows as $row) {
                $selected = $row == $value ? "selected" : "";
                $options .= "<option value=\"".$row."\" ".$selected." >$row</option>\r\n";
            }
    
        }
        return $options;
    }
}

