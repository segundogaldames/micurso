<?php

namespace controllers;

use application\Controller;
use application\Filter;
use application\Flash;
use application\Helper;
use application\Session;
use application\Validate;
use application\Password;
use models\Role;
use models\User;

class UsersController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->_view->load('users/index', [
            'title' => 'Usuarios',
            'users' => User::with('role')->get(),
            'subject' => 'Lista de Usuarios',
            'action' => 'index',
            'route_create' => "users/create",
            'button_create' => 'Nuevo Usuario'
        ]);
    }

    public function create()
    {
        $this->_view->load('users/create', [
            'title' => 'Usuarios',
            'subject' => 'Nuevo Usuario',
            'user' => Session::get('form_data') ?? [],
            'roles' => Role::select('id', 'name')->orderBy('name')->get(),
            'send' => $this->encrypt($this->getForm()),
            'process' => "users/store",
            'action' => 'create',
        ]);
    }

    public function store()
    {
        $data = [
            'name' => Filter::getPost('name'),
            'email' => Filter::getPost('email'),
            'role' => Filter::getPost('role'),
            'password' => Filter::getPostRaw('password'),
            'confirm' => Filter::getPostRaw('confirm'),
            'image' => $_FILES['image']['name'] ?? null
        ];

        $rules = [
            'name' => 'required|min:3|max:100|string',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|numeric',
            'password' => 'required|strong_password',
            'confirm' => 'required|same:password',
            'image' => 'image|mimes:jpg,png,jpeg|maxsize:5M'
        ];

        $this->validateForm('users/create', $data, $rules);

        // Procesar imagen (opcional)
        if ($_FILES['image']['name']) {

            $image = $_FILES['image']['name'];
            $tmp_name = $_FILES['image']['tmp_name'];
            $upload = ROOT . 'public' . DS . 'img' . DS . 'users' . DS;
            $fichero = $upload . basename($_FILES['image']['name']);
        }


        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Password::hash($data['password']);
        $user->active = 1;
        $user->role_id = (int)$data['role'];

        if (move_uploaded_file($_FILES['image']['tmp_name'], $fichero)) {
            $user->image = $image;
        }

        $user->save();

        Flash::success('Usuario creado correctamente.');
        $this->redirect('users');
    }

    public function show($id = null)
    {
        $user = Validate::validateModel(User::class, $id, 'users');

        $this->_view->load('users/show', [
            'title' => 'Usuarios',
            'subject' => 'Detalle Usuario',
            'user' => $user->load('role')
        ]);
    }

    public function edit($id = null)
    {
        $user = Validate::validateModel(User::class, $id, 'users');

        $this->_view->load('users/edit', [
            'title' => 'Usuarios',
            'subject' => 'Editar Usuario',
            'user' => $user->load('role'),
            'roles' => Role::select('id', 'name')->orderBy('name')->get(),
            'send' => $this->encrypt($this->getForm()),
            'process' => "users/update/$id",
            'action' => 'edit',
        ]);
    }

    public function update($id = null)
    {
        $this->validatePUT();
        $user = Validate::validateModel(User::class, $id, 'users');

        $data = [
            'name' => Filter::getPost('name'),
            'role' => Filter::getPost('role'),
            'active' => Filter::getPost('active'),
        ];

        $rules = [
            'name' => 'required|min:3|max:100|string',
            'role' => 'required|numeric',
            'active' => 'required|numeric',
        ];

        $this->validateForm("users/edit/$id", $data, $rules);

        $user->name = $data['name'];
        $user->active = (int)$data['active'];
        $user->role_id = (int)$data['role'];
        $user->save();

        Flash::success('Usuario actualizado correctamente.');
        $this->redirect('users/show/' . $id);
    }

    public function editImage($id = null)
    {
        $user = Validate::validateModel(User::class, $id, 'users');
        $this->_view->load('users/editImage', [
            'title' => 'Usuarios',
            'subject' => 'Editar Imagen Usuario',
            'user' => $user->load('role'),
            'send' => $this->encrypt($this->getForm()),
            'process' => "users/updateImage/$id",
            'action' => 'edit',
        ]);
    }

    public function updateImage($id = null)
    {
        //Helper::debuger($_POST);
        $this->validatePUT();
        $user = Validate::validateModel(User::class, $id, 'users');

        $data = [
            'image' => $_FILES['image']['name'] ?? null
        ];

        $rules = [
            'image' => 'required|image|mimes:jpg,png,jpeg|maxsize:5M'
        ];

        $this->validateForm("users/editImage/$id", $data, $rules);

        $image = $_FILES['image']['name'];
        $tmp_name = $_FILES['image']['tmp_name'];
        $upload = ROOT . 'public' . DS . 'img' . DS . 'users' . DS;
        $fichero = $upload . basename($_FILES['image']['name']);

        if (move_uploaded_file($_FILES['image']['tmp_name'], $fichero)) {
            $user = User::find((int)$id);
            $user->image = $image;
            $user->save();

            Flash::success('La imagen se ha cargado correctamente');
        }else{
            Flash::error('La imagen no pudo cargarse correctamente... intente nuevamente');
        }

        Flash::success('Imagen de usuario actualizada correctamente.');
        $this->redirect('users/show/' . $id);
    }
}
