<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            if (!Schema::hasColumn('citas', 'created_at')) {
                $table->timestamps();
            } elseif (!Schema::hasColumn('citas', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            if (Schema::hasColumn('citas', 'created_at') && Schema::hasColumn('citas', 'updated_at')) {
                $table->dropTimestamps();
            } elseif (Schema::hasColumn('citas', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }
};
