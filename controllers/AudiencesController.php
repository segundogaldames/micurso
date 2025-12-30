<?php 
namespace controllers;

use application\Controller;
use application\Flash;
use application\Helper;
use application\Session;
use application\Validate;
use application\Filter;
use models\Audience;
use models\Course; 

/**
 * 
 */
class AudiencesController extends Controller
{
	
	private $_module;

    public function __construct()
    {
        $this->validateSession();
        parent::__construct();
        $this->_module = $this->getModule('Audience');
    }

    public function index()
    {
        $this->validatePermission($this->_module, 'Listar');

        $this->_view->load('audiences/index',[
            'title' => 'Audiencias',
            'subject' => 'Lista de Audiencias',
            'audiences' => Audience::select('id','name')->get(),
            'action' =>  'list',
        ]);
    }

    public function create($course = null)
    {
        $this->validatePermission($this->_module, 'Crear');
        $exist = Validate::validateModel(Course::class, $course, 'courses');

        $this->_view->load('audiences/create', [
            'title' => 'Audiencias',
            'subject' => 'Nueva Audiencia',
            'audience' => Session::get('form_data') ?? [],
            'send'   => $this->encrypt($this->getForm()),
            'process' => "audiences/store/$course",
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
            'name' => 'required|min:3|max:100|string|unique:audiences,name'
        ];
        
        $this->validateForm("audiences/create/$course", $data, $rules);
        
        $audience = new Audience();
        $audience->name = Helper::getTitle($data['name']);
        $audience->course_id = (int) $course;
        $audience->save();

        Flash::success('Audiencia creada correctamente.');
        $this->redirect('audiences/audiencesCourse/' . $course);
    }

    public function audiencesCourse($course = null)
    {
        $this->validatePermission($this->_module, 'Listar');
        $exist = Validate::validateModel(Course::class, $course, 'courses');

        $this->_view->load('audiences/audiencesCourse',[
            'title' => 'Audiencias',
            'subject' => 'Audiencias ' . $exist->title,
            'audiences' => Audience::select('id','name')->where('course_id', $exist->id)->get(),
            'action' =>  'index',
            'route_create' => "audiences/create/$course",
            'button_create' => 'Nueva Audiencia'
        ]);
    }

    public function show($id = null)
    {
        $this->validatePermission($this->_module, 'Leer');
        $audience = Validate::validateModel(Audience::class, $id, 'courses');

        $this->_view->load('audiences/show', [
            'title' => 'Audiencias',
            'subject' => 'Detalle Audiencia',
            'audience'  => $audience,
            'send'   => $this->encrypt($this->getForm()),
            'process' => "audiences/delete",
        ]);
    }

    public function edit($id = null)
    {
        $this->validatePermission($this->_module, 'Editar');
        $audience = Validate::validateModel(Audience::class, $id, 'courses');

        $this->_view->load('audiences/edit', [
            'title' => 'Audiencias',
            'subject' => 'Editar Audiencia',
            'audience'   => $audience,
            'send'   => $this->encrypt($this->getForm()),
            'process' => "audiences/update/{$id}",
            'action' => 'edit',
        ]);
    }

    public function update($id = null)
    {
        $this->validatePermission($this->_module, 'Editar');
        $this->validatePUT();
        $audience = Validate::validateModel(Audience::class, $id, 'courses');

        $data = [
            'name' => Filter::getPost('name')
        ];

        $rules = [
            'name' => 'required|min:3|max:100|string'
        ];

        $this->validateForm("audiences/edit/$id", $data, $rules);

        $audience = $audience;
        $audience->name = Helper::getTitle($data['name']);
        $audience->save();

        Flash::success('Categoría actualizada correctamente.');
        $this->redirect('audiences/show/' . $id);
    }

    public function delete()
    {
        $this->validatePermission($this->_module, 'Eliminar');
        $this->validateDelete();

        $data = [
            'audience' => Filter::getPost('audience'),
            'course' => Filter::getPost('course'),
        ];

        $rules = [
            'audience' => 'required|numeric',
            'course' => 'required|numeric',
        ];

        $this->validateForm("error/denied", $data, $rules);

        $audience = Validate::validateModel(Audience::class, $data['audience'],'courses');

        $audience->delete();
        
        Flash::success('Audiencia eliminada correctamente');
        $this->redirect('audiences/audiencesCourse/' . $data['course']);
    }
}
