<?php
// example of using model with eloquent
namespace models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'comments';
    protected $fillable = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
