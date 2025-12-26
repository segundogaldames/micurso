<?php

namespace controllers;

use application\Controller;
use application\Filter;
use application\Flash;
use application\Helper;
use application\Session;
use application\Validate;
use models\Module;

class ModulesController extends Controller
{
    public function __construct()
    {
        $this->validateSession();
        $this->validateRole(['Administrador']);
        parent::__construct(); 
    }

    public function index()
    {
        $this->_view->load('modules/index', [
            'title' => 'Modulos',
            'subject' => 'Lista de Módulos',
            'modules'  => Module::select('id','name')->get(),
            'action' =>  'index',
            'route_create' => "modules/create",
            'button_create' => 'Nuevo Módulo'
        ]);
    }

    public function create()
    {
        $this->_view->load('modules/create', [
            'title' => 'Modulos',
            'subject' => 'Nuevo Módulo',
            'module' => Session::get('form_data') ?? [],
            'send'   => $this->encrypt($this->getForm()),
            'process' => "modules/store",
            'action' => 'create',
        ]);
    }

    public function store()
    {
        $data = [
            'name' => Filter::getPost('name')
        ];
        
        $rules = [
            'name' => 'required|min:3|max:100|string|unique:modules,name'
        ];
        //Helper::debuger($data['name']);
        
        $this->validateForm('modules/create', $data, $rules);

        $module = new Module();
        $module->name = $data['name'];
        $module->save();

        Flash::success('Módulo creado correctamente.');
        $this->redirect('modules');
    }

    public function show($id = null)
    {
        $module = Validate::validateModel(Module::class, $id, 'modules');

        $this->_view->load('modules/show', [
            'title' => 'Modulos',
            'subject' => 'Detalle Módulo',
            'module'  => $module
        ]);
    }

    public function edit($id = null)
    {
        $module = Validate::validateModel(Module::class, $id, 'modules');

        $this->_view->load('modules/edit', [
            'title' => 'Modulos',
            'subject' => 'Editar Módulo',
            'module'   => $module,
            'send'   => $this->encrypt($this->getForm()),
            'process' => "modules/update/$id",
            'action' => 'edit',
        ]);
    }

    public function update($id = null)
    {
        $this->validatePUT();
        $module = Validate::validateModel(Module::class, $id, 'modules');

        $data = [
            'name' => Filter::getPost('name')
        ];

        $rules = [
            'name' => 'required|min:3|max:100|string'
        ];

        $this->validateForm("modules/edit/$id", $data, $rules);

        $module = $module;
        $module->name = $data['name'];
        $module->save();

        Flash::success('Módulo actualizado correctamente.');
        $this->redirect('modules/show/' . $id);
    }
}