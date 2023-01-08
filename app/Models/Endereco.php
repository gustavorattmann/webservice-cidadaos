<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Endereco extends Model
{
    use HasFactory;

    public function cidadao()
    {
        return $this->belongsTo(Cidadao::class, 'id_cidadao', 'id');
    }
}
