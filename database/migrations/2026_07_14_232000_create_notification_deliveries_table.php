<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pqr_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('recipient');
            $table->date('notification_date');
            $table->timestamps();
            $table->unique(['pqr_id', 'type', 'recipient', 'notification_date'], 'notification_delivery_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
    }
};
