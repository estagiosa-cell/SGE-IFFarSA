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
            $table->index('status');
            $table->index('company_legal_identifier');
            $table->index('student_name');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->index('legal_identifier');
            $table->index('address_city');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('deactivated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internships', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['company_legal_identifier']);
            $table->dropIndex(['student_name']);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex(['legal_identifier']);
            $table->dropIndex(['address_city']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['deactivated_at']);
        });
    }
};
