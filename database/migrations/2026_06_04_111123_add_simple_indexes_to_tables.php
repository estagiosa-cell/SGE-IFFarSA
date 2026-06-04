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
            $table->index('student_registration_number');
        });

        Schema::table('supervisor_evaluations', function (Blueprint $table) {
            $table->index('student_name');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internships', function (Blueprint $table) {
            $table->dropIndex(['student_registration_number']);
        });

        Schema::table('supervisor_evaluations', function (Blueprint $table) {
            $table->dropIndex(['student_name']);
        });


    }
};
