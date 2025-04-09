<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Unidade extends Model
{
    protected $table = 'unidades';
    protected $primaryKey = 'unid_id';

    protected $fillable = [
        'unid_nome',
        'unid_sigla'
    ];

    public function enderecos(): BelongsToMany
    {
        return $this->belongsToMany(Endereco::class, 'unidade_endereco', 'unid_id', 'end_id')
            ->withTimestamps();
    }

    public function lotacoes(): HasMany
    {
        return $this->hasMany(Lotacao::class, 'unid_id');
    }
}
