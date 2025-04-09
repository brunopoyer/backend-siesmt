<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lotacao extends Model
{
    protected $table = 'lotacao';
    protected $primaryKey = 'lot_id';

    protected $fillable = [
        'pes_id',
        'unid_id',
        'lot_data_lotacao',
        'lot_data_remocao',
        'lot_portaria'
    ];

    protected $casts = [
        'lot_data_lotacao' => 'date',
        'lot_data_remocao' => 'date'
    ];

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'pes_id');
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class, 'unid_id');
    }

    public function servidoresEfetivos(): HasMany
    {
        return $this->hasMany(ServidorEfetivo::class, 'lot_id');
    }

    public function servidoresTemporarios(): HasMany
    {
        return $this->hasMany(ServidorTemporario::class, 'lot_id');
    }
}
