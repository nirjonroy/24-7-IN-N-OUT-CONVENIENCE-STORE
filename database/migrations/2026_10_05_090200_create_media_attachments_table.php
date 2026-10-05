<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_asset_id')->constrained()->cascadeOnDelete();
            $table->string('mediable_type');
            $table->unsignedBigInteger('mediable_id');
            $table->string('collection', 100)->index();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->boolean('is_primary')->default(false);
            $table->string('alt_text_override', 255)->nullable();
            $table->string('title_override', 255)->nullable();
            $table->text('caption_override')->nullable();
            $table->timestamps();

            $table->index(['mediable_type', 'mediable_id']);
            $table->index(['mediable_type', 'mediable_id', 'collection']);
            $table->unique(['media_asset_id', 'mediable_type', 'mediable_id', 'collection'], 'media_attachment_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_attachments');
    }
};
