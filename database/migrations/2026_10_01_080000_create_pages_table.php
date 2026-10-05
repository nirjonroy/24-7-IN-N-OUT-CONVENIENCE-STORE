<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->string('route_name', 150)->nullable();
            $table->string('template', 100)->default('default');
            $table->string('h1', 255);
            $table->text('intro_text')->nullable();
            $table->string('canonical_url', 500)->nullable();
            $table->string('og_title', 255)->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image', 500)->nullable();
            $table->boolean('robots_index')->default(true)->index();
            $table->boolean('robots_follow')->default(true);
            $table->boolean('is_home')->default(false)->index();
            $table->string('status', 30)->default('draft')->index();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->string('page_name', 150)->nullable();
            $table->string('seo_title', 255)->nullable();
            $table->text('seo_description')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_image', 500)->nullable();
            $table->string('author', 150)->nullable();
            $table->string('publisher', 150)->nullable();
            $table->string('copyright', 255)->nullable();
            $table->string('site_name', 150)->nullable();
            $table->text('keywords')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
