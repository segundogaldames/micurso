<?php
namespace controllers;

use application\Controller;
use application\Flash;
use application\Helper;
use application\Session;
use application\Validate;
use application\Filter;
use models\Requirement;
use models\Course;

class RequirementsController extends Controller
{
    private $_module;

    public function __construct()
    {
        $this->validateSession();
        parent::__construct();
        $this->_module = $this->getModule('Requirement');
    }

    public function index()
    {
        $this->redirect();
    }

    public function requirementsCourse($course = null)
    {
        $this->validatePermission($this->_module, 'Listar');
        $exist = Validate::validateModel(Course::class, $course, 'courses');

        $this->_view->load('requirements/requirementsCourse',[
            'title' => 'Requerimientos',
            'subject' => 'Lista de Metas ' . $exist->title,
            'requirements' => Requirement::with('course')->where('course_id', (int) $course)->get(),
            'action' =>  'index',
            'route_create' => "requirements/create/$course",
            'button_create' => 'Nuevo Requerimiento'
        ]);
    }

    public function create($course = null)
    {
        $this->validatePermission($this->_module, 'Crear');
        $exist = Validate::validateModel(Course::class, $course, 'courses');

        $this->_view->load('requirements/create', [
            'title' => 'Requerimientos',
            'subject' => 'Nuevo Requerimiento',
            'requirement' => Session::get('form_data') ?? [],
            'course' => $course,
            'send'   => $this->encrypt($this->getForm()),
            'process' => "requirements/store/$course",
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
            'name' => 'required|min:3|max:100|string|unique:requirements,name'
        ];

        $this->validateForm("requirements/create/$course", $data, $rules);

        $requirement = new Requirement();
        $requirement->name = Helper::getTitle($data['name']);
        $requirement->course_id = (int) $course;
        $requirement->save();

        Flash::success('Requerimiento creado correctamente.');
        $this->redirect('requirements/requirementsCourse/' . $course);
    }

    public function show($id = null)
    {
        $this->validatePermission($this->_module, 'Leer');
        $requirement = Validate::validateModel(Requirement::class, $id, 'courses');

        $this->_view->load('requirements/show', [
            'title' => 'Requerimientos',
            'subject' => 'Detalle Requerimiento',
            'requirement'  => $requirement,
            'send'   => $this->encrypt($this->getForm()),
            'process' => "requirements/delete",
        ]);
    }

    public function edit($id = null)
    {
        $this->validatePermission($this->_module, 'Editar');
        //Helper::debuger($this->_module);
        $requirement = Validate::validateModel(Requirement::class, $id, 'courses');

        $this->_view->load('requirements/edit', [
            'title' => 'Requerimientos',
            'subject' => 'Editar Requerimiento',
            'requirement'   => $requirement,
            'course' => $requirement->course_id,
            'send'   => $this->encrypt($this->getForm()),
            'process' => "requirements/update/{$id}",
            'action' => 'edit',
        ]);
    }

    public function update($id = null)
    {
        $this->validatePermission($this->_module, 'Editar');
        $this->validatePUT();
        $requirement = Validate::validateModel(Requirement::class, $id, 'courses');

        $data = [
            'name' => Filter::getPost('name')
        ];

        $rules = [
            'name' => 'required|min:3|max:100|string'
        ];

        $this->validateForm("requirements/edit/$id", $data, $rules);

        $requirement = $requirement;
        $requirement->name = Helper::getTitle($data['name']);
        $requirement->save();

        Flash::success('Requerimiento actualizado correctamente.');
        $this->redirect('requirements/show/' . $id);
    }

    public function delete()
    {
        $this->validatePermission($this->_module, 'Eliminar');
        $this->validateDelete();

        $data = [
            'requirement' => Filter::getPost('requirement'),
            'course' => Filter::getPost('course')
        ];

$rules = [
            'requirement' => 'required|numeric',
            'course' => 'required|numeric',
        ];

        $this->validateForm("error/denied", $data, $rules);

        $requirement = Validate::validateModel(Requirement::class, $data['requirement'],'courses');

        $requirement->delete();
        
        Flash::success('Meta eliminada correctamente');
        $this->redirect('requirements/requirementsCourse/' . $data['course']);
    }
}