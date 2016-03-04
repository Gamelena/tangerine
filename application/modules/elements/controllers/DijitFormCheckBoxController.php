<?php

class Elements_DijitFormCheckBoxController extends Elements_BaseController
{
    public function indexAction()
    {
        $r = $this->getRequest();
        
        $this->view->defaultValue = $r->getParam('defaultValue', '1');
        $this->view->uncheckedValue = $r->getParam('uncheckedValue', '0');
        $this->view->checked = $r->getParam('value') == $this->view->defaultValue ? " checked=\"checked\"" : "";
        if ($r->getParam('checked') && $r->getParam("mode") === 'add') { $this->view->checked = " checked=\"checked\""; 
        }
    }
}

