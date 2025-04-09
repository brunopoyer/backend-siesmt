<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cidade extends Model
{
    protected $table = 'cidades';
    protected $primaryKey = 'cid_id';

    protected $fillable = [
        'cid_nome',
        'cid_uf'
    ];

    public function enderecos(): HasMany
    {
        return $this->hasMany(Endereco::class, 'cid_id');
    }
}
