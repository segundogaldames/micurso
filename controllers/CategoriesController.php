<?php
namespace controllers;

use application\Controller;
use application\Flash;
use application\Helper;
use application\Session;
use models\Category;

class CategoriesController extends Controller
{
    private $_modulo;

    public function __construct(){
        parent::__construct();
    }

    public function index()
    {
        $this->_view->load('categories/index',[
            'titulo' => 'Categorias Disponibles',
            'categories' => Category::all()
        ]);
    }
}