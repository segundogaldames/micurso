<?php
// example of using model with eloquent
namespace models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $table = 'lessons';
    protected $fillable = [];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function description()
    {
        return $this->hasOne(Description::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }
}
