<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servidores_temporarios', function (Blueprint $table) {
            $table->id('st_data_id');
            $table->string('st_nome');
            $table->date('st_data_admissao');
            $table->date('st_data_demissao')->nullable();
            $table->foreignId('lot_id')->constrained('lotacoes', 'lot_id')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servidores_temporarios');
    }
};
