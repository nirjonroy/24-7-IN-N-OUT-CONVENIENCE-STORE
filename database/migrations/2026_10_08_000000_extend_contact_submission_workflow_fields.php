<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            if (! Schema::hasColumn('contacts', 'phone')) {
                $table->string('phone', 30)->nullable()->after('email');
            }

            if (! Schema::hasColumn('contacts', 'subject')) {
                $table->string('subject')->nullable()->after('topic');
            }

            if (! Schema::hasColumn('contacts', 'referrer')) {
                $table->string('referrer', 1000)->nullable()->after('user_agent');
            }

            if (! Schema::hasColumn('contacts', 'page_url')) {
                $table->string('page_url', 1000)->nullable()->after('referrer');
            }

            if (! Schema::hasColumn('contacts', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('replied_at');
            }
        });

        Schema::table('contact_infos', function (Blueprint $table) {
            if (! Schema::hasColumn('contact_infos', 'recipient_email')) {
                $table->string('recipient_email')->nullable()->after('form_description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            foreach (['phone', 'subject', 'referrer', 'page_url', 'submitted_at'] as $column) {
                if (Schema::hasColumn('contacts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('contact_infos', function (Blueprint $table) {
            if (Schema::hasColumn('contact_infos', 'recipient_email')) {
                $table->dropColumn('recipient_email');
            }
        });
    }
};
