<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServidorEfetivo extends Model
{
    protected $table = 'servidor_efetivo';
    protected $primaryKey = 'pes_id';
    public $incrementing = false;

    protected $fillable = [
        'pes_id',
        'se_matricula'
    ];

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'pes_id');
    }
}
