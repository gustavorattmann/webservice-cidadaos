<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cidadao extends Model
{
    use HasFactory;

    public function endereco()
    {
        return $this->hasOne(Endereco::class, 'id_cidadao');
    }
}
