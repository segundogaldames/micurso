<?php

namespace controllers;

use application\Controller;
use application\Filter;
use application\Flash;
use application\Helper;
use application\Session;
use application\Validate;
use models\Section;
use models\Course;
use models\Lesson;

class SectionsController extends Controller
{
    private $_module;

    public function __construct()
    {
        $this->validateSession();
        parent::__construct(); 
        $this->_module = $this->getModule('Section');
    }

    public function index()
    {
        $this->redirect();
    }

    public function sectionsCourse($course = null)
    {
        $this->validatePermission($this->_module, 'Listar');
        $exist = Validate::validateModel(Course::class, $course, 'courses');

        $this->_view->load('sections/sectionsCourse', [
            'title' => 'Secciones',
            'sections'  => Section::with('course')->where('course_id', $exist->id)->get(),
            'subject' => 'Secciones ' . $exist->title,
            'action' =>  'index',
            'route_create' => "sections/create/$course",
            'button_create' => 'Nueva Sección'
        ]);
    }

    public function create($course = null)
    {
        $this->validatePermission($this->_module, 'Crear');
        $exist = Validate::validateModel(Course::class, $course, 'courses');

        $this->_view->load('sections/create', [
            'title' => 'Secciones',
            'subject' => 'Nueva Sección',
            'section' => Session::get('form_data') ?? [],
            'course' => $course,
            'send'   => $this->encrypt($this->getForm()),
            'process' => "sections/store/$course",
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
            'name' => 'required|min:3|max:100|string|unique:sections,name'
        ];

        $this->validateForm("sections/create/$course", $data, $rules);

        $section = new Section();
        $section->name = Helper::getTitle($data['name']);
        $section->slug = Helper::friendlyRoute($data['name']);
        $section->course_id = (int) $course;
        $section->save();

        Flash::success('Sección creada correctamente.');
        $this->redirect('courses/show/' . $course);
    }

    public function show($id = null)
    {
        $this->validatePermission($this->_module, 'Leer');
        $section = Validate::validateModel(Section::class, $id, 'courses');

        $this->_view->load('sections/show', [
            'title' => 'Secciones',
            'subject' => 'Detalle Sección',
            'section'  => $section->load('course'),
            'send' => $this->encrypt($this->getForm()),
            'process' => "sections/delete",
        ]);
    }

    public function edit($id = null)
    {
        $this->validatePermission($this->_module, 'Editar');
        $section = Validate::validateModel(Section::class, $id, 'courses');

        $this->_view->load('sections/edit', [
            'title' => 'Secciones',
            'subject' => 'Editar Sección',
            'section'   => $section->load('course'),
            'course' => $section->course_id,
            'send'   => $this->encrypt($this->getForm()),
            'process' => "sections/update/{$id}",
            'action' => 'edit',
        ]);
    }

    public function update($id = null)
    {
        $this->validatePermission($this->_module, 'Editar');
        $this->validatePUT();
        $section = Validate::validateModel(Section::class, $id, 'courses');

        $data = [
            'name' => Filter::getPost('name')
        ];

        $rules = [
            'name' => 'required|min:3|max:100|string'
        ];

        $this->validateForm("sections/edit/$id", $data, $rules);

        $section = $section;
        $section->name = Helper::getTitle($data['name']);
        $section->slug = Helper::friendlyRoute($data['name']);
        $section->save();

        Flash::success('Sección actualizada correctamente.');
        $this->redirect('sections/show/' . $id);
    }

    public function delete()
    {
        $this->validatePermission($this->_module, 'Eliminar');
        $this->validateDelete();

        $data = [
            'section' => Filter::getPost('section'),
            'course' => Filter::getPost('course')
        ];

        $rules = [
            'section' => 'required|numeric',
            'course' => 'required|numeric',
        ];

        $this->validateForm("error/denied", $data, $rules);

        $section = Validate::validateModel(Section::class, $data['section'],'courses');
        $lessons = Lesson::where('section_id', $section->id)->exists();

        if ($lessons) {
            Flash::error('Hay lecciones asociadas. No se puede eliminar');
            $this->redirect('courses/show/' . $data['course']);
        }

        $section->delete();
        
        Flash::success('Sección eliminada correctamente');
        $this->redirect('courses/show/' . $data['course']);
    }
}