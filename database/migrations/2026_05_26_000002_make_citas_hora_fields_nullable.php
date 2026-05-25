<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('citas')) {
            Schema::table('citas', function (Blueprint $table) {
                if (Schema::hasColumn('citas', 'hora_inicio')) {
                    $table->time('hora_inicio')->nullable()->change();
                }
                if (Schema::hasColumn('citas', 'hora_fin')) {
                    $table->time('hora_fin')->nullable()->change();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('citas')) {
            Schema::table('citas', function (Blueprint $table) {
                if (Schema::hasColumn('citas', 'hora_inicio')) {
                    $table->time('hora_inicio')->nullable(false)->change();
                }
                if (Schema::hasColumn('citas', 'hora_fin')) {
                    $table->time('hora_fin')->nullable(false)->change();
                }
            });
        }
    }
};
