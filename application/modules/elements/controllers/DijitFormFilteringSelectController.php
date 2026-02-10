<?php

class Elements_DijitFormFilteringSelectController extends Elements_BaseController
{

    public function indexAction()
    {
        $r = $this->getRequest();


        $this->view->formatter = $r->getParam('formatter', false);
        $this->view->data = $r->getParam('data', false);
        $this->view->mode = $r->getParam('mode');
        $this->view->trim = $r->getParam('trim') ? "trim=\"{$r->getParam('trim')}\"" : '';


        $this->view->regExp = $r->getParam('regExp') ? " regExp=\"{$r->getParam('regExp')}\"" : '';
        $this->view->label = $r->getParam('label') ? " label=\"{$r->getParam('label')}\"" : '';

        $formatter = (!$this->view->data && $this->view->formatter && $this->view->formatter != 'formatimage') ?
            "console.log('onchange');dijit.byId('{$this->view->domId}{$this->view->i}').textbox.value={$this->view->formatter}(dijit.byId('{$this->view->domId}{$this->view->i}').get('value'));" : '';

        $this->view->onchange = $r->getParam('onchange') ? $formatter . $r->getParam('onchange') : $formatter;

        $this->view->onclick = $r->getParam('onclick') ? " onclick=\"{$r->getParam('onclick')}\"" : '';
        $this->view->onkeypress = $r->getParam('onkeypress') ? " onkeypress=\"{$r->getParam('onkeypress')}\"" : '';
        $this->view->onshow = $r->getParam('onshow') ? " onShow=\"{$r->getParam('onshow')}\"" : '';
        $this->view->onload = $r->getParam('onload', '');

        if (preg_match("/^\{BASE_URL\}(.*)$/", (string) $r->getParam('label'), $matches)) {
            $this->view->label = "url=\"" . BASE_URL . $matches[1] . "\"";
        }

        $this->view->invalidMessage = $r->getParam('invalidMessage') ? " invalidMessage=\"{$r->getParam('invalidMessage')}\"" : '';
        $this->view->missingMessage = $r->getParam('missingMessage') ? "missingMessage=\"{$r->getParam('missingMessage')}\"" : '';
        $this->view->promptMessage = $r->getParam('promptMessage') ? " promptMessage=\"{$r->getParam('promptMessage')}\"" : '';
        $this->view->placeHolder = $r->getParam('placeHolder') ? " placeHolder=\"{$r->getParam('placeHolder')}\"" : '';

        if ($r->getParam('data') && in_array($r->getParam('mode'), array('add', null)) && $r->getParam('defaultValue') !== false) {
            $this->view->value = $r->getParam('defaultValue');
        } else if ($r->getParam('data') && $r->getParam('value') !== false) {
            $this->view->value = $r->getParam('value');
        } else {
            $this->view->value = $r->getParam('defaultValue') && !$r->getParam('defaultText') ? $r->getParam('defaultValue') : '';
        }

        $this->view->options = !$this->view->data ? $this->options() : false;
    }
}

