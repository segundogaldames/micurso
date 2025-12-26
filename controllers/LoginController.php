<?php

namespace controllers;

use application\Controller;
use application\Session;
use application\Filter;
use application\Flash;
use application\Validate;
use application\Password;
use models\User;

final class LoginController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(): void
    {
        $this->redirect('login/login');
    }

    public function login()
    {
        if (Session::get('authenticate')) {
            $this->redirect();
        }

        $this->_view->load('login/login', [
            'titulo' => 'Login',
            'subject' => 'Inicio de Sesión',
            'send' => $this->encrypt($this->getForm()),
            'process' => 'login/init',
            'action' => 'create',
        ]);
    }

    public function init()
    {
        if (Session::get('authenticate')) {
            $this->redirect();
        }

        $data = [
            'email' => Filter::getPost('email'),
            'password' => Filter::getPostRaw('password')
        ];

        $rules = [
            'email' => 'required|email',
            'password' => 'required'
        ];

        $this->validateForm('login/login', $data, $rules);

        $user = User::with('role')
            ->where('email', $data['email'])
            ->where('active', 1)
            ->first();

        if (!$user) {
            Flash::error('El usuario no está registrado.');
            $this->redirect('login/login');
        }

        if (!Password::verify(Filter::getPost('password'), $user->password)) {
            Flash::error('Las credenciales ingresadas no son válidas');
            $this->redirect('login/login');
        }


        Session::set('authenticate', true);
        Session::set('user_id', $user->id);
        Session::set('user_email', $user->email);
        Session::set('user_name', $user->name);
        Session::set('user_image', $user->image);
        Session::set('user_role', $user->role->name);
        Session::set('time', time());

        Flash::success('Bienvenido(a)');
        if($user->role_id == 1 || $user->role_id == 3 ){
            $this->redirect('admin');
        }

        $this->redirect();
    }

    public function logout()
    {
        Session::destroy();
        $this->redirect();
    }
}
