<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lotacoes', function (Blueprint $table) {
            $table->id('lot_id');
            $table->foreignId('unid_id')->constrained('unidades', 'unid_id')->onDelete('cascade');
            $table->date('lot_data_admissao');
            $table->date('lot_data_demissao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotacoes');
    }
};
