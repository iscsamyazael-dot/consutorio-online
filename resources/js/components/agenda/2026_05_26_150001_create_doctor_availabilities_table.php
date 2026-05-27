<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('doctor_availabilities')) {

            Schema::create('doctor_availabilities', function (Blueprint $table) {

                $table->id();

                $table->foreignId('user_id')
                    ->constrained('users')
                    ->onDelete('cascade');

                $table->tinyInteger('dia_semana'); 
                $table->time('hora_inicio');
                $table->time('hora_fin');
                $table->boolean('activo')->default(true);

                $table->timestamps();

                // Nombre corto personalizado
                $table->unique(
                    ['user_id','dia_semana','hora_inicio','hora_fin'],
                    'doc_avail_uq'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_availabilities');
    }
};