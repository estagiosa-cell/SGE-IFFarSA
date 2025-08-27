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
            $table->string('address_street');
            $table->string('address_number');
            $table->string('address_neighborhood');
            $table->string('address_city');
            $table->enum('address_state', ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO']);
            $table->string('address_zip');

            // Representante
            $table->string('representative_name');
            $table->string('representative_role');

            // Contato
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // Informações Adicionais
            $table->string('field_of_activity'); // Área de Atuação
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
