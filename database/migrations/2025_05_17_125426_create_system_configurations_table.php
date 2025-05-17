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
        Schema::create('system_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('Configuration key');
            $table->text('value')->nullable()->comment('Configuration value');
            $table->string('type')->default('string')->comment('Data type: string, integer, boolean, json, etc.');
            $table->string('group')->default('general')->comment('Group for organization: general, appearance, contact, etc.');
            $table->boolean('is_public')->default(false)->comment('Whether this config is publicly accessible');
            $table->string('display_name')->nullable()->comment('Human-readable name for admin UI');
            $table->text('description')->nullable()->comment('Description of this configuration item');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_configurations');
    }
};
