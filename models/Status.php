<?php
// example of using model with eloquent
namespace models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $table = 'statuses';
    protected $fillable = [];

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
