<?php
namespace controllers;

use application\Controller;
use application\Filter;
use application\Flash;
use application\Helper;
use application\Session;
use application\Validate;
use models\Permission;
use models\Module;
use models\Role;
use models\Task;


class PermissionsController extends Controller
{
    private $_module;

    public function __construct()
    {
        $this->validateSession();
        $this->validateRole(['Administrador']);
        parent::__construct(); 
    }

    public function index()
    {
        $this->redirect();
    }

    public function permissionsModule($mod = null)
    {
        $module = Validate::validateModel(Module::class, $mod, 'modules');
        $this->_view->load('permissions/permissionsModule', [
            'title' => 'Permisos',
            'permissions'  => Permission::with(['module','role','task'])->get(),
            'subject' => 'Lista de Permisos',
            'action' =>  'index',
            'route_create' => "permissions/create/$mod",
            'button_create' => 'Nuevo Permiso'
        ]);
    }

    public function create($mod = null)
    {
        $module = Validate::validateModel(Module::class, $mod, 'modules');

        $this->_view->load('permissions/create', [
            'title' => 'Permisos',
            'subject' => 'Nuevo Permiso',
            'permission' => Session::get('form_data') ?? [],
            'roles' => Role::select('id','name')->orderBy('name')->get(),
            'tasks' => Task::select('id','name')->orderBy('name')->get(),
            'module' => $module,
            'send'   => $this->encrypt($this->getForm()),
            'process' => "permissions/store/$mod",
            'action' => 'create',
        ]);
    }

    public function store($mod = null)
    {
        $module = Validate::validateModel(Module::class, $mod, 'modules');

        $data = [
            'role' => Filter::getPost('role'),
            'task' => Filter::getPost('task'),
        ];

        $rules = [
            'role' => 'required|numeric',
            'task' => 'required|numeric',
        ];

        $this->validateForm("permissions/create/$module->id", $data, $rules);

        $exist = Permission::select('id')
            ->where('module_id', $module->id)
            ->where('role_id', (int) $data['role'])
            ->where('task_id', (int) $data['task'])
            ->exists();

        if($exist){
            Flash::error('Este módulo ya tiene asociado este rol y esta tarea');
            $this->redirect('permissions/create/' . $mod);
        }

        $permission = new Permission();
        $permission->module_id = (int) $module->id;
        $permission->role_id = (int) $data['role'];
        $permission->task_id = (int) $data['task'];
        $permission->save();

        Flash::success('Permiso creado correctamente.');
        $this->redirect('permissions/permissionsModule/' . $mod);
    }

    public function show($id = null)
    {
        $permission = Validate::validateModel(Permission::class, $id, 'modules');

        $this->_view->load('permissions/show', [
            'title' => 'Permisos',
            'subject' => 'Detalle Permiso',
            'permission'  => $permission->load('module','role','task'),
            'send'   => $this->encrypt($this->getForm()),
            'process' => "permissions/delete",

        ]);
    }

    public function delete()
    {
        $this->validateDelete();
        $data = [
            'module' => Filter::getPost('module'),
            'permission' => Filter::getPost('permission'),
        ];

        $rules = [
            'module' => 'required|numeric',
            'permission' => 'required|numeric',
        ];

        $this->validateForm("error/denied", $data, $rules);

        $permission = Permission::find((int) $data['permission']);
        $permission->delete();
        Flash::success('Permiso eliminado correctamente');
        $this->redirect('permissions/permissionsModule/' . $data['module']);
    }
}