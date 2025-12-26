<?php

namespace controllers;

use application\Controller;
use application\Filter;
use application\Flash;
use application\Helper;
use application\Session;
use application\Validate;
use models\Task;

class TasksController extends Controller
{
    public function __construct()
    {
        $this->validateSession();
        $this->validateRole(['Administrador']);
        parent::__construct(); 
    }

    public function index()
    {
        $this->_view->load('tasks/index', [
            'title' => 'Tareas',
            'subject' => 'Lista de Tareas',
            'tasks'  => Task::select('id','name')->get(),
            'action' =>  'index',
            'route_create' => "tasks/create",
            'button_create' => 'Nueva Tarea'
        ]);
    }

    public function create()
    {
        $this->_view->load('tasks/create', [
            'title' => 'Tareas',
            'subject' => 'Nueva Tarea',
            'task' => Session::get('form_data') ?? [],
            'send'   => $this->encrypt($this->getForm()),
            'process' => "tasks/store",
            'action' => 'create',
        ]);
    }

    public function store()
    {
        $data = [
            'name' => Filter::getPost('name')
        ];

        $rules = [
            'name' => 'required|min:3|max:100|string|unique:tasks,name'
        ];

        $this->validateForm('tasks/create', $data, $rules);

        $task = new Task();
        $task->name = $data['name'];
        $task->save();

        Flash::success('Tarea creada correctamente.');
        $this->redirect('tasks');
    }

    public function show($id = null)
    {
        $task = Validate::validateModel(Task::class, $id, 'tasks');

        $this->_view->load('tasks/show', [
            'title' => 'Tareas',
            'subject' => 'Detalle Tarea',
            'task'  => $task
        ]);
    }

    public function edit($id = null)
    {
        $task = Validate::validateModel(Task::class, $id, 'tasks');

        $this->_view->load('tasks/edit', [
            'title' => 'Tareas',
            'subject' => 'Editar Tarea',
            'task'   => $task,
            'send'   => $this->encrypt($this->getForm()),
            'process' => "tasks/update/$id",
            'action' => 'edit',
        ]);
    }

    public function update($id = null)
    {
        $this->validatePUT();
        $task = Validate::validateModel(Task::class, $id, 'tasks');

        $data = [
            'name' => Filter::getPost('name')
        ];

        $rules = [
            'name' => 'required|min:3|max:100|string'
        ];

        $this->validateForm("tasks/edit/$id", $data, $rules);

        $task = $task;
        $task->name = $data['name'];
        $task->save();

        Flash::success('Tarea actualizada correctamente.');
        $this->redirect('tasks/show/' . $id);
    }
}