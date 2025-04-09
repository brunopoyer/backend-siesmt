<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pessoa extends Model
{
    protected $table = 'pessoas';
    protected $primaryKey = 'pes_id';

    protected $fillable = [
        'pes_nome',
        'pes_data_nascimento',
        'pes_sexo',
        'pes_mae',
        'pes_pai'
    ];

    protected $casts = [
        'pes_data_nascimento' => 'date'
    ];

    public function fotos(): HasMany
    {
        return $this->hasMany(FotoPessoa::class, 'pes_id');
    }

    public function enderecos(): BelongsToMany
    {
        return $this->belongsToMany(Endereco::class, 'pessoa_endereco', 'pes_id', 'end_id')
            ->withTimestamps();
    }

    public function servidorEfetivo(): HasOne
    {
        return $this->hasOne(ServidorEfetivo::class, 'pes_id');
    }

    public function servidorTemporario(): HasOne
    {
        return $this->hasOne(ServidorTemporario::class, 'pes_id');
    }

    public function lotacoes(): HasMany
    {
        return $this->hasMany(Lotacao::class, 'pes_id');
    }
}
