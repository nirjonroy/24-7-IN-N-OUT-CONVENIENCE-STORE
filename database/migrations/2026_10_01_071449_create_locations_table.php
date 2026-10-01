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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('slug', 180);
            $table->string('phone', 30)->nullable();
            $table->string('secondary_phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('postal_code', 20);
            $table->char('country_code', 2)->default('US');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 11, 7)->nullable();
            $table->string('timezone', 60)->nullable();
            $table->string('price_range', 50)->nullable();
            $table->string('google_place_id')->nullable();
            $table->text('google_business_url')->nullable();
            $table->text('google_maps_url')->nullable();
            $table->text('directions_url')->nullable();
            $table->boolean('is_primary')->default(false);
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
            $table->unique(['business_id', 'slug']);
            $table->index(['business_id', 'city', 'state', 'postal_code', 'is_active', 'is_primary'], 'locations_admin_lookup_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('locations');
    }
};
