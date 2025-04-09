<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servidores_efetivos', function (Blueprint $table) {
            $table->id('pes_id');
            $table->string('pes_nome');
            $table->date('pes_data_nascimento');
            $table->string('pes_foto')->nullable();
            $table->foreignId('lot_id')->constrained('lotacoes', 'lot_id')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servidores_efetivos');
    }
};
