<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FotoPessoa extends Model
{
    protected $table = 'fotos_pessoa';
    protected $primaryKey = 'fp_id';

    protected $fillable = [
        'pes_id',
        'fp_data',
        'fp_bucket',
        'fp_hash'
    ];

    protected $casts = [
        'fp_data' => 'date'
    ];

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'pes_id');
    }
}
