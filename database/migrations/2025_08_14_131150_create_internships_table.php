// Substitua o conteúdo do seu arquivo de migration create_internships_table.php por este:

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
        Schema::create('internships', function (Blueprint $table) {
            $table->id();

            // --- Chaves Estrangeiras Essenciais ---
            $table->foreignId('advisor_id')->constrained('users'); // Orientador
            $table->foreignId('course_id')->constrained('courses');
            $table->foreignId('internship_type_id')->constrained('internship_types');

            // --- Dados do Aluno ---
            $table->string('student_name');
            $table->string('student_email');
            $table->string('student_registration_number');
            $table->string('student_year_semester');
            $table->date('student_birth_date');
            $table->boolean('student_is_adult');
            $table->string('student_rg');
            $table->string('student_rg_issuer');
            $table->date('student_rg_issue_date');
            $table->string('student_cpf');
            $table->string('student_phone');
            $table->string('student_address_street');
            $table->string('student_address_number');
            $table->string('student_address_neighborhood');
            $table->string('student_address_city');
            $table->string('student_address_state', 2);
            $table->string('student_address_zip');

            // --- Dados da Concedente (Empresa/Pessoa Física) ---
            $table->string('company_legal_identifier_type'); // 'CPF' ou 'CNPJ'
            $table->string('company_legal_identifier');
            $table->string('company_name')->nullable(); // Nome ou Razão Social
            $table->string('company_phone')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_address_street')->nullable();
            $table->string('company_address_number')->nullable();
            $table->string('company_address_neighborhood')->nullable();
            $table->string('company_address_city')->nullable();
            $table->string('company_address_state', 2)->nullable();
            $table->string('company_address_zip')->nullable();
            $table->string('company_representative_name')->nullable();
            $table->string('company_representative_role')->nullable();
            $table->string('internship_sector')->nullable(); // Setor ou área onde será desenvolvido o estágio

            // --- Dados do Responsável Legal (se o aluno for menor) ---
            $table->string('legal_guardian_name')->nullable();
            $table->string('legal_guardian_cpf')->nullable();
            $table->string('legal_guardian_kinship')->nullable(); // Grau de Parentesco
            $table->string('legal_guardian_email')->nullable();

            // --- Dados do Supervisor na Concedente ---
            $table->string('supervisor_name');
            $table->string('supervisor_phone');
            $table->string('supervisor_email');
            $table->string('supervisor_role')->nullable();
            $table->string('supervisor_qualification')->nullable(); // O supervisor possui: Ensino Superior, Técnico, etc.
            $table->string('supervisor_training')->nullable(); // Formação do Supervisor
            $table->text('supervisor_experience')->nullable(); // Experiência profissional do supervisor

            // --- Dados do Estágio ---
            $table->text('activities');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('Pendente');
            $table->text('notes')->nullable(); // Observações específicas do estágio

            // Carga Horária
            $table->unsignedTinyInteger('hours_sunday')->nullable();
            $table->unsignedTinyInteger('hours_monday')->nullable();
            $table->unsignedTinyInteger('hours_tuesday')->nullable();
            $table->unsignedTinyInteger('hours_wednesday')->nullable();
            $table->unsignedTinyInteger('hours_thursday')->nullable();
            $table->unsignedTinyInteger('hours_friday')->nullable();
            $table->unsignedTinyInteger('hours_saturday')->nullable();

            // Remuneração
            $table->boolean('is_remunerated')->default(false);
            $table->decimal('grant_value', 8, 2)->nullable();
            $table->decimal('transportation_allowance', 8, 2)->nullable();


            $table->string('google_docs_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internships');
    }
};
