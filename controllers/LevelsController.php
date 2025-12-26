<?php
namespace controllers;

use application\Controller;
use application\Flash;
use application\Helper;
use application\Session;
use application\Validate;
use application\Filter;
use models\Level;
use models\Course;

class LevelsController extends Controller
{
    private $_module;

    public function __construct()
    {
        $this->validateSession();
        parent::__construct();
        $this->_module = $this->getModule('Level');
    }

    public function index()
    {
        $this->validatePermission($this->_module, 'Listar');

        $this->_view->load('levels/index',[
            'title' => 'Niveles',
            'subject' => 'Lista de Niveles',
            'levels' => Level::select('id','name')->get(),
            'action' =>  'index',
            'route_create' => "levels/create",
            'button_create' => 'Nuevo Nivel'
        ]);
    }

    public function create()
    {
        $this->validatePermission($this->_module, 'Crear');

        $this->_view->load('levels/create', [
            'title' => 'Niveles',
            'subject' => 'Nuevo Nivel',
            'level' => Session::get('form_data') ?? [],
            'send'   => $this->encrypt($this->getForm()),
            'process' => "levels/store",
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
            'name' => 'required|min:3|max:100|string|unique:levels,name'
        ];

        $this->validateForm('levels/create', $data, $rules);

        $level = new Level();
        $level->name = Helper::getTitle($data['name']);
        $level->slug = Helper::friendlyRoute($data['name']);
        $level->save();

        Flash::success('Nivel creado correctamente.');
        $this->redirect('levels');
    }

    public function show($id = null)
    {
        $this->validatePermission($this->_module, 'Leer');
        $level = Validate::validateModel(Level::class, $id, 'levels');

        $this->_view->load('levels/show', [
            'title' => 'Niveles',
            'subject' => 'Detalle Nivel',
            'level'  => $level,
            'send'   => $this->encrypt($this->getForm()),
            'process' => "levels/delete",
        ]);
    }

    public function edit($id = null)
    {
        $this->validatePermission($this->_module, 'Editar');
        $level = Validate::validateModel(Level::class, $id, 'levels');

        $this->_view->load('levels/edit', [
            'title' => 'Niveles',
            'subject' => 'Editar Nivel',
            'level'   => $level,
            'send'   => $this->encrypt($this->getForm()),
            'process' => "levels/update/{$id}",
            'action' => 'edit',
        ]);
    }

    public function update($id = null)
    {
        $this->validatePermission($this->_module, 'Editar');
        $this->validatePUT();
        $level = Validate::validateModel(Level::class, $id, 'levels');

        $data = [
            'name' => Filter::getPost('name')
        ];

        $rules = [
            'name' => 'required|min:3|max:100|string'
        ];

        $this->validateForm("levels/edit/$id", $data, $rules);

        $level = $level;
        $level->name = Helper::getTitle($data['name']);
        $level->slug = Helper::friendlyRoute($data['name']);
        $level->save();

        Flash::success('Nivel actualizado correctamente.');
        $this->redirect('levels/show/' . $id);
    }

    public function delete()
    {
        $this->validatePermission($this->_module, 'Eliminar');
        $this->validateDelete();

        $data = [
            'level' => Filter::getPost('level'),
        ];

        $rules = [
            'level' => 'required|numeric',
        ];

        $this->validateForm("error/denied", $data, $rules);

        $level = Validate::validateModel(Level::class, $data['level'],'levels');
        $courses = Course::where('level_id', $level->id)->exists();

        if ($courses) {
            Flash::error('Hay cursos asociados. No se puede eliminar');
            $this->redirect('levels');
        }

        $level->delete();
        
        Flash::success('Nivel eliminado correctamente');
        $this->redirect('levels');
    }
}