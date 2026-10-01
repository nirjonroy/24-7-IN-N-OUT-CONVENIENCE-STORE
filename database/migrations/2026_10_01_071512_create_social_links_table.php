<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 50);
            $table->string('label', 100)->nullable();
            $table->text('url');
            $table->string('icon', 100)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
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
            $table->index(['business_id', 'platform', 'is_active', 'sort_order'], 'social_links_admin_lookup_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('social_links');
    }
};
