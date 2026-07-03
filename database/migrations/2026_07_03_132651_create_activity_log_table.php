<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable()->index();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->string('event')->nullable();
            $table->nullableMorphs('causer', 'causer');
            $table->jsonb('attribute_changes')->nullable();
            $table->jsonb('properties')->nullable();
            $table->timestamps();

            $table->index('attribute_changes', 'activity_log_attribute_changes_gin', 'gin');
            $table->index('properties', 'activity_log_properties_gin', 'gin');
        });
    }
};
