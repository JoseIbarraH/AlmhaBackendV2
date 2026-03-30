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
        // 1. Teams (Global)
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->nullable()->index();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('status')->default('active'); // active, inactive
            $table->string('image')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // 2. Team Translations (Multi-language)
        Schema::create('team_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->string('lang', 5);
            $table->string('specialization')->nullable();
            $table->text('description')->nullable();
            $table->text('biography')->nullable();
            $table->unique(['team_id', 'lang']);
            $table->timestamps();
        });

        // 3. Team Gallery Images
        Schema::create('team_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->string('path');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 4. Team Gallery Image Translations
        Schema::create('team_image_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_image_id')->constrained('team_images')->onDelete('cascade');
            $table->string('lang', 5);
            $table->string('description')->nullable();
            $table->unique(['team_image_id', 'lang']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_image_translations');
        Schema::dropIfExists('team_images');
        Schema::dropIfExists('team_translations');
        Schema::dropIfExists('teams');
    }
};
