<?php

namespace controllers;

use application\Controller;
use application\Filter;
use application\Flash;
use application\Helper;
use application\Session;
use application\Validate;
use models\Role;

class RolesController extends Controller
{
    public function __construct()
    {
        parent::__construct(); 
    }

    public function index()
    {
        $this->_view->load('roles/index', [
            'title' => 'Roles',
            'roles'  => Role::select('id','name')->get(),
            'subject' => 'Lista de Roles',
            'action' =>  'index',
            'route_create' => "roles/create",
            'button_create' => 'Nuevo Rol'
        ]);
    }

    public function create()
    {
        $this->_view->load('roles/create', [
            'title' => 'Roles',
            'subject' => 'Nuevo Rol',
            'role' => Session::get('form_data') ?? [],
            'send'   => $this->encrypt($this->getForm()),
            'process' => "roles/store",
            'action' => 'create',
        ]);
    }

    public function store()
    {
        $data = [
            'name' => Filter::getPost('name')
        ];

        $rules = [
            'name' => 'required|min:3|max:100|string|unique:roles,name'
        ];

        $this->validateForm('roles/create', $data, $rules);

        $role = new Role();
        $role->name = $data['name'];
        $role->save();

        Flash::success('Rol creado correctamente.');
        $this->redirect('roles');
    }

    public function show($id = null)
    {
        $role = Validate::validateModel(Role::class, $id, 'roles');

        $this->_view->load('roles/show', [
            'title' => 'Roles',
            'subject' => 'Detalle Rol',
            'role'  => $role
        ]);
    }

    public function edit($id = null)
    {
        $role = Validate::validateModel(Role::class, $id, 'roles');

        $this->_view->load('roles/edit', [
            'title' => 'Roles',
            'subject' => 'Editar Rol',
            'role'   => $role,
            'send'   => $this->encrypt($this->getForm()),
            'process' => "roles/update/$id",
            'action' => 'edit',
        ]);
    }

    public function update($id = null)
    {
        $this->validatePUT();
        $role = Validate::validateModel(Role::class, $id, 'roles');

        $data = [
            'name' => Filter::getPost('name')
        ];

        $rules = [
            'name' => 'required|min:3|max:100|string'
        ];

        $this->validateForm("roles/edit/$id", $data, $rules);

        $role = $role;
        $role->name = $data['name'];
        $role->save();

        Flash::success('Rol actualizado correctamente.');
        $this->redirect('roles/show/' . $id);
    }
}