<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Procedure Categories
        Schema::create('procedure_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->timestamps();
        });

        // 2. Procedure Category Translations
        Schema::create('procedure_categories_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('procedure_categories')->onDelete('cascade');
            $table->string('lang', 5);
            $table->string('title');
            $table->unique(['category_id', 'lang']);
            $table->timestamps();
        });

        // 3. Procedures
        Schema::create('procedures', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('image')->nullable();
            $table->foreignId('category_code')->index();
            $table->string('status')->default('draft'); // draft, published, archived
            $table->integer('views')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Procedure Translations
        Schema::create('procedure_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_id')->constrained('procedures')->onDelete('cascade');
            $table->string('lang', 5);
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->unique(['procedure_id', 'lang']);
            $table->timestamps();
        });

        // 5. Procedure Sections
        Schema::create('procedure_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_id')->constrained('procedures')->onDelete('cascade');
            $table->string('type'); // what_is, technique, recovery
            $table->string('image')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 6. Procedure Section Translations
        Schema::create('procedure_section_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_section_id')->constrained('procedure_sections')->onDelete('cascade');
            $table->string('lang', 5);
            $table->string('title')->nullable();
            $table->longText('content_one')->nullable();
            $table->longText('content_two')->nullable();
            $table->unique(['procedure_section_id', 'lang']);
            $table->timestamps();
        });

        // 7. Procedure FAQs
        Schema::create('procedure_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_id')->constrained('procedures')->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 8. Procedure FAQ Translations
        Schema::create('procedure_faq_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_faq_id')->constrained('procedure_faqs')->onDelete('cascade');
            $table->string('lang', 5);
            $table->string('question');
            $table->longText('answer');
            $table->unique(['procedure_faq_id', 'lang']);
            $table->timestamps();
        });

        // 9. Procedure Postoperative Instructions
        Schema::create('procedure_postoperative_instructions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_id')->constrained('procedures')->onDelete('cascade');
            $table->string('type'); // do, dont
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 10. Procedure Postoperative Instruction Translations
        Schema::create('procedure_postoperative_instruction_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_postoperative_instruction_id')->constrained('procedure_postoperative_instructions', indexName: 'postop_instr_id_trans_foreign')->onDelete('cascade');
            $table->string('lang', 5);
            $table->longText('content');
            $table->unique(['procedure_postoperative_instruction_id', 'lang'], 'postop_instr_lang_unique');
            $table->timestamps();
        });

        // 11. Procedure Preparation Steps
        Schema::create('procedure_preparation_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_id')->constrained('procedures')->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 12. Procedure Preparation Step Translations
        Schema::create('procedure_preparation_step_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_preparation_step_id')->constrained('procedure_preparation_steps', indexName: 'prep_step_id_trans_foreign')->onDelete('cascade');
            $table->string('lang', 5);
            $table->string('title');
            $table->longText('description')->nullable();
            $table->unique(['procedure_preparation_step_id', 'lang'], 'prep_step_lang_unique');
            $table->timestamps();
        });

        // 13. Procedure Recovery Phases
        Schema::create('procedure_recovery_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_id')->constrained('procedures')->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 14. Procedure Recovery Phase Translations
        Schema::create('procedure_recovery_phase_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_recovery_phase_id')->constrained('procedure_recovery_phases', indexName: 'recov_phase_id_trans_foreign')->onDelete('cascade');
            $table->string('lang', 5);
            $table->string('period')->nullable();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->unique(['procedure_recovery_phase_id', 'lang'], 'recov_phase_lang_unique');
            $table->timestamps();
        });

        // 15. Procedure Results Gallery (Before/After)
        Schema::create('procedure_result_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_id')->constrained('procedures')->onDelete('cascade');
            $table->string('path');
            $table->string('type'); // before, after
            $table->unsignedBigInteger('pair_id')->nullable()->index(); // identifies the pair for gallery display
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedure_result_galleries');
        Schema::dropIfExists('procedure_recovery_phase_translations');
        Schema::dropIfExists('procedure_recovery_phases');
        Schema::dropIfExists('procedure_preparation_step_translations');
        Schema::dropIfExists('procedure_preparation_steps');
        Schema::dropIfExists('procedure_postoperative_instruction_translations');
        Schema::dropIfExists('procedure_postoperative_instructions');
        Schema::dropIfExists('procedure_faq_translations');
        Schema::dropIfExists('procedure_faqs');
        Schema::dropIfExists('procedure_section_translations');
        Schema::dropIfExists('procedure_sections');
        Schema::dropIfExists('procedure_translations');
        Schema::dropIfExists('procedures');
        Schema::dropIfExists('procedure_categories_translations');
        Schema::dropIfExists('procedure_categories');
    }
};
