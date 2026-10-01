<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->index('city', 'locations_city_index');
            $table->index('state', 'locations_state_index');
            $table->index('postal_code', 'locations_postal_code_index');
            $table->index('is_active', 'locations_is_active_index');
            $table->index('is_primary', 'locations_is_primary_index');
        });

        Schema::table('social_links', function (Blueprint $table) {
            $table->index('platform', 'social_links_platform_index');
            $table->index('is_active', 'social_links_is_active_index');
            $table->index('sort_order', 'social_links_sort_order_index');
        });
    }

    public function down(): void
    {
        Schema::table('social_links', function (Blueprint $table) {
            $table->dropIndex('social_links_platform_index');
            $table->dropIndex('social_links_is_active_index');
            $table->dropIndex('social_links_sort_order_index');
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->dropIndex('locations_city_index');
            $table->dropIndex('locations_state_index');
            $table->dropIndex('locations_postal_code_index');
            $table->dropIndex('locations_is_active_index');
            $table->dropIndex('locations_is_primary_index');
        });
    }
};
