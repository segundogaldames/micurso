<?php
namespace controllers;

use application\Controller;

class ErrorController extends Controller
{
	public function __construct(){
		parent::__construct();
	}

	public function index() : void {
		$this->redirect('error/error');
	}

	public function error()
	{
		$route_back = "javascript:history.back()";

		$this->_view->load('error/error', compact('route_back'));
	}

	public function denied()
	{
		$route_back = "javascript:history.back()";
		
		$this->_view->load('error/denied', compact('route_back'));
	}
}