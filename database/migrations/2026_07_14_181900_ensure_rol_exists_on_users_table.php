<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'rol')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('rol')->default('usuario')->after('password');
            });
        }
    }

    public function down(): void
    {
        // This repair migration does not own the column on fresh databases.
    }
};
