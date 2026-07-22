<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pqr_histories')) {
            return;
        }

        Schema::create('pqr_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pqr_id')->constrained('pqrs')->onDelete('cascade');
            $table->string('campo');
            $table->text('valor_anterior')->nullable();
            $table->text('valor_nuevo')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // The table may predate this migration because of a corrected legacy
        // migration, so it must not be dropped during a rollback.
    }
};
