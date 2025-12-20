<?php

namespace controllers;

use application\Controller;

class AdminController extends Controller
{
    public function __construct()
    {
        return parent::__construct();
    }

    public function index()
    {
        $this->_view->load('admin/index');
    }
}