<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $seoColumns = [
        'page_name' => ['string', 150],
        'seo_title' => ['string', 255],
        'seo_description' => ['text'],
        'meta_title' => ['string', 255],
        'meta_description' => ['text'],
        'meta_image' => ['string', 500],
        'author' => ['string', 150],
        'publisher' => ['string', 150],
        'copyright' => ['string', 255],
        'site_name' => ['string', 150],
        'keywords' => ['text'],
    ];

    private array $tables = [
        'abouts',
        'contacts',
        'contact_infos',
        'siteinfos',
        'sliders',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach ($this->seoColumns as $column => $definition) {
                    if (Schema::hasColumn($tableName, $column)) {
                        continue;
                    }

                    if ($definition[0] === 'text') {
                        $table->text($column)->nullable();
                    } else {
                        $table->string($column, $definition[1])->nullable();
                    }
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach (array_keys($this->seoColumns) as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
