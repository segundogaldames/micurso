<?php

namespace application;

use application\Validator;
use application\Filter;
use application\Request;
use application\View;
use models\Module;
use models\Permission;

class Controller
{
    protected $_view;
    protected string $method = 'AES-256-CBC';
    protected string $secret_key = APP_NAME;
    protected string $secret_iv = 'TU_IV_SECRETO';

    public function __construct()
    {
        $this->_view = new View(new Request);
    }
    
    protected function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function base64url_decode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder > 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }
    // Método para encriptar / desencriptar (puedes modificar según tu necesidad)
    protected function encrypt(string $data): string
    {
        $key = hash('sha256', $this->secret_key, true);
        $iv = openssl_random_pseudo_bytes(16);

        $encrypted = openssl_encrypt($data, $this->method, $key, OPENSSL_RAW_DATA, $iv);

        $output = $iv . $encrypted;

        return $this->base64url_encode($output);
    }

    protected function decrypt(string $data): string
    {
        $key = hash('sha256', $this->secret_key, true);
        $data = $this->base64url_decode($data);

        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);

        return openssl_decrypt($encrypted, $this->method, $key, OPENSSL_RAW_DATA, $iv);
    }

    // Método para generar token simple para formulario
    protected function getForm(): string
    {
        $now = getdate();
        $now = $now['year'] . $now['month'] . $now['mday'] . $now['hours'];

        if (Session::get('authenticate')) {
            return Session::get('user_name') . $now;
        }

        return CTRL . $now;
    }

	protected function getMessages() 
	{
		$msg = Session::get('flash');
		Session::destroy('flash');
		return $msg ? $msg : ['type' => '', 'text' => ''];
	}

    protected function getModule($mod)
    {
        $module = Module::select('id')->where('name',$mod)->first();
        
        if (!$module) {
            $this->redirect('error/denied');
        }
        //Helper::debuger($module);

        return $module->id;
    }

    protected function redirect(string $route = ''): void
    {
        $url = BASE_URL . ($route ?: '');
        header("Location: $url");
        exit;
    }

    /**
     * Validar formulario con Validator
     *
     * @param string $route Ruta a redirigir si la validación falla
     * @param array $data Datos ya filtrados
     * @param array $rules Reglas en formato Validator
     */
    protected function validateForm(string $route, array $data, array $rules): void
    {
        //print_r($route);exit;
        if ($this->decrypt(Filter::getPostRaw('send')) !== $this->getForm()) {
            //die("Error de validacion");exit;
            $this->redirect('error/denied');
        }
        
        $validator = new Validator($data, $rules);
        
        if (!$validator->validate()) {
            Session::set('form_data', $_POST);
            Flash::error($validator->firstError());
            //print_r($route);exit;
            $this->redirect($route);
        }else{
            Session::destroy('form_data');
        }
    }

    protected function validateDelete()
	{
		if (Filter::getPost('_method') != 'DELETE') {
			$this->redirect('error/denied');
		}
	}

    protected function validatePUT()
	{
		if (Filter::getPost('_method') != 'PUT') {
			$this->redirect('error/denied');
		}
	}

    protected function validateRole($roles){

		if (is_array($roles)) {
			foreach ($roles as $role) {
				if (Session::get('user_role') == $role) {
					return true;
				}
			}
		}

		$this->redirect();
	}

    protected function validatePermission($module, $task)
    {
        //Helper::debuger($module);
        $permissions = Permission::with(['module','role','task'])->where('module_id',(int) $module)->get();
        //print_r($permisos);exit;
        if ($permissions->isEmpty()) {
            $this->redirect('error/denied');
        }

        $roles = [];

        foreach ($permissions as $permission) {
            if ($permission->task->name == $task) {
                $roles[] = $permission->role->name;
            }
        }

        if (empty($roles)) {
            $this->redirect('error/denied');
        }

        $this->validateRole($roles);
    }

    protected function validateSession(){
		if (!Session::get('authenticate')) {
			$this->redirect('login/login');
		}

		Session::resetId();
	}
}
