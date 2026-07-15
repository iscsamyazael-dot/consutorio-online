<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('triage', function (Blueprint $table) {
            $table->id();
            $table->string('presion')->nullable();
            $table->string('saturacion')->nullable();
            $table->string('temperatura')->nullable();
            $table->text('sintomas')->nullable();
            $table->string('estado')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('triage');
    }
};