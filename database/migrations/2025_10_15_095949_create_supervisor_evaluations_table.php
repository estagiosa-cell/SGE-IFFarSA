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
        Schema::create('supervisor_evaluations', function (Blueprint $table) {
            $table->id();

            // Dados do formulário
            $table->string('supervisor_email')->nullable();
            $table->string('student_name')->nullable();
            $table->string('supervisor_name')->nullable();

            // Perguntas sim/não e descritivas
            $table->string('has_academic_background')->nullable();
            $table->string('completed_workload')->nullable();
            $table->string('training_course')->nullable();
            $table->string('education_level')->nullable();
            $table->string('job_role')->nullable();
            $table->string('experience_time')->nullable();

            // Avaliações (Ótimo, Muito Bom, Bom, Satisfatório, Insatisfatório)
            $table->string('performance')->nullable();
            $table->string('comprehension')->nullable();
            $table->string('technical_knowledge')->nullable();
            $table->string('organization')->nullable();
            $table->string('initiative')->nullable();
            $table->string('attendance')->nullable();
            $table->string('discipline')->nullable();
            $table->string('sociability')->nullable();
            $table->string('cooperation')->nullable();
            $table->string('responsibility')->nullable();

            // Campos de texto livre
            $table->text('considerations')->nullable();
            $table->text('suggestions_to_institution')->nullable();
            $table->text('performance_issues')->nullable();
            $table->text('other_observations')->nullable();

            // Soft delete para "limpar" após associação
            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supervisor_evaluations');
    }
};
