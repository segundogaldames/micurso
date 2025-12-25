<?php
// example of using model with eloquent
namespace models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'tasks';
    protected $fillable = [];

    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }
}
