<?php
// example of using model with eloquent
namespace models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $fillable = [];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
