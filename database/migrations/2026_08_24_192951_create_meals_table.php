<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner']);
            $table->enum('food_type', ['veg', 'non_veg']);
            $table->string('food_name');
            $table->enum('status', ['available', 'unavailable'])->default('available');
            $table->timestamps();

            $table->unique(['menu_id', 'meal_type', 'food_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meals');
    }
};
