<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table): void {
            $table->foreignId('source_id')->nullable()->after('author_id')->constrained('news_sources')->nullOnDelete();
            $table->string('source_name')->nullable()->after('source_id');
            $table->text('source_url')->nullable()->after('source_name');
            $table->string('source_guid', 191)->nullable()->after('source_url');
            $table->string('content_hash', 64)->nullable()->after('source_guid');
            $table->boolean('is_auto_fetched')->default(false)->after('content_hash');
            $table->string('city')->nullable()->after('district');
            $table->string('locality')->nullable()->after('city');
            $table->text('attribution_text')->nullable()->after('locality');

            $table->index('source_guid');
            $table->index('content_hash');
            $table->index('is_auto_fetched');
            $table->index('district');
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table): void {
            $table->dropForeign(['source_id']);
            $table->dropIndex(['source_guid']);
            $table->dropIndex(['content_hash']);
            $table->dropIndex(['is_auto_fetched']);
            $table->dropIndex(['district']);
            $table->dropIndex(['city']);

            $table->dropColumn([
                'source_id',
                'source_name',
                'source_url',
                'source_guid',
                'content_hash',
                'is_auto_fetched',
                'city',
                'locality',
                'attribution_text',
            ]);
        });
    }
};
