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
        Schema::create('abouts', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow')->nullable();
            $table->string('title');
            $table->text('description_one')->nullable();
            $table->text('description_two')->nullable();
            $table->string('image')->nullable();
            $table->string('image_alt')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->string('identity_eyebrow')->nullable();
            $table->string('identity_title')->nullable();
            $table->text('identity_description')->nullable();
            $table->string('category_one_label')->nullable();
            $table->string('category_one_title')->nullable();
            $table->string('category_two_label')->nullable();
            $table->string('category_two_title')->nullable();
            $table->string('category_three_label')->nullable();
            $table->string('category_three_title')->nullable();
            $table->string('category_four_label')->nullable();
            $table->string('category_four_title')->nullable();
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
        Schema::dropIfExists('abouts');
    }
};
