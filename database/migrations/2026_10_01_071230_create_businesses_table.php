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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('short_name', 100)->nullable();
            $table->string('legal_name', 180)->nullable();
            $table->string('tagline')->nullable();
            $table->longText('description')->nullable();
            $table->string('primary_category', 120)->nullable();
            $table->json('schema_types')->nullable();
            $table->char('currency', 3)->default('USD');
            $table->unsignedTinyInteger('minimum_age')->nullable();
            $table->text('adult_retail_notice')->nullable();
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
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('businesses');
    }
};
