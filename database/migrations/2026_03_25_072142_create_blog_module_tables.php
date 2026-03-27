<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Blog Categories
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->timestamps();
        });

        // 2. Blog Category Translations
        Schema::create('blog_category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('blog_categories')->onDelete('cascade');
            $table->string('lang', 5);
            $table->string('title');
            $table->unique(['category_id', 'lang']);
            $table->timestamps();
        });

        // 3. Blogs
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('image')->nullable();
            $table->string('category_code')->index(); // references blog_categories.code virtually
            $table->string('writer')->nullable();
            $table->integer('views')->default(0);
            $table->string('status')->default('draft'); // draft, published, archived
            $table->timestamp('published_at')->nullable();
            $table->timestamp('notification_sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Blog Translations
        Schema::create('blog_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained('blogs')->onDelete('cascade');
            $table->string('lang', 5);
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->unique(['blog_id', 'lang']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_translations');
        Schema::dropIfExists('blogs');
        Schema::dropIfExists('blog_category_translations');
        Schema::dropIfExists('blog_categories');
    }
};
