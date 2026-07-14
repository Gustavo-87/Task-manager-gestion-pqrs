<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pqrs', function (Blueprint $table) {
            $table->text('respuesta')->nullable()->after('descripcion');
            $table->timestamp('respondida_en')->nullable()->after('fecha_limite_respuesta');
            $table->foreignId('respondida_por')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pqrs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('respondida_por');
            $table->dropColumn(['respuesta', 'respondida_en']);
        });
    }
};
