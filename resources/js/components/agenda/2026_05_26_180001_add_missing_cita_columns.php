<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            if (!Schema::hasColumn('citas', 'motivo')) {
                $table->string('motivo')->nullable()->after('tipo_cita');
            }

            if (!Schema::hasColumn('citas', 'notas')) {
                $table->text('notas')->nullable()->after('ubicacion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            if (Schema::hasColumn('citas', 'motivo')) {
                $table->dropColumn('motivo');
            }

            if (Schema::hasColumn('citas', 'notas')) {
                $table->dropColumn('notas');
            }
        });
    }
};
