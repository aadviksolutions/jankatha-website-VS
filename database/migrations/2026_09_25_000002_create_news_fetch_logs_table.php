<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_fetch_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('news_source_id')->nullable()->constrained('news_sources')->cascadeOnDelete();
            $table->string('status'); // 'success', 'failed', 'partial'
            $table->unsignedInteger('items_found')->default(0);
            $table->unsignedInteger('items_imported')->default(0);
            $table->unsignedInteger('items_skipped_duplicate')->default(0);
            $table->integer('http_status')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('execution_time_ms')->default(0);
            $table->timestamp('created_at')->nullable();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_fetch_logs');
    }
};
