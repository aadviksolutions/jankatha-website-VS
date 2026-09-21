<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('submission_id')->constrained('citizen_submissions')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type', 20);
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('file_size');
            $table->string('media_type', 20);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['submission_id', 'media_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_media');
    }
};
