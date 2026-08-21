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
        Schema::table('internship_types', function (Blueprint $table) {
            $table->unsignedTinyInteger('report_weight')->nullable()->after('weight');
            $table->unsignedTinyInteger('presentation_weight')->nullable()->after('report_weight');
        });

        Schema::table('internships', function (Blueprint $table) {
            $table->unsignedTinyInteger('report_weight')->nullable()->after('internship_type_weight');
            $table->unsignedTinyInteger('presentation_weight')->nullable()->after('report_weight');
            $table->decimal('report_grade', 5, 2)->nullable()->after('evaluation_grade');
            $table->decimal('presentation_grade', 5, 2)->nullable()->after('report_grade');
            $table->decimal('consolidated_grade', 5, 2)->nullable()->after('presentation_grade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internships', function (Blueprint $table) {
            $table->dropColumn(['report_weight', 'presentation_weight', 'report_grade', 'presentation_grade', 'consolidated_grade']);
        });

        Schema::table('internship_types', function (Blueprint $table) {
            $table->dropColumn(['report_weight', 'presentation_weight']);
        });
    }
};
