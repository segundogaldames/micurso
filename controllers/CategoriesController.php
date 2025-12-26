<?php
namespace controllers;

use application\Controller;
use application\Flash;
use application\Helper;
use application\Session;
use application\Validate;
use application\Filter;
use models\Category;
use models\Course;

class CategoriesController extends Controller
{
    private $_module;

    public function __construct()
    {
        $this->validateSession();
        parent::__construct();
        $this->_module = $this->getModule('Category');
    }

    public function index()
    {
        $this->validatePermission($this->_module, 'Listar');

        $this->_view->load('categories/index',[
            'title' => 'Categorias',
            'subject' => 'Lista de Categorías',
            'categories' => Category::select('id','name')->get(),
            'action' =>  'index',
            'route_create' => "categories/create",
            'button_create' => 'Nueva Categoria'
        ]);
    }

    public function create()
    {
        $this->validatePermission($this->_module, 'Crear');

        $this->_view->load('categories/create', [
            'title' => 'Categorias',
            'subject' => 'Nueva Categoría',
            'category' => Session::get('form_data') ?? [],
            'send'   => $this->encrypt($this->getForm()),
            'process' => "categories/store",
            'action' => 'create',
        ]);
    }

    public function store()
    {
        $this->validatePermission($this->_module, 'Crear');
        
        $data = [
            'name' => Filter::getPost('name')
        ];

        $rules = [
            'name' => 'required|min:3|max:100|string|unique:categories,name'
        ];

        $this->validateForm('categories/create', $data, $rules);

        $category = new Category();
        $category->name = Helper::getTitle($data['name']);
        $category->slug = Helper::friendlyRoute($data['name']);
        $category->save();

        Flash::success('Categoría creada correctamente.');
        $this->redirect('categories');
    }

    public function show($id = null)
    {
        $this->validatePermission($this->_module, 'Leer');
        $category = Validate::validateModel(Category::class, $id, 'roles');

        $this->_view->load('categories/show', [
            'title' => 'Categorias',
            'subject' => 'Detalle Categoría',
            'category'  => $category,
            'send'   => $this->encrypt($this->getForm()),
            'process' => "categories/delete",
        ]);
    }

    public function edit($id = null)
    {
        $this->validatePermission($this->_module, 'Editar');
        $category = Validate::validateModel(Category::class, $id, 'roles');

        $this->_view->load('categories/edit', [
            'title' => 'Categorias',
            'subject' => 'Editar Categoría',
            'category'   => $category,
            'send'   => $this->encrypt($this->getForm()),
            'process' => "categories/update/{$id}",
            'action' => 'edit',
        ]);
    }

    public function update($id = null)
    {
        $this->validatePermission($this->_module, 'Editar');
        $this->validatePUT();
        $category = Validate::validateModel(Category::class, $id, 'roles');

        $data = [
            'name' => Filter::getPost('name')
        ];

        $rules = [
            'name' => 'required|min:3|max:100|string'
        ];

        $this->validateForm("categories/edit/$id", $data, $rules);

        $category = $category;
        $category->name = Helper::getTitle($data['name']);
        $category->slug = Helper::friendlyRoute($data['name']);
        $category->save();

        Flash::success('Categoría actualizada correctamente.');
        $this->redirect('categories/show/' . $id);
    }

    public function delete()
    {
        $this->validatePermission($this->_module, 'Eliminar');
        $this->validateDelete();

        $data = [
            'category' => Filter::getPost('category'),
        ];

        $rules = [
            'category' => 'required|numeric',
        ];

        $this->validateForm("error/denied", $data, $rules);

        $category = Validate::validateModel(Category::class, $data['category'],'categories');
        $courses = Course::where('category_id', $category->id)->exists();

        if ($courses) {
            Flash::error('Hay cursos asociados. No se puede eliminar');
            $this->redirect('categories');
        }

        $category->delete();
        
        Flash::success('Categoría eliminada correctamente');
        $this->redirect('categories');
    }
}