<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('source_path', 500)->index();
            $table->string('target_url', 1000);
            $table->string('match_type', 30)->default('exact')->index();
            $table->unsignedSmallInteger('status_code')->default(301)->index();
            $table->boolean('preserve_query_string')->default(true);
            $table->unsignedBigInteger('hit_count')->default(0);
            $table->timestamp('last_hit_at')->nullable()->index();
            $table->string('note')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('created_by');
            $table->index(['is_active', 'source_path', 'match_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirects');
    }
};
