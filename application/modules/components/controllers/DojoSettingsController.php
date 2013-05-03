<?php

class Components_DojoSettingsController extends Zend_Controller_Action
{
    
    /**
     * Nombre del modelo Zend_Db_Table
     * @var Zwei_Db_Table
     */
    private $_model;
    
    /**
     * 
     * @var Zend_Config
     */
    private $_config;

    public function init()
    {
        $this->_helper->layout->disableLayout();
        
        $configParams = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getApplication()->getOptions();
        $this->_config = new Zend_Config($configParams);
        
        $file = Zwei_Admin_Xml::getFullPath($this->getRequest()->getParam('p'));
        $this->_xml = new Zwei_Admin_Xml($file, 0, 1);
        $this->view->mainPane = isset($this->_config->zwei->layout->mainPane) ? $this->_config->zwei->layout->mainPane : 'undefined';
        $this->view->domPrefix  = (isset($this->view->mainPane) && $this->view->mainPane == 'dijitTabs') ? Zwei_Utils_String::toVarWord($this->getRequest()->getParam('p')) : '';
    }

    public function indexAction()
    {
        $r = $this->getRequest();
        $model = $this->_xml->getAttribute('target', 'SettingsModel');
        $this->_model = new $model();
        $this->view->model = $this->_model;
        
        //$out .= "<div dojoType=\"dijit.layout.TabContainer\" style=\"width: 100%; height: 100%;\" tabStrip=\"true\">\r\n";

        $this->view->groupies = $this->_model->loadGroups();
        $this->view->groups = array();
        $h = 'style="background:#cccccc"';
        $i = 0;
        foreach ($groupies as $group) {
            $g = !empty($group['group']) ? $group['group'] : 'General';
            $groups[] = $group['group'];
            $selected = ($i == 0) ? ' selected="true"' : '';
            /*
            $out .= "\t<div dojoType=\"dijit.layout.ContentPane\" title=\"{$group['group']}\" $selected>\r\n";
            $out .= "\t\t<div dojoType=\"dijit.form.Form\" id=\"settings{$i}_form\" jsId=\"settings{$i}_form\" encType=\"multipart/form-data\" target=\"ifrm_process\" action=\"".BASE_URL."objects/multi-update\" method=\"post\">\r\n";
            */
            //Subgrupos
            $this->_model = new $this->_model();
            $table=$this->_model->getName();

            $query = $this->_model->select()
                ->where($this->_model->getAdapter()->quoteInto($table.'.group = ?', $group['group']))
                ->order('ord ASC');

            $subgroups = $this->_model->fetchAll($query);

            foreach ($subgroups as $sg) {
                $out .= "\t\t\t<input type=\"hidden\" id=\"id[$i]\" name=\"id[$i]\" value=\"$sg[id]\" />\r\n";
                $out .= "\t\t\t<b>$sg[id]:</b> ";
                $ClassElement = "Zwei_Admin_Elements_".Zwei_Utils_String::toClassWord($sg['type']);
                $e = new $ClassElement(true, true, "", "value", $sg['value']);

                if (!empty($sg['enum'])) {
                    $out .= $this->_select("value[$i]", $sg['enum'], $sg['value']);
                }else{
                    $out .= $e->edit($i,0);
                }

                if (!empty($sg['function'])&&class_exists($sg['function'])) {
                    $f = new $sg['function'];
                    $f->setId($i);
                    $out .= $f->display();
                }
                $out .= "<br />".nl2br($sg['description']);
                $out .= "\r\n<br /><br />\r\n";
                $i++;
            }
            /*
            $out .="
        	<script type=\"dojo/method\" event=\"onSubmit\">
                if (this.validate())
                {
                    this.submit();
                }
                else
                {
                	alert('Por favor favor corrija los campos marcados');
                	return false;
                }

            </script>\r\n
            ";			

            $out.="<button type=\"submit\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconSave\" id=\"btnSaveSettings$i\">Guardar</button>";
            $out.="<input type=\"hidden\" name=\"save\" value=\"save\" />\r\n";
            $out.="<input type=\"hidden\" name=\"model\" value=\"$this->_model\" />\r\n";
            $out.="\t\t</div>\r\n";
            $out.="\t</div>\r\n";
            */
        }

        //$out.="</div>\r\n";
    }


}

