<?php

namespace controllers;

use application\Controller;
use application\Flash;
use application\Helper;
use application\Session;
use models\Course;

class indexController extends Controller
{
	private $_modulo;

	public function __construct(){
		parent::__construct();
	}

	public function index()
	{

		$this->_view->load('index/index',[
			'titulo' => 'Bienvenidos a E-Learning',
			'courses' => Course::with(['category','level'])->where('status_id', 1)->latest('id')->get(),
			'process' => 'courses/getCourses',
			'send' => $this->encrypt($this->getForm()),
			'module' => $this->encrypt('index')
		]);
	}
}