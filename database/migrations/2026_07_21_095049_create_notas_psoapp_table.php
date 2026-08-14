<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas_psoapp', function (Blueprint $table) {
            $table->id();

            // Relación con la evaluación de IA y la consulta que la generó.
            // Se usan los mismos identificadores que ya manejas en el resto
            // del sistema (consulta_id, consulta_folio, session_uuid).
            $table->foreignId('evaluacion_ia_id')
                ->nullable()
                ->constrained('evaluaciones_ia')
                ->nullOnDelete();

            $table->unsignedBigInteger('consulta_id')->nullable()->index();
            $table->string('consulta_folio')->nullable()->index();
            $table->uuid('session_uuid')->nullable()->index();

            // Los 6 apartados del formato PSOAPP.
            $table->text('presentacion')->nullable();
            $table->text('subjetivo')->nullable();
            $table->text('objetivo')->nullable();
            $table->text('analisis')->nullable();
            $table->text('plan')->nullable();
            $table->text('pronostico')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas_psoapp');
    }
};