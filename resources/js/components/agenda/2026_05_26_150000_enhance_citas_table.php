<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            // Color para FullCalendar (para diferenciar estados visualmente)
            if (!Schema::hasColumn('citas', 'color')) {
                $table->string('color')->nullable()->default('#3b82f6')->after('estado');
            }

            // Confirmación del paciente
            if (!Schema::hasColumn('citas', 'confirmada_paciente')) {
                $table->boolean('confirmada_paciente')->default(false)->after('color');
            }

            // Recordatorio enviado
            if (!Schema::hasColumn('citas', 'recordatorio_enviado')) {
                $table->boolean('recordatorio_enviado')->default(false)->after('confirmada_paciente');
            }

            // Razón de cancelación (si la hay)
            if (!Schema::hasColumn('citas', 'razon_cancelacion')) {
                $table->text('razon_cancelacion')->nullable()->after('recordatorio_enviado');
            }

            // Fecha de cancelación
            if (!Schema::hasColumn('citas', 'cancelada_en')) {
                $table->timestamp('cancelada_en')->nullable()->after('razon_cancelacion');
            }

            // Duración en minutos (mejor que string)
            if (!Schema::hasColumn('citas', 'duracion_minutos')) {
                $table->integer('duracion_minutos')->default(30)->after('cancelada_en');
            }
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropColumn([
                'color',
                'confirmada_paciente',
                'recordatorio_enviado',
                'razon_cancelacion',
                'cancelada_en',
                'duracion_minutos',
            ]);
        });
    }
};
