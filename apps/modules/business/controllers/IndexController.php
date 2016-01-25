<?php

namespace Promoziti\Modules\Business\Controllers;

use \Promoziti\Lib\Core\DiHandler as DiHandler;

class IndexController extends ControllerBase
{

	public function indexAction()
	{
		if($this->validateSession())
		{
			$this->dispatcher->forward(array(
				"controller" => "dashboard",
				"action" => "index"
			)); 
		}
		else //not logged in
		{
			//loading language vars from config
			$this->view->lang_g = $this->getLangConfig()->business->global;
			$this->view->lang_p = $this->getLangConfig()->business->pages->login;
			
			$this->view->year = date('Y');
			$this->view->js_files = array('assets/common/js/ie_check.js',
											'assets/business/js/pages/login.js');
			$this->view->remembered_email = DiHandler::getCookies()->has('remember_me') ?
											DiHandler::getCookies()->get('remember_me'):'';
		}        
	}

	//alex todo
	public function forgotAction()
	{
		if($this->validateSession())
		{
			$this->dispatcher->forward(array(
				"controller" => "dashboard",
				"action" => "index"
			)); 
		}
		else //not logged in
		{
			$this->view->now_w_format = date('d/m/y h:ia');
			$this->view->year = date('Y');
			$this->view->js_files = array('assets/common/js/ie_check.js',
											'assets/business/js/pages/login.js');
			$this->view->remembered_email = DiHandler::getCookies()->has('remember_me') ?
											DiHandler::getCookies()->get('remember_me'):'';
		}        
	}
}