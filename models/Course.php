<?php
// example of using model with eloquent
namespace models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $table = 'courses';
    protected $fillable = [];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function price()
    {
        return $this->belongsTo(Price::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function audiences()
    {
        return $this->hasMany(Audience::class);
    }   

    public function courseUsers()
    {
        return $this->hasMany(CourseUser::class);
    }

    public function goals()
    {
        return $this->hasMany(Goal::class);
    }

    public function requirements()
    {
        return $this->hasMany(Requirement::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }
}
