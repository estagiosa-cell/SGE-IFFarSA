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
        Schema::create('internship_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('required_hours');
            $table->unsignedTinyInteger('weight');
            $table->decimal('great_value', 8, 2);
            $table->decimal('very_good_value', 8, 2);
            $table->decimal('good_value', 8, 2);
            $table->decimal('satisfactory_value', 8, 2);
            $table->decimal('unsatisfactory_value', 8, 2);
            $table->foreignId('course_id')->constrained('courses');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internship_types');
    }
};
