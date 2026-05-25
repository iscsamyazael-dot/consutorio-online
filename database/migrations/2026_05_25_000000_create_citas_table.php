<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('citas')) {
            Schema::create('citas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('fecha_hora');
                $table->string('duracion')->nullable();
                $table->string('tipo_cita')->nullable();
                $table->string('estado')->default('pendiente');
                $table->string('ubicacion')->nullable();
                $table->string('motivo')->nullable();
                $table->text('notas')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('citas');
    }
};
