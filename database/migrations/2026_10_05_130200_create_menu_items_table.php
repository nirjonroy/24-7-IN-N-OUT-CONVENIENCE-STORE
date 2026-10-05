<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->nullOnDelete();
            $table->foreignId('page_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label', 150);
            $table->string('url', 500)->nullable();
            $table->string('link_type', 30)->default('page')->index();
            $table->string('icon', 100)->nullable();
            $table->string('badge', 100)->nullable();
            $table->string('target', 20)->default('_self');
            $table->string('rel', 100)->nullable();
            $table->string('css_identifier', 100)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
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

            $table->index(['menu_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
