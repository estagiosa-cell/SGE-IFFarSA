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
        Schema::table('internships', function (Blueprint $table) {
            // Dados da avaliação do supervisor
            $table->string('evaluation_has_academic_background')->nullable();
            $table->string('evaluation_completed_workload')->nullable();
            $table->string('evaluation_training_course')->nullable();
            $table->string('evaluation_education_level')->nullable();
            $table->string('evaluation_job_role')->nullable();
            $table->string('evaluation_experience_time')->nullable();

            // Avaliações (Ótimo, Muito Bom, Bom, Satisfatório, Insatisfatório)
            $table->string('evaluation_performance')->nullable();
            $table->string('evaluation_comprehension')->nullable();
            $table->string('evaluation_technical_knowledge')->nullable();
            $table->string('evaluation_organization')->nullable();
            $table->string('evaluation_initiative')->nullable();
            $table->string('evaluation_attendance')->nullable();
            $table->string('evaluation_discipline')->nullable();
            $table->string('evaluation_sociability')->nullable();
            $table->string('evaluation_cooperation')->nullable();
            $table->string('evaluation_responsibility')->nullable();

            // Campos de texto livre
            $table->text('evaluation_considerations')->nullable();
            $table->text('evaluation_suggestions_to_institution')->nullable();
            $table->text('evaluation_performance_issues')->nullable();
            $table->text('evaluation_other_observations')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internships', function (Blueprint $table) {
            $table->dropColumn([
                'evaluation_submitted_at',
                'evaluation_supervisor_email',
                'evaluation_supervisor_name',
                'evaluation_has_academic_background',
                'evaluation_completed_workload',
                'evaluation_training_course',
                'evaluation_education_level',
                'evaluation_job_role',
                'evaluation_experience_time',
                'evaluation_performance',
                'evaluation_comprehension',
                'evaluation_technical_knowledge',
                'evaluation_organization',
                'evaluation_initiative',
                'evaluation_attendance',
                'evaluation_discipline',
                'evaluation_sociability',
                'evaluation_cooperation',
                'evaluation_responsibility',
                'evaluation_considerations',
                'evaluation_suggestions_to_institution',
                'evaluation_performance_issues',
                'evaluation_other_observations',
            ]);
        });
    }
};
