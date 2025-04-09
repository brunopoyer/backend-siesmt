<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidade_endereco', function (Blueprint $table) {
            $table->foreignId('unid_id')->constrained('unidades', 'unid_id')->onDelete('cascade');
            $table->foreignId('end_id')->constrained('enderecos', 'end_id')->onDelete('cascade');
            $table->primary(['unid_id', 'end_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidade_endereco');
    }
};
