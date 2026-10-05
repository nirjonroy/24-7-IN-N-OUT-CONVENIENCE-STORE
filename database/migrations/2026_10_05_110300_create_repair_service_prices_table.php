<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_service_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_model_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('compare_at_price', 10, 2)->nullable();
            $table->string('price_label', 100)->nullable();
            $table->boolean('is_price_visible')->default(true);
            $table->unsignedSmallInteger('estimated_minutes')->nullable();
            $table->string('warranty_text', 255)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_available')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->timestamps();

            $table->unique(['repair_service_id', 'device_model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_service_prices');
    }
};
