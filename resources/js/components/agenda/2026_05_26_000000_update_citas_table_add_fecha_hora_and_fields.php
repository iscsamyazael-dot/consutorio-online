<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('citas')) {
            return;
        }

        Schema::table('citas', function (Blueprint $table) {
            if (! Schema::hasColumn('citas', 'fecha_hora')) {
                $table->dateTime('fecha_hora')->nullable();
            }
            if (! Schema::hasColumn('citas', 'duracion')) {
                $table->string('duracion')->nullable()->after('fecha_hora');
            }
            if (! Schema::hasColumn('citas', 'tipo_cita')) {
                $table->string('tipo_cita')->nullable()->after('duracion');
            }
            if (! Schema::hasColumn('citas', 'ubicacion')) {
                $table->string('ubicacion')->nullable()->after('estado');
            }
        });

        if (Schema::hasColumn('citas', 'fecha') && Schema::hasColumn('citas', 'hora_inicio')) {
            DB::table('citas')
                ->whereNotNull('fecha')
                ->whereNotNull('hora_inicio')
                ->update([
                    'fecha_hora' => DB::raw("CONCAT(fecha, ' ', hora_inicio)"),
                ]);
        }
    }

    public function down()
    {
        if (! Schema::hasTable('citas')) {
            return;
        }

        Schema::table('citas', function (Blueprint $table) {
            if (Schema::hasColumn('citas', 'fecha_hora')) {
                $table->dropColumn('fecha_hora');
            }
            if (Schema::hasColumn('citas', 'duracion')) {
                $table->dropColumn('duracion');
            }
            if (Schema::hasColumn('citas', 'tipo_cita')) {
                $table->dropColumn('tipo_cita');
            }
            if (Schema::hasColumn('citas', 'ubicacion')) {
                $table->dropColumn('ubicacion');
            }
        });
    }
};
