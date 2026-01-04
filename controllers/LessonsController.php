<?php
namespace controllers;

use application\Controller;
use application\Flash;
use application\Helper;
use application\Session;
use application\Validate;
use application\Filter;
use models\Description;
use models\Lesson;
use models\Platform;
use models\Section;

class LessonsController extends Controller
{
    private $_module;

    public function __construct()
    {
        $this->validateSession();
        parent::__construct();
        $this->_module = $this->getModule('Lesson');
    }

    public function index()
    {
        $this->redirect();
    }

    public function lessonsSection($section = null)
    {
        $this->validatePermission($this->_module, 'Listar');
        $exist = Validate::validateModel(Section::class, $section, 'courses');

        $this->_view->load('lessons/lessonsSection',[
            'title' => 'Lecciones',
            'subject' => 'Lista de Lecciones',
            'lessons' => Lesson::with(['platform','section'])->where('section_id',(int)$section)->get(),
            'section' => $exist,
            'action' =>  'index',
            'route_create' => "lessons/create/$section",
            'button_create' => 'Nueva Lección'
        ]);
    }

    public function create($section = null)
    {
        $this->validatePermission($this->_module, 'Crear');
        $exist = Validate::validateModel(Section::class, $section, 'courses');

        $this->_view->load('lessons/create', [
            'title' => 'Lecciones',
            'subject' => 'Nueva Lección',
            'lesson' => Session::get('form_data') ?? [],
            'platforms' => Platform::select('id','name')->orderBy('name')->get(),
            'send'   => $this->encrypt($this->getForm()),
            'process' => "lessons/store/$section",
            'action' => 'create',
        ]);
    }

    public function store($section = null)
    {
        $this->validatePermission($this->_module, 'Crear');
        $exist = Validate::validateModel(Section::class, $section, 'courses');
        
        $data = [
            'name' => Filter::getPost('name'),
            'link' => Filter::getPostRaw('link'),
            'iframe' => Filter::getPostHtml('iframe'),
            'platform' => Filter::getPost('platform'),
        ];

        $rules = [
            'name' => 'required|min:3|max:100|string|unique:lessons,name',
            'link' => 'required|active_url',
            'iframe' => 'required',
            'platform' => 'required|numeric'
        ];

        $this->validateForm("lessons/create/$section", $data, $rules);

        $lesson = new Lesson();
        $lesson->name = Helper::getTitle($data['name']);
        $lesson->slug = Helper::friendlyRoute($data['name']);
        $lesson->link = $data['link'];
        $lesson->iframe = $data['iframe'];
        $lesson->platform_id = $data['platform'];
        $lesson->section_id = $exist->id;
        $lesson->save();

        Flash::success('Lección creada correctamente.');
        $this->redirect('lessons/lessonsSection/' . $section);
    }

    public function show($id = null)
    {
        $this->validatePermission($this->_module, 'Leer');
        $lesson = Validate::validateModel(Lesson::class, $id, 'courses');

        $this->_view->load('lessons/show', [
            'title' => 'Lecciones',
            'subject' => 'Detalle Lección',
            'lesson'  => $lesson->load('platform','section'),
            'send'   => $this->encrypt($this->getForm()),
            'process' => "lessons/delete",
        ]);
    }

    public function edit($id = null)
    {
        $this->validatePermission($this->_module, 'Crear');
        $lesson = Validate::validateModel(Lesson::class, $id, 'courses');

        $this->_view->load('lessons/edit', [
            'title' => 'Lecciones',
            'subject' => 'Editar Lección',
            'lesson' => $lesson->load('platform','section'),
            'platform' => Platform::select('id','name')->orderBy('name')->get(),
            'section' => Section::select('id','name')->orderBy('name')->get(),
            'send'   => $this->encrypt($this->getForm()),
            'process' => "lessons/update/$id",
            'action' => 'edit',
        ]);
    }

    public function update($id = null)
    {
        $this->validatePermission($this->_module, 'Crear');
        $lesson = Validate::validateModel(Lesson::class, $id, 'courses');
        
        $data = [
            'name' => Filter::getPost('name'),
            'link' => Filter::getPostRaw('link'),
            'iframe' => Filter::getPostHtml('iframe'),
            'platform' => Filter::getPost('platform'),
            'section' => Filter::getPost('section')
        ];

        $rules = [
            'name' => 'required|min:3|max:100|string',
            'link' => 'required|active_url',
            'iframe' => 'required',
            'platform' => 'required|numeric',
            'section' => 'required|numeric',
        ];

        $this->validateForm("lessons/edit/$id", $data, $rules);

        $lesson = $lesson;
        $lesson->name = Helper::getTitle($data['name']);
        $lesson->slug = Helper::friendlyRoute($data['name']);
        $lesson->link = $data['link'];
        $lesson->iframe = $data['iframe'];
        $lesson->platform_id = $data['platform'];
        $lesson->section_id = (int) $data['section'];
        $lesson->save();

        Flash::success('Lección actualizada correctamente.');
        $this->redirect('lessons/show/' . $id);
    }

    public function delete()
    {
        $this->validatePermission($this->_module, 'Eliminar');
        $this->validateDelete();

        $data = [
            'lesson' => Filter::getPost('lesson'),
            'section' => Filter::getPost('section')
        ];

        $rules = [
            'lesson' => 'required|numeric',
            'section' => 'required|numeric',
        ];

        $this->validateForm("error/denied", $data, $rules);

        $lesson = Validate::validateModel(Lesson::class, $data['lesson'],'courses');
        $descriptions = Description::where('lesson_id', $lesson->id)->exists();

        if ($descriptions) {
            Flash::error('Hay descripciones asociadas. No se puede eliminar');
            $this->redirect('lessons/lessonsSection/' . $data['section']);
        }

        $lesson->delete();
        
        Flash::success('Categoría eliminada correctamente');
        $this->redirect('lessons/lessonsSection/' . $data['section']);
    }
}