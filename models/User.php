<?php
// example of using model with eloquent
namespace models;

use Dom\Comment;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = [];

    //pertenece a 
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    
    //uno a uno
    public function imageRelation()
    {
        return $this->hasOne(Image::class);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    //uno a muchos
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function courseUsers()
    {
        return $this->hasMany(CourseUser::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}