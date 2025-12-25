<?php
namespace controllers;

use application\Controller;
use application\Filter;
use application\Flash;
use application\Helper;
use application\Session;
use models\Course;
use models\Category;
use models\Level;
use models\CourseUser;

class CoursesController extends Controller
{
    private $_modulo;

    public function __construct(){
        parent::__construct();
    }

    public function index()
    {

        $this->_view->load('courses/index',[
            'titulo' => 'Cursos Disponibles',
            'courses' => Course::where('status_id', 3)->latest('id')->get()
        ]);
    }

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
        //Helper::debuger($_POST);
        $search = Filter::getPost('search');
        $module = $this->decrypt(Filter::getPost('module'));
        $courses = Course::with(['category','level'])->where('title', 'LIKE', "%$search%")->where('status_id', 1)->get();
        
        $route = match ($module) {
            'course' => 'courses/courses',
            'category' => 'courses/coursesCategory/' . Session::get('category'),
            'level' => 'courses/coursesLevel/' . Session::get('level'),
            'getCourses' => 'courses/getCourses',
            default => 'index/index',
        };
        
        if(!$courses) {
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
}