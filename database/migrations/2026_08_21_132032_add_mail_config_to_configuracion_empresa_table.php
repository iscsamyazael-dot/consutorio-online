<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion_empresa', function (Blueprint $table) {
            $table->string('mail_host')->nullable();
            $table->string('mail_port')->nullable();
            $table->string('mail_username')->nullable();
            $table->text('mail_password')->nullable();
            $table->string('mail_encryption')->nullable()->default('tls');
            $table->boolean('mail_configurado')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('configuracion_empresa', function (Blueprint $table) {
            $table->dropColumn([
                'mail_host', 'mail_port', 'mail_username', 'mail_password',
                'mail_encryption', 'mail_configurado',
            ]);
        });
    }
};