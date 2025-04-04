<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('whatsapp_instances', function (Blueprint $table) {
            $table->id();
            $table->string('instance_name')->unique();
            $table->string('instance_key');
            $table->string('status')->default('disconnected');
            $table->json('connection_data')->nullable();
            $table->text('qrcode')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_instances');
    }
};