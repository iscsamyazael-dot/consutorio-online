<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('citas') && Schema::hasColumn('citas', 'fecha')) {
            Schema::table('citas', function (Blueprint $table) {
                $table->date('fecha')->nullable()->change();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('citas') && Schema::hasColumn('citas', 'fecha')) {
            Schema::table('citas', function (Blueprint $table) {
                $table->date('fecha')->nullable(false)->change();
            });
        }
    }
};
