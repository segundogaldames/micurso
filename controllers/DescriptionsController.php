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

class DescriptionsController extends Controller
{
    private $_module;

    public function __construct()
    {
        $this->validateSession();
        parent::__construct();
        $this->_module = $this->getModule('Description');
    }

    public function index()
    {
        $this->redirect();
    }

    public function create($lesson = null)
    {
        $this->validatePermission($this->_module, 'Crear');
        $lesson_exist = Validate::validateModel(Lesson::class, $lesson, 'courses');

        $this->_view->load('descriptions/create', [
            'title' => 'Descripción',
            'subject' => 'Nueva Descripción '. $lesson_exist->name,
            'description' => Session::get('form_data') ?? [],
            'lesson' => $lesson,
            'send'   => $this->encrypt($this->getForm()),
            'process' => "descriptions/store/$lesson",
            'action' => 'create',
        ]);
    }

    public function store($lesson = null)
    {
        $this->validatePermission($this->_module, 'Crear');
        $lesson_exist = Validate::validateModel(Lesson::class, $lesson, 'courses');
        
        $data = [
            'text' => Filter::getPost('text')
        ];

        $rules = [
            'text' => 'required|min:3|max:100|string'
        ];

        $this->validateForm("descriptions/create/$lesson", $data, $rules);

        $description_exist = Description::select('id')->where('lesson_id', (int) $lesson)->exists();

        if($description_exist){
            Flash::error('Esta lección ya tiene una descripción.');
            $this->redirect('descriptions/create/' . $lesson);
        }

        $description = new Description();
        $description->text = Helper::getSentence($data['text']);
        $description->lesson_id = (int) $lesson;
        $description->save();

        Flash::success('Descripción creada correctamente.');
        $this->redirect('lessons/show/' . $lesson);
    }

    public function edit($id = null)
    {
        $this->validatePermission($this->_module, 'Editar');
        $description = Validate::validateModel(Description::class, $id, 'courses');

        $this->_view->load('descriptions/edit', [
            'title' => 'Descripcion',
            'subject' => 'Editar Descripción',
            'description'   => $description,
            'lesson' => $description->lesson_id,
            'send'   => $this->encrypt($this->getForm()),
            'process' => "descriptions/update/{$id}",
            'action' => 'edit',
        ]);
    }

    public function update($id = null)
    {
        $this->validatePermission($this->_module, 'Editar');
        $this->validatePUT();
        $description = Validate::validateModel(Description::class, $id, 'descriptions');

        $data = [
            'text' => Filter::getPost('text')
        ];

        $rules = [
            'text' => 'required|min:3|max:100|string'
        ];

        $this->validateForm("descriptions/edit/$id", $data, $rules);

        $description = $description;
        $description->text = Helper::getSentence($data['text']);
        $description->save();

        Flash::success('Descripción actualizada correctamente.');
        $this->redirect('lessons/show/' . $description->lesson_id);
    }

    public function delete()
    {
        $this->validatePermission($this->_module, 'Eliminar');
        $this->validateDelete();
        
        $data = [
            'description' => Filter::getPost('description'),
            'lesson' => Filter::getPost('lesson'),
        ];
        
        $rules = [
            'description' => 'required|numeric',
            'lesson' => 'required|numeric',
        ];
        
        $this->validateForm("error/denied", $data, $rules);
        
        $description = Validate::validateModel(Description::class, $data['description'],'courses');
        //Helper::debuger($description);

        $description->delete();
        
        Flash::success('Descripción eliminada correctamente');
        $this->redirect('lessons/show/' . $data['lesson']);
    }
}