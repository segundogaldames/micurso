<?php
// example of using model with eloquent
namespace models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';
    protected $fillable = [];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
