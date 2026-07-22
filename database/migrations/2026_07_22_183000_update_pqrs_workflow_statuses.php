<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("
            ALTER TABLE pqrs
            MODIFY estado ENUM(
                'radicada',
                'en_revision',
                'respondida',
                'en_proceso',
                'en_espera',
                'rechazada',
                'resuelta',
                'cerrada'
            ) NOT NULL DEFAULT 'radicada'
        ");
        DB::statement("UPDATE pqrs SET estado = 'resuelta' WHERE estado = 'respondida'");
        DB::statement("
            ALTER TABLE pqrs
            MODIFY estado ENUM(
                'radicada',
                'en_revision',
                'en_proceso',
                'en_espera',
                'rechazada',
                'resuelta',
                'cerrada'
            ) NOT NULL DEFAULT 'radicada'
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("
            ALTER TABLE pqrs
            MODIFY estado ENUM(
                'radicada',
                'en_revision',
                'respondida',
                'en_proceso',
                'en_espera',
                'rechazada',
                'resuelta',
                'cerrada'
            ) NOT NULL DEFAULT 'radicada'
        ");
        DB::statement("UPDATE pqrs SET estado = 'respondida' WHERE estado = 'resuelta'");
        DB::statement("UPDATE pqrs SET estado = 'en_revision' WHERE estado IN ('en_proceso', 'en_espera', 'rechazada')");
        DB::statement("
            ALTER TABLE pqrs
            MODIFY estado ENUM(
                'radicada',
                'en_revision',
                'respondida',
                'cerrada'
            ) NOT NULL DEFAULT 'radicada'
        ");
    }
};
