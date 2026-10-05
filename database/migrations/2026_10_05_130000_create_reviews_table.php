<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('source', 50)->default('manual')->index();
            $table->string('external_id')->nullable();
            $table->string('author_name', 150);
            $table->string('author_url', 500)->nullable();
            $table->decimal('rating', 2, 1)->nullable()->index();
            $table->text('review_text')->nullable();
            $table->string('review_url', 500)->nullable();
            $table->dateTime('reviewed_at')->nullable()->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->string('page_name', 150)->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_image', 500)->nullable();
            $table->string('author', 150)->nullable();
            $table->string('publisher', 150)->nullable();
            $table->string('copyright')->nullable();
            $table->string('site_name', 150)->nullable();
            $table->text('keywords')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['source', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
