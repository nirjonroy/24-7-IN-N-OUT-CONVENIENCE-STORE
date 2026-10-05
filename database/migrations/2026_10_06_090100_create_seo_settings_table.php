<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name', 150)->nullable();
            $table->string('default_title')->nullable();
            $table->string('title_separator', 20)->default('|');
            $table->text('default_description')->nullable();
            $table->text('default_keywords')->nullable();
            $table->string('default_author', 150)->nullable();
            $table->string('default_publisher', 150)->nullable();
            $table->string('default_copyright')->nullable();
            $table->string('default_meta_image', 500)->nullable();
            $table->string('canonical_base_url', 500)->nullable();
            $table->boolean('default_robots_index')->default(true);
            $table->boolean('default_robots_follow')->default(true);
            $table->string('twitter_card', 50)->default('summary_large_image');
            $table->string('twitter_site', 100)->nullable();
            $table->string('facebook_app_id', 100)->nullable();
            $table->string('google_site_verification')->nullable();
            $table->string('bing_site_verification')->nullable();
            $table->text('robots_txt_extra')->nullable();
            $table->boolean('sitemap_enabled')->default(true);
            $table->boolean('robots_enabled')->default(true);
            $table->boolean('structured_data_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_settings');
    }
};
