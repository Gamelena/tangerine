<?php

class Zwei_Legacy_Acl extends Zwei_Admin_Acl
{
	static private $acl;
	static private $ready = 0;

	static private function init(){
		$userInfo = Zend_Auth::getInstance()->getStorage()->read();
		Zwei_Legacy_Acl::$acl = new Zwei_Admin_Acl($userInfo->USER_LOGIN);
		$ready = 1;
	}
	static public function isAllowedList(){
		if( Zwei_Legacy_Acl::$ready == 0 ){
			Zwei_Legacy_Acl::init();
		}
		return Zwei_Legacy_Acl::$acl->isUserAllowed($_REQUEST['p'], "LIST");
	}
	static public function isAllowedAdd(){
		if( Zwei_Legacy_Acl::$ready == 0 ){
			Zwei_Legacy_Acl::init();
		}
		return Zwei_Legacy_Acl::$acl->isUserAllowed($_REQUEST['p'], "ADD");
	}
	static public function isAllowedDelete(){
		if( Zwei_Legacy_Acl::$ready == 0 ){
			Zwei_Legacy_Acl::init();
		}
		return Zwei_Legacy_Acl::$acl->isUserAllowed($_REQUEST['p'], "DELETE");
	}
	static public function isAllowedEdit(){
		if( Zwei_Legacy_Acl::$ready == 0 ){
			Zwei_Legacy_Acl::init();
		}
		return Zwei_Legacy_Acl::$acl->isUserAllowed($_REQUEST['p'], "EDIT");
	}
}

