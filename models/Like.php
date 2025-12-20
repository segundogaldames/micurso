<?php
// example of using model with eloquent
namespace models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $table = 'likes';
    protected $fillable = [];

    public function user() 
    {
        return $this->belongsTo(User::class);
    }
}
