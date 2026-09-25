<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_sources', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('url')->nullable();
            $table->text('feed_url');
            $table->string('source_type')->default('rss'); // 'rss' or 'json_api'
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('state')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('language')->default('hi');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('fetch_frequency_minutes')->default(10);
            $table->integer('priority')->default(0);
            $table->string('logo_url')->nullable();
            $table->text('attribution_text')->nullable();
            $table->timestamp('last_fetched_at')->nullable();
            $table->text('last_error')->nullable();
            $table->unsignedInteger('items_fetched_count')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('priority');
            $table->index('last_fetched_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_sources');
    }
};
