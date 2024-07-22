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
        Schema::create('admin_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('function');
            $table->string('type')->nullable();
            $table->string('trigger_en');
            $table->string('trigger_bg');
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_configs');
    }
};
