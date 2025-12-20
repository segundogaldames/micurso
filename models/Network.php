<?php
// example of using model with eloquent
namespace models;

use Illuminate\Database\Eloquent\Model;

class Network extends Model
{
    protected $table = 'networks';
    protected $fillable = [];

    public function networkType()
    {
        return $this->belongsTo(NetworkType::class);
    }
}
