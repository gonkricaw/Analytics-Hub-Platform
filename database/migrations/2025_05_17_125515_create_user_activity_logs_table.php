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
        Schema::create('user_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('activity_type')->comment('Login, Logout, View, Create, Update, Delete, etc.');
            $table->text('activity_details')->nullable()->comment('Additional details about the activity');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device')->nullable()->comment('Desktop, Mobile, Tablet, etc.');
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->string('related_model_type')->nullable()->comment('Model type if activity is related to a model');
            $table->unsignedBigInteger('related_model_id')->nullable()->comment('Model ID if activity is related to a model');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activity_logs');
    }
};
