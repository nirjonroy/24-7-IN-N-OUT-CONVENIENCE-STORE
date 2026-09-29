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
        Schema::create('contact_infos', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('address_title')->nullable();
            $table->text('address')->nullable();
            $table->string('google_map_text')->nullable();
            $table->text('google_map_url')->nullable();
            $table->string('business_details_title')->nullable();
            $table->text('business_details_description')->nullable();
            $table->string('business_profile_button_text')->nullable();
            $table->text('business_profile_url')->nullable();
            $table->text('map_embed_url')->nullable();
            $table->string('form_eyebrow')->nullable();
            $table->string('form_title')->nullable();
            $table->text('form_description')->nullable();
            $table->boolean('status')->default(true);
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
        Schema::dropIfExists('contact_infos');
    }
};
