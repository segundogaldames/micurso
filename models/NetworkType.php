<?php
// example of using model with eloquent
namespace models;

use Illuminate\Database\Eloquent\Model;

class NetworkType extends Model
{
    protected $table = 'network_types';
    protected $fillable = [];

    public function networks()
    {
        return $this->hasMany(Network::class);
    }
}
