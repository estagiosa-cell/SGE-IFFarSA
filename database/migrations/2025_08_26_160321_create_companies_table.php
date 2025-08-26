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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // Identificação
            $table->string('legal_identifier'); // CPF ou CNPJ
            $table->string('name'); // Nome / Razão Social

            // Endereço
            $table->string('address_street')->nullable();
            $table->string('address_number')->nullable();
            $table->string('address_neighborhood')->nullable();
            $table->string('address_city')->nullable();
            $table->string('address_state')->nullable();
            $table->string('address_zip')->nullable();

            // Representante
            $table->string('representative_name')->nullable();
            $table->string('representative_role')->nullable();

            // Contato
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // Informações Adicionais
            $table->string('field_of_activity')->nullable(); // Área de Atuação
            $table->string('professional_council')->nullable(); // Conselho Profissional
            $table->string('council_registration_number')->nullable(); // Número do Registro no Conselho
            $table->string('process_number')->nullable(); // Número do Processo (para Credenciamentos)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
