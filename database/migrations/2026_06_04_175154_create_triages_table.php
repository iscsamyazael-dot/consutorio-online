<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('triages', function (Blueprint $table) {
            $table->id();

            // Relación con consultas
            $table->foreignId('consulta_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('presion');
            $table->string('saturacion');
            $table->decimal('temperatura', 4, 1);

            $table->text('sintomas')->nullable();

            $table->enum('estado', [
                'leve',
                'estable',
                'grave',
                'urgente'
            ]);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('triages');
    }
};