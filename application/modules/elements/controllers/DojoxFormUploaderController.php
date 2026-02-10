<?php

class Elements_DojoxFormUploaderController extends Elements_BaseController
{

    public function indexAction()
    {
        $r = $this->getRequest();

        $this->view->formatter = $r->getParam('formatter', '');
        $this->view->thumbs = $r->getParam('thumbs', array());

        $this->view->baseUrlPath = $r->getParam('url') ? $r->getParam('url') : BASE_URL . '/upfiles/';

        //La RegExp busca constantes declaradas entre llaves en atributo xml "path"
        //ej {BASE_URL}/myupfiles
        if (preg_match("/^\{(.*)\}(.*)$/", (string) $r->getParam('url'), $matches)) {
            $this->view->baseUrlPath = constant($matches[1]) . $matches[2];
        }

        $this->view->path = $r->getParam('path');

        if (preg_match("/^\{(.*)\}(.*)$/", (string) $this->view->path, $matches)) {
            $this->view->path = constant($matches[1]) . $matches[2];
        }

        $this->view->invalidMessage = $r->getParam('invalidMessage') ? "invalidMessage=\"{$r->getParam('invalidMessage')}\"" : '';
        $this->view->promptMessage = $r->getParam('promptMessage') ? "promptMessage=\"{$r->getParam('promptMessage')}\"" : '';
    }
}

