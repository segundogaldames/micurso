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
            'categories' => Category::query()->get(['id','name','slug'])->toArray(),
            'levels' => Level::query()->get(['id','name','slug'])->toArray(),
            'courses' => Course::with(['category','level'])->where('status_id', 1)->latest('id')->get()->toArray()
        ] );
    }

    public function coursesCategory($category = null)
    {
        $slug = Filter::sanitizeSlug($category);
        $category_id = Category::where('slug', $slug)->value('id');
        
        $this->_view->load('courses/coursesCategory',[
            'title' => 'Cursos por Categoria',
            'categories' => Category::query()->get(['id','name','slug'])->toArray(),
            'levels' => Level::query()->get(['id','name','slug'])->toArray(),
            'courses' => Course::with(['category','level'])->where('category_id', $category_id)->where('status_id', 1)->latest('id')->get()->toArray()
        ] );
    }

    public function coursesLevel($level = null)
    {
        $slug = Filter::sanitizeSlug($level);
        $level_id = Level::where('slug', $slug)->value('id');
        
        $this->_view->load('courses/coursesLevel',[
            'title' => 'Cursos por Categoria',
            'categories' => Category::query()->get(['id','name','slug'])->toArray(),
            'levels' => Level::query()->get(['id','name','slug'])->toArray(),
            'courses' => Course::with(['category','level'])->where('level_id', $level_id)->where('status_id', 1)->latest('id')->get()->toArray()
        ] );
    }

    public function course($course = null)
    {
        $slug = Filter::sanitizeSlug($course);
        $exist = Course::select('id','category_id')->where('slug', $slug)->first();
        //Helper::debuger($exist);

        $this->_view->load('courses/course',[
            'title' => 'Detalle del Curso',
            'course' => Course::with(['category','level','status'])->find((int) $exist->id)->toArray(),
            'similares' => Course::with(['category','level'])->where('status_id', 1)->where('category_id', $exist->category_id)->where('id', '!=', $exist->id)->latest('id')->limit(3)->get()->toArray()
        ] );
    }
}