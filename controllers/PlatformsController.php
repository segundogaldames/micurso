<?php
namespace controllers;

use application\Controller;
use application\Flash;
use application\Helper;
use application\Session;
use application\Validate;
use application\Filter;
use models\Lesson;
use models\Platform;

class PlatformsController extends Controller
{
    private $_module;

    public function __construct()
    {
        $this->validateSession();
        parent::__construct();
        $this->_module = $this->getModule('Platform');
    }

    public function index()
    {
        $this->validatePermission($this->_module, 'Listar');

        $this->_view->load('platforms/index',[
            'title' => 'Plataformas',
            'subject' => 'Lista de Plataformas',
            'platforms' => Platform::select('id','name')->get(),
            'action' =>  'index',
            'route_create' => "platforms/create",
            'button_create' => 'Nueva Plataforma'
        ]);
    }

    public function create()
    {
        $this->validatePermission($this->_module, 'Crear');

        $this->_view->load('platforms/create', [
            'title' => 'Plataformas',
            'subject' => 'Nueva Plataforma',
            'platform' => Session::get('form_data') ?? [],
            'send'   => $this->encrypt($this->getForm()),
            'process' => "platforms/store",
            'action' => 'create',
        ]);
    }

    public function store()
    {
        $this->validatePermission($this->_module, 'Crear');
        
        $data = [
            'name' => Filter::getPost('name')
        ];

        $rules = [
            'name' => 'required|min:3|max:100|string|unique:platforms,name'
        ];

        $this->validateForm('platforms/create', $data, $rules);

        $platform = new Platform();
        $platform->name = Helper::getTitle($data['name']);
        $platform->save();

        Flash::success('Plataforma creada correctamente.');
        $this->redirect('platforms');
    }

    public function show($id = null)
    {
        $this->validatePermission($this->_module, 'Leer');
        $platform = Validate::validateModel(Platform::class, $id, 'platforms');

        $this->_view->load('platforms/show', [
            'title' => 'Plataformas',
            'subject' => 'Detalle Plataforma',
            'platform'  => $platform,
            'send'   => $this->encrypt($this->getForm()),
            'process' => "platforms/delete",
        ]);
    }

    public function edit($id = null)
    {
        $this->validatePermission($this->_module, 'Editar');
        $platform = Validate::validateModel(Platform::class, $id, 'platforms');

        $this->_view->load('platforms/edit', [
            'title' => 'Plataformas',
            'subject' => 'Editar Plataforma',
            'platform'   => $platform,
            'send'   => $this->encrypt($this->getForm()),
            'process' => "platforms/update/{$id}",
            'action' => 'edit',
        ]);
    }

    public function update($id = null)
    {
        $this->validatePermission($this->_module, 'Editar');
        $this->validatePUT();
        $platform = Validate::validateModel(Platform::class, $id, 'platforms');

        $data = [
            'name' => Filter::getPost('name')
        ];

        $rules = [
            'name' => 'required|min:3|max:100|string'
        ];

        $this->validateForm("platforms/edit/$id", $data, $rules);

        $platform = $platform;
        $platform->name = Helper::getTitle($data['name']);
        $platform->save();

        Flash::success('Plataforma actualizada correctamente.');
        $this->redirect('platforms/show/' . $id);
    }

    public function delete()
    {
        $this->validatePermission($this->_module, 'Eliminar');
        $this->validateDelete();

        $data = [
            'platform' => Filter::getPost('platform'),
        ];

        $rules = [
            'platform' => 'required|numeric',
        ];

        $this->validateForm("error/denied", $data, $rules);

        $platform = Validate::validateModel(Platform::class, $data['platform'],'platforms');
        $lessons = Lesson::where('platform_id', $platform->id)->exists();

        if ($lessons) {
            Flash::error('Hay lecciones asociadas. No se puede eliminar');
            $this->redirect('platforms');
        }

        $platform->delete();
        
        Flash::success('Plataforma eliminada correctamente');
        $this->redirect('platforms');
    }
}