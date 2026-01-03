<?php
namespace controllers;

use application\Controller;
use application\Flash;
use application\Helper;
use application\Session;
use application\Validate;
use application\Filter;
use models\Goal;
use models\Course;

class GoalsController extends Controller
{
    private $_module;

    public function __construct()
    {
        $this->validateSession();
        parent::__construct();
        $this->_module = $this->getModule('Goal');
    }

    public function index()
    {
        $this->redirect();
    }

    public function goalsCourse($course = null)
    {
        $this->validatePermission($this->_module, 'Listar');
        $exist = Validate::validateModel(Course::class, $course, 'courses');

        $this->_view->load('goals/goalsCourse',[
            'title' => 'Metas',
            'subject' => 'Lista de Metas ' . $exist->title,
            'goals' => Goal::with('course')->where('course_id', (int) $course)->get(),
            'action' =>  'index',
            'route_create' => "goals/create/$course",
            'button_create' => 'Nueva Meta'
        ]);
    }

    public function create($course = null)
    {
        $this->validatePermission($this->_module, 'Crear');
        $exist = Validate::validateModel(Course::class, $course, 'courses');

        $this->_view->load('goals/create', [
            'title' => 'Metas',
            'subject' => 'Nueva Meta',
            'goal' => Session::get('form_data') ?? [],
            'course' => $course,
            'send'   => $this->encrypt($this->getForm()),
            'process' => "goals/store/$course",
            'action' => 'create',
        ]);
    }

    public function store($course = null)
    {
        $this->validatePermission($this->_module, 'Crear');
        $exist = Validate::validateModel(Course::class, $course, 'courses');
        
        $data = [
            'name' => Filter::getPost('name')
        ];

        $rules = [
            'name' => 'required|min:3|max:100|string|unique:goals,name'
        ];

        $this->validateForm("goals/create/$course", $data, $rules);

        $goal = new Goal();
        $goal->name = Helper::getTitle($data['name']);
        $goal->course_id = (int) $course;
        $goal->save();

        Flash::success('Meta creada correctamente.');
        $this->redirect('goals/goalsCourse/' . $course);
    }

    public function show($id = null)
    {
        $this->validatePermission($this->_module, 'Leer');
        $goal = Validate::validateModel(Goal::class, $id, 'courses');

        $this->_view->load('goals/show', [
            'title' => 'Metas',
            'subject' => 'Detalle Meta',
            'goal'  => $goal,
            'send'   => $this->encrypt($this->getForm()),
            'process' => "goals/delete",
        ]);
    }

    public function edit($id = null)
    {
        $this->validatePermission($this->_module, 'Editar');
        $goal = Validate::validateModel(Goal::class, $id, 'courses');

        $this->_view->load('goals/edit', [
            'title' => 'Metas',
            'subject' => 'Editar Meta',
            'goal'   => $goal,
            'course' => $goal->course_id,
            'send'   => $this->encrypt($this->getForm()),
            'process' => "goals/update/{$id}",
            'action' => 'edit',
        ]);
    }

    public function update($id = null)
    {
        $this->validatePermission($this->_module, 'Editar');
        $this->validatePUT();
        $goal = Validate::validateModel(Goal::class, $id, 'courses');

        $data = [
            'name' => Filter::getPost('name')
        ];

        $rules = [
            'name' => 'required|min:3|max:100|string'
        ];

        $this->validateForm("goals/edit/$id", $data, $rules);

        $goal = $goal;
        $goal->name = Helper::getTitle($data['name']);
        $goal->save();

        Flash::success('Meta actualizada correctamente.');
        $this->redirect('goals/show/' . $id);
    }

    public function delete()
    {
        $this->validatePermission($this->_module, 'Eliminar');
        $this->validateDelete();

        $data = [
            'goal' => Filter::getPost('goal'),
            'course' => Filter::getPost('course')
        ];

        $rules = [
            'goal' => 'required|numeric',
            'course' => 'required|numeric',
        ];

        $this->validateForm("error/denied", $data, $rules);

        $goal = Validate::validateModel(Goal::class, $data['goal'],'courses');

        $goal->delete();
        
        Flash::success('Meta eliminada correctamente');
        $this->redirect('goals/goalsCourse/' . $data['course']);
    }
}