<?php

class EventsController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        if (!Zwei_Admin_Auth::getInstance()->hasIdentity()) {
                echo "<script>window.parent.location.href='".BASE_URL."/admin'</script>";
        }
    }
}

