<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {

            $table->string('paciente_nombre')->after('id');

            $table->string('telefono')->nullable()->after('paciente_nombre');

        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {

            $table->dropColumn([
                'paciente_nombre',
                'telefono'
            ]);

        });
    }
};