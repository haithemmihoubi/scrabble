<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partie extends Model
{
    public function joueurs()
    {
        return $this->hasMany(Joueur::class,"partie");
    }





    use HasFactory;
    protected $fillable=["typePartie"] ;
    public $timestamps = false;
}
