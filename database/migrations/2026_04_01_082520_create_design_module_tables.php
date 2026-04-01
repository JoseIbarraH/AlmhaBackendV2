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
        // 1. Designs (Sections)
        Schema::create('designs', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('display_mode')->default('single_image'); // single_image, carousel, video, etc
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
        });

        // 2. Design Items (Media inside sections)
        Schema::create('design_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('design_id')->constrained('designs')->onDelete('cascade');
            $table->string('media_type')->default('image'); // image, video
            $table->string('media_path')->nullable();
            $table->integer('order')->default(0);
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
        });

        // 3. Design Item Translations
        Schema::create('design_item_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('design_item_id')->constrained('design_items')->onDelete('cascade');
            $table->string('lang', 5);
            $table->string('title')->nullable();
            $table->text('subtitle')->nullable();
            $table->unique(['design_item_id', 'lang']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_item_translations');
        Schema::dropIfExists('design_items');
        Schema::dropIfExists('designs');
    }
};
