<?php
// example of using model with eloquent
namespace models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $table = 'resources';
    protected $fillable = [];

    public function user() 
    {
        return $this->belongsTo(User::class);
    }
}
