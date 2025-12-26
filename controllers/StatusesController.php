<?php

namespace controllers;

use application\Controller;
use application\Filter;
use application\Flash;
use application\Helper;
use application\Session;
use application\Validate;
use models\Status;

class StatusesController extends Controller
{
    private $_module;

    public function __construct()
    {
        $this->validateSession();
        parent::__construct(); 
        $this->_module = $this->getModule('Status');
    }

    public function index()
    {
        //Helper::debuger($this->_module);
        $this->validatePermission($this->_module, 'Listar');
        $this->_view->load('statuses/index', [
            'title' => 'Estados',
            'statuses'  => Status::select('id','name')->get(),
            'subject' => 'Lista de Estados',
            'action' =>  'index',
            'route_create' => "statuses/create",
            'button_create' => 'Nuevo Estado'
        ]);
    }

    public function create()
    {
        $this->validatePermission($this->_module, 'Crear');
        $this->_view->load('statuses/create', [
            'title' => 'Estados',
            'subject' => 'Nuevo Estado',
            'status' => Session::get('form_data') ?? [],
            'send'   => $this->encrypt($this->getForm()),
            'process' => "statuses/store",
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
            'name' => 'required|min:3|max:100|string|unique:statuses,name'
        ];

        $this->validateForm('statuses/create', $data, $rules);

        $status = new Status();
        $status->name = Helper::getTitle($data['name']);
        $status->save();

        Flash::success('Estado creado correctamente.');
        $this->redirect('statuses');
    }

    public function show($id = null)
    {
        $this->validatePermission($this->_module, 'Leer');
        $status = Validate::validateModel(Status::class, $id, 'statuses');

        $this->_view->load('statuses/show', [
            'title' => 'Estados',
            'subject' => 'Detalle Estado',
            'status'  => $status
        ]);
    }

    public function edit($id = null)
    {
        $this->validatePermission($this->_module, 'Editar');
        $status = Validate::validateModel(Status::class, $id, 'statuses');

        $this->_view->load('statuses/edit', [
            'title' => 'Estados',
            'subject' => 'Editar Estado',
            'status'   => $status,
            'send'   => $this->encrypt($this->getForm()),
            'process' => "statuses/update/{$id}",
            'action' => 'edit',
        ]);
    }

    public function update($id = null)
    {
        $this->validatePermission($this->_module, 'Editar');
        $this->validatePUT();
        $status = Validate::validateModel(Status::class, $id, 'statuses');

        $data = [
            'name' => Filter::getPost('name')
        ];

        $rules = [
            'name' => 'required|min:3|max:100|string'
        ];

        $this->validateForm("statuses/edit/$id", $data, $rules);

        $status = $status;
        $status->name = Helper::getTitle($data['name']);
        $status->save();

        Flash::success('Estado actualizado correctamente.');
        $this->redirect('statuses/show/' . $id);
    }
}