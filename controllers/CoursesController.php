<?php
namespace controllers;

use application\Controller;
use application\Filter;
use application\Flash;
use application\Helper;
use application\Session;
use application\Validate;
use models\Course;
use models\Category;
use models\Level;
use models\CourseUser;
use models\Price;
use models\Status;
use models\Section;

class CoursesController extends Controller
{
    private $_module;

    public function __construct(){
        parent::__construct();
        $this->_module = $this->getModule('Course');
    }

    public function index()
    {
        $this->validateSession();
        $this->validatePermission($this->_module, 'Listar');

        $this->_view->load('courses/index',[
            'title' => 'Cursos',
            'subject' => 'Lista de Cursos',
            'courses' => Course::with(['category','level','user','price','status'])->get(),
            'action' =>  'index',
            'route_create' => "courses/create",
            'button_create' => 'Nuevo Curso'
        ]);
    }

    public function create()
    {
        $this->validateSession();
        $this->validatePermission($this->_module, 'Crear');

        $this->_view->load('courses/create', [
            'title' => 'Cursos',
            'subject' => 'Nuevo Curso',
            'course' => Session::get('form_data') ?? [],
            'categories' => Category::select('id','name')->orderBy('name')->get(),
            'levels' => Level::select('id','name')->orderBy('name')->get(),
            'prices' => Price::select('id','name','value')->orderBy('name')->get(),
            'send'   => $this->encrypt($this->getForm()),
            'process' => "courses/store",
            'action' => 'create',
        ]);
    }

    public function store()
    {
        $this->validateSession();
        $this->validatePermission($this->_module, 'Crear');
        
        $data = [
            'title' => Filter::getPost('title'),
            'subtitle' => Filter::getPost('subtitle'),
            'description' => Filter::getPost('description'),
            'category' => Filter::getPost('category'),
            'level' => Filter::getPost('level'),
            'price' => Filter::getPost('price'),
            'image' => $_FILES['image']['name']
        ];

        $rules = [
            'title' => 'required|min:3|max:100|string',
            'subtitle' => 'required|min:3|max:100|string',
            'description' => 'required|min:3|string',
            'category' => 'required|numeric',
            'level' => 'required|numeric',
            'price' => 'required|numeric',
            'image' => 'image|mimes:jpg,png,jpeg|maxsize:5M'
        ];

        $this->validateForm('courses/create', $data, $rules);

        $exist = Course::where('title', $data['title'])->where('user_id', Session::get('user_id'))->exists();

        if ($exist) {
            Flash::error('El curso ya existe. Intente con otro');
            $this->redirect('courses/create');
        }

        // Procesar imagen (opcional)
        if ($_FILES['image']['name']) {

            $image = $_FILES['image']['name'];
            $tmp_name = $_FILES['image']['tmp_name'];
            $upload = ROOT . 'public' . DS . 'img' . DS . 'courses' . DS;
            $fichero = $upload . basename($_FILES['image']['name']);
        }

        $course = new Course();
        $course->title = Helper::getTitle($data['title']);
        $course->slug = Helper::friendlyRoute($data['title']);
        $course->subtitle = Helper::getSentence($data['subtitle']);
        $course->description = Helper::getSentence($data['description']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $fichero)) {
            $course->image = $image;
        }
        $course->status_id = 1; //en revision
        $course->category_id = (int) $data['category']; 
        $course->level_id = (int) $data['level']; 
        $course->price_id = (int) $data['price']; 
        $course->user_id = Session::get('user_id'); 
        $course->save();

        Flash::success('Curso creado correctamente.');
        $this->redirect('courses');
    }

    public function show($id = null)
    {
        $this->validateSession();
        $this->validatePermission($this->_module, 'Leer');
        $course = Validate::validateModel(Course::class, $id, 'courses');

        $this->_view->load('courses/show', [
            'title' => 'Cursos',
            'subject' => 'Detalle Curso',
            'course'  => $course->load('category','level','price','user','status'),
            'send'   => $this->encrypt($this->getForm()),
            'process' => "courses/delete",
        ]);
    }

    public function edit($id = null)
    {
        $this->validateSession();
        $this->validatePermission($this->_module, 'Editar');
        $course = Validate::validateModel(Course::class, $id, 'courses');

        $this->_view->load('courses/edit', [
            'title' => 'Cursos',
            'subject' => 'Editar Curso',
            'course'   => $course->load('category','level','price','user','status'),
            'statuses' => Status::select('id','name')->orderBy('name')->get(),
            'categories' => Category::select('id','name')->orderBy('name')->get(),
            'levels' => Level::select('id','name')->orderBy('name')->get(),
            'prices' => Price::select('id','name','value')->orderBy('name')->get(),
            'send'   => $this->encrypt($this->getForm()),
            'process' => "courses/update/{$id}",
            'action' => 'edit',
        ]);
    }

    public function update($id = null)
    {
        $this->validateSession();
        $this->validatePermission($this->_module, 'Crear');
        $this->validatePUT();
        $course = Validate::validateModel(Course::class, $id, 'courses');
        
        $data = [
            'title' => Filter::getPost('title'),
            'subtitle' => Filter::getPost('subtitle'),
            'description' => Filter::getPost('description'),
            'status' => Filter::getPost('status'),
            'category' => Filter::getPost('category'),
            'level' => Filter::getPost('level'),
            'price' => Filter::getPost('price'),
        ];

        $rules = [
            'title' => 'required|min:3|max:100|string',
            'subtitle' => 'required|min:3|max:100|string',
            'description' => 'required|min:3|string',
            'status' => 'required|numeric',
            'category' => 'required|numeric',
            'level' => 'required|numeric',
            'price' => 'required|numeric',
        ];

        $this->validateForm("courses/edit/$id", $data, $rules);

        $course = $course;
        $course->title = Helper::getTitle($data['title']);
        $course->slug = Helper::friendlyRoute($data['title']);
        $course->subtitle = Helper::getSentence($data['subtitle']);
        $course->description = Helper::getSentence($data['description']);
        $course->status_id = (int) $data['status'];
        $course->category_id = (int) $data['category']; 
        $course->level_id = (int) $data['level']; 
        $course->price_id = (int) $data['price']; 
        $course->save();

        Flash::success('Curso actualizado correctamente.');
        $this->redirect('courses/show/' . $id);
    }

    //metodos para vistas publicas

    public function courses()
    {
        $this->_view->load('courses/courses',[
            'title' => 'Todos Los Cursos',
            'categories' => Category::select('id','name','slug')->get(),
            'levels' => Level::select('id','name','slug')->get(),
            'courses' => Course::with(['category','level'])->where('status_id', 1)->latest('id')->get(),
            'send' => $this->encrypt($this->getForm()),
            'process' => "courses/getCourses",
            'module' => $this->encrypt('course')
        ] );
    }

    public function coursesCategory($category = null)
    {
        $slug = Filter::sanitizeSlug($category);
        $category_id = Category::where('slug', $slug)->value('id');

        //se crea variable de session para el buscador que esta en esta vista
        Session::set('category', $slug);
        
        $this->_view->load('courses/coursesCategory',[
            'title' => 'Cursos por Categoria',
            'categories' => Category::select('id','name','slug')->get(),
            'levels' => Level::select('id','name','slug')->get(),
            'courses' => Course::with(['category','level'])->where('category_id', $category_id)->where('status_id', 1)->latest('id')->get(),
            'send' => $this->encrypt($this->getForm()),
            'process' => "courses/getCourses",
            'module' => $this->encrypt('category')
        ] );
    }

    public function coursesLevel($level = null)
    {
        $slug = Filter::sanitizeSlug($level);
        $level_id = Level::where('slug', $slug)->value('id');

        //se crea variable de session para el buscador que esta en esta vista
        Session::set('level', $slug);
        
        $this->_view->load('courses/coursesLevel',[
            'title' => 'Cursos por Categoria',
            'categories' => Category::select('id','name','slug')->get(),
            'levels' => Level::select('id','name','slug')->get(),
            'courses' => Course::with(['category','level'])->where('level_id', $level_id)->where('status_id', 1)->latest('id')->get(),
            'send' => $this->encrypt($this->getForm()),
            'process' => "courses/getCourses",
            'module' => $this->encrypt('level')
        ] );
    }

    public function course($course = null)
    {
        $slug = Filter::sanitizeSlug($course);
        $exist = Course::select('id','category_id')->where('slug', $slug)->first();
        //Helper::debuger($exist);
        $course_user = CourseUser::where('user_id', 1)->where('course_id', $exist->id)->exists();

        if($course_user){
            $status = 'matriculado';
        }else{
            $status = 'no-matriculado';
        }

        $this->_view->load('courses/course',[
            'title' => 'Detalle del Curso',
            'course' => Course::with(['category','level','status'])->find((int) $exist->id),
            'similares' => Course::with(['category','level'])->where('status_id', 1)->where('category_id', $exist->category_id)->where('id', '!=', $exist->id)->latest('id')->limit(3)->get(),
            'send' => $this->encrypt($this->getForm()),
            'process' => "courses/enrolled",
            'status' => $status,
            'course_user' => CourseUser::select('id')->where('course_id', $exist->id)->count()
        ] );
    }

    public function enrolled()
    {
        $fields = [
            'course' => Filter::getPost('course'),
        ];
        
        $rules = [
            'course' => 'required|numeric',
        ];

        $course_id = Filter::getPost('course');
        $course = Course::find((int) $course_id);

        $this->validateForm('courses/course/'.$course->slug, $fields, $rules);

        $exist = CourseUser::where('user_id', 1)->where('course_id', $course_id)->exists();
        
        if($exist){
            Flash::info('Ya estas inscrito en este curso.');
            $this->redirect('courses/course/'.$course->slug);
        }

        $course_user = new CourseUser();
        $course_user->user_id = 1;
        $course_user->course_id = $course_id;
        $course_user->save();

        Flash::success('Te has inscrito correctamente en el curso.');
        $this->redirect('courses/course/'.$course->slug);
    }

    public function getCourses()
    {
        $search = Filter::getPost('search');
        $module = $this->decrypt(Filter::getPost('module'));
        $courses = Course::with(['category','level'])->where('title', 'LIKE', "%$search%")->where('status_id', 1)->get();
        
        //Helper::debuger($module);
        $route = match ($module) {
            'course' => 'courses/courses',
            'category' => 'courses/coursesCategory/' . Session::get('category'),
            'level' => 'courses/coursesLevel/' . Session::get('level'),
            'getCourses' => 'courses/getCourses',
            default => 'index/index',
        };
        
        if($courses->isEmpty()) {
            Flash::warning('El curso solicitado aún no está disponible. Solícitalo en la sección Contacto');
            $this->redirect($route);
        } 
        
        $this->_view->load('courses/getCourses',[
            'courses' => $courses,
            'categories' => Category::select('id','name','slug')->get(),
            'levels' => Level::select('id','name','slug')->get(),
            'title' => 'Cursos Seleccionados',
            'send' => $this->encrypt($this->getForm()),
            'process' => "courses/getCourses",
            'module' => $this->encrypt('getCourses')
        ]);
    }

    public function status($course = null)
    {
        $slug = Filter::sanitizeSlug($course);
        $exist = Course::select('id','category_id')->where('slug', $slug)->first();

        $this->_view->load('courses/status',[
            'title' => 'Detalle del Curso',
            'course' => Course::with(['category','level','status'])->find((int) $exist->id),
        ]);
    }

    //editar imagen del curso
    public function editImage($id = null)
    {
        $this->validateSession();
        $this->validatePermission($this->_module, 'Editar');
        $course = Validate::validateModel(Course::class, $id, 'courses');
        $this->_view->load('courses/editImage', [
            'title' => 'Cursos',
            'subject' => 'Editar Imagen Curso',
            'course' => $course->load('category','level','price','user','status'),
            'send' => $this->encrypt($this->getForm()),
            'process' => "courses/updateImage/$id",
            'action' => 'edit',
        ]);
    }

    public function updateImage($id = null)
    {
        $this->validateSession();
        $this->validatePermission($this->_module, 'Editar');
        $this->validatePUT();
        $course = Validate::validateModel(Course::class, $id, 'courses');

        $data = [
            'image' => $_FILES['image']['name'] ?? null
        ];

        $rules = [
            'image' => 'required|image|mimes:jpg,png,jpeg|maxsize:5M'
        ];

        $this->validateForm("courses/editImage/$id", $data, $rules);

        $image = $_FILES['image']['name'];
        $tmp_name = $_FILES['image']['tmp_name'];
        $upload = ROOT . 'public' . DS . 'img' . DS . 'courses' . DS;
        $fichero = $upload . basename($_FILES['image']['name']);

        if (move_uploaded_file($_FILES['image']['tmp_name'], $fichero)) {
            $course = $course;
            $course->image = $image;
            $course->save();

            Flash::success('La imagen se ha cargado correctamente');
        }else{
            Flash::error('La imagen no pudo cargarse correctamente... intente nuevamente');
            $this->redirect('courses/editImage/' . $id);
        }

        Flash::success('Imagen de curso actualizada correctamente.');
        $this->redirect('courses/show/' . $id);
    }

    public function delete()
    {
        $this->validateSession();
        $this->validatePermission($this->_module, 'Eliminar');
        $this->validateDelete();
        
        $data = [
            'course' => Filter::getPost('course'),
        ];
        
        $rules = [
            'course' => 'required|numeric',
        ];
        
        $this->validateForm("error/denied", $data, $rules);
        
        $course = Validate::validateModel(Course::class, $data['course'],'courses');
        $sections = Section::where('course_id', $course->id)->exists();

        if ($sections) {
            Flash::error('Hay secciones asociadas. No se puede eliminar');
            $this->redirect('courses');
        }

        $course->delete();
        
        Flash::success('Curso eliminado correctamente');
        $this->redirect('courses');
    }
}