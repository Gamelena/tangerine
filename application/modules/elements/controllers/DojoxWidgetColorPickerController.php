<?php

class Elements_DojoxWidgetColorPickerController extends Elements_BaseController
{

    public function indexAction()
    {
        $r = $this->getRequest();
        if (empty($this->view->value)) {
            $this->view->value = 'transparent';
        }
    }


}

