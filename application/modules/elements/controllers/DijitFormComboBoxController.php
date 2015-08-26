<?php

class Elements_DijitFormComboBoxController extends Zend_Controller_Action
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
        
        $this->view->formatter = $r->getParam('formatter', false);
        $this->view->data = $r->getParam('data', false);
        $this->view->mode = $r->getParam('mode');
        $this->view->trim = $r->getParam('trim') ? "trim=\"{$r->getParam('trim')}\"" : '';
        $this->view->readonly = $r->getParam('readonly') === 'true' || $r->getParam($r->getParam('mode')) == 'readonly' ? "readonly=\"readonly\"" : '';
        $this->view->disabled = $r->getParam('disabled') === 'true' || $r->getParam($r->getParam('mode')) == 'disabled' ? "disabled=\"disabled\"" : '';
        $this->view->required = $r->getParam('required', '') === 'true' ? "required=\"true\"" : '';
        $this->view->onblur = $r->getParam('onblur') ? "onblur=\"{$r->getParam('onblur')}\"" : '';
        $this->view->regExp = $r->getParam('regExp') ? "regExp=\"{$r->getParam('regExp')}\"" : '';
        $this->view->label = $r->getParam('label') ? "label=\"{$r->getParam('label')}\"" : '';
        
        $formatter = (!$this->view->data && $this->view->formatter && $this->view->formatter != 'formatimage') ?
            "console.log('onchange');dijit.byId('{$this->view->domId}{$this->view->i}').textbox.value={$this->view->formatter}(dijit.byId('{$this->view->domId}{$this->view->i}').get('value'));" : '';
        
        $this->view->onchange = $r->getParam('onchange') ? $formatter.$r->getParam('onchange') : $formatter;
        
        $this->view->onclick = $r->getParam('onclick') ? "onclick=\"{$r->getParam('onclick')}\"" : '';
        $this->view->onshow = $r->getParam('onshow') ? "onShow=\"{$r->getParam('onshow')}\"" : '';
        $this->view->onload = $r->getParam('onload', '');
        
        if (preg_match("/^\{BASE_URL\}(.*)$/", $r->getParam('label'), $matches)) {
            $this->view->label = "url=\"".BASE_URL . $matches[1]."\"";
        }
        
        $this->view->invalidMessage = $r->getParam('invalidMessage') ? "invalidMessage=\"{$r->getParam('invalidMessage')}\"" : '';
        $this->view->missingMessage = $r->getParam('missingMessage') ? "missingMessage=\"{$r->getParam('missingMessage')}\"" : '';
        $this->view->promptMessage= $r->getParam('promptMessage') ? "promptMessage=\"{$r->getParam('promptMessage')}\"" : '';
        $this->view->placeHolder = $r->getParam('placeHolder') ? "placeHolder=\"{$r->getParam('placeHolder')}\"" : '';
        
        if ($r->getParam('data') && $r->getParam('mode') == 'add' && $r->getParam('defaultValue') !== false) {
            $this->view->value =  $r->getParam('defaultValue');
        } else if ($r->getParam('value') !== false) {
            $this->view->value = $r->getParam('value');
        } else {
            $this->view->value =  $r->getParam('defaultValue') && !$r->getParam('defaultText') ? $r->getParam('defaultValue') : '';
        }
        
        $this->view->value=str_replace("'", "\'", $this->view->value);
        
        $this->view->options = !$this->view->data ? $this->options() : false;
    }

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
            
            
            if (method_exists($select, '__toString')) Zwei_Utils_Debug::writeBySettings($select->__toString(), 'query_log');
            
            try {
                $rows = $model->fetchAll($select);
            } catch (Zend_Db_Exception $e) {
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

