<?php

namespace Database\Seeders;

use App\Models\Redirect;
use App\Services\RedirectService;
use Illuminate\Database\Seeder;

class LegacyFrontendRedirectSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('public-pages.legacy_html_redirects', []) as $source => $target) {
            Redirect::firstOrCreate(
                [
                    'source_path' => $source,
                    'match_type' => Redirect::MATCH_EXACT,
                ],
                [
                    'target_url' => $target,
                    'status_code' => 301,
                    'preserve_query_string' => true,
                    'is_active' => true,
                    'note' => 'Legacy frontend HTML redirect.',
                ]
            );
        }

        app(RedirectService::class)->clearCache();
    }
}
