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
        
        $this->view->formatter = $r->getParam('formatter', false);
        $this->view->data = $r->getParam('data', false);
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
        $this->view->promptMessage= $r->getParam('promptMessage') ? "promptMessage=\"{$r->getParam('promptMessage')}\"" : '';
        
        if ($r->getParam('data') && $r->getParam('value') !== false) {
            $this->view->value = "value=\"{$r->getParam('value')}\" ";
        } else {
            $this->view->value =  $r->getParam('defaultValue') && !$r->getParam('defaultText') ? "value=\"{$r->getParam('defaultValue')}\" " : '';
        }
        
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
            $rows = $model->fetchAll($select);

            if ($r->getParam('defaultValue') || $r->getParam('defaultValue') === '' || $r->getParam('defaultText') || $r->getParam('defaultText') === '') {
                $options .= "<option value=\"{$r->getParam('defaultValue', '')}\" label=\"{$r->getParam('defaultText', '')}\">{$r->getParam('defaultText', '')}</option>\r\n";
            }
    
    
            foreach ($rows as $row) {
                $selected = $row[$id] == $value ? "selected" : "";
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

