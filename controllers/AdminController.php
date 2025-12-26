<?php

namespace controllers;

use application\Controller;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->validateSession();
        $this->validateRole(['Administrador','Instructor']);
        return parent::__construct();
    }

    public function index()
    {
        $this->_view->load('admin/index');
    }
}