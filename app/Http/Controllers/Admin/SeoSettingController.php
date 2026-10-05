<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSeoSettingRequest;
use App\Models\SeoSetting;
use App\Services\MediaAttachmentService;
use App\Services\SitemapService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SeoSettingController extends Controller
{
    public function edit(): View
    {
        $seoSetting = SeoSetting::current();
        $seoSetting->load('mediaAttachments.media.variants');

        return view('admin.seo.settings.edit', compact('seoSetting'));
    }

    public function update(UpdateSeoSettingRequest $request, MediaAttachmentService $mediaAttachments, SitemapService $sitemapService): RedirectResponse
    {
        DB::transaction(function () use ($request, $mediaAttachments) {
            $seoSetting = SeoSetting::current();
            $data = $request->validated();
            $mediaId = $data['default_meta_image_media_id'] ?? null;
            $mediaAlt = $data['default_meta_image_alt_override'] ?? null;
            unset($data['default_meta_image_media_id'], $data['default_meta_image_alt_override']);

            foreach (['default_robots_index', 'default_robots_follow', 'sitemap_enabled', 'robots_enabled', 'structured_data_enabled'] as $field) {
                $data[$field] = $request->boolean($field);
            }

            $data['title_separator'] = $data['title_separator'] ?: '|';
            $data['twitter_card'] = $data['twitter_card'] ?: 'summary_large_image';
            $seoSetting->update($data);

            $mediaAttachments->syncSingle($seoSetting, 'default_meta_image', $mediaId ? (int) $mediaId : null, [
                'alt_text_override' => $mediaAlt,
            ]);
        });

        SeoSetting::clearCache();
        $sitemapService->clearCache();

        return redirect()->route('admin.seo.settings.edit')->with('success', 'SEO settings updated successfully.');
    }
}
