<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citizen_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('headline');
            $table->longText('description');
            $table->string('location');
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            $table->date('event_date')->nullable();
            $table->time('event_time')->nullable();
            $table->string('contributor_name');
            $table->string('mobile', 30);
            $table->string('email')->nullable();
            $table->text('source_information')->nullable();
            $table->string('video_url')->nullable();
            $table->timestamp('consent_at')->nullable();
            $table->string('status')->default('pending');
            $table->text('editor_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('published_news_id')->nullable()->constrained('news')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citizen_submissions');
    }
};
