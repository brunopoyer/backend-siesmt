<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Endereco extends Model
{
    protected $table = 'enderecos';
    protected $primaryKey = 'end_id';

    protected $fillable = [
        'end_tipo_logradouro',
        'end_logradouro',
        'end_numero',
        'end_bairro',
        'cid_id'
    ];

    public function cidade(): BelongsTo
    {
        return $this->belongsTo(Cidade::class, 'cid_id');
    }

    public function pessoas(): BelongsToMany
    {
        return $this->belongsToMany(Pessoa::class, 'pessoa_endereco', 'end_id', 'pes_id')
            ->withTimestamps();
    }

    public function unidades(): BelongsToMany
    {
        return $this->belongsToMany(Unidade::class, 'unidade_endereco', 'end_id', 'unid_id')
            ->withTimestamps();
    }
}
