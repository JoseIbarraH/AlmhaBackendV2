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
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable()->index(); // UUID o ID del usuario
            $table->string('action')->nullable();           // Descripción de la acción
            $table->string('method');                       // POST, PUT, DELETE, etc.
            $table->text('url');                            // URL de la petición
            $table->json('payload')->nullable();            // Cuerpo de la petición (filtrado)
            $table->integer('response_status')->nullable(); // Status HTTP de respuesta
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
