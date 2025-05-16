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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('url')->nullable();
            $table->string('route_name')->nullable();
            $table->string('icon')->nullable();
            $table->integer('order')->default(0);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('menu_items')->onDelete('cascade');
            $table->text('permissions')->nullable()->comment('JSON array of permission names required to see this menu item');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_external')->default(false);
            $table->string('target')->default('_self'); // _self, _blank, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
