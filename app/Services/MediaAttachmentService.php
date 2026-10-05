<?php

namespace App\Services;

use App\Models\MediaAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MediaAttachmentService
{
    public const SINGLE_COLLECTIONS = [
        'meta_image', 'og_image', 'image', 'background_image', 'logo', 'favicon', 'avatar', 'default_meta_image',
    ];

    public function syncSingle(Model $model, string $collection, ?int $mediaAssetId, array $overrides = []): void
    {
        DB::transaction(function () use ($model, $collection, $mediaAssetId, $overrides) {
            $query = $model->mediaAttachments()->where('collection', $collection);

            if (! $mediaAssetId) {
                $query->delete();
                return;
            }

            $media = MediaAsset::active()->find($mediaAssetId);

            if (! $media) {
                throw ValidationException::withMessages([
                    $collection.'_media_id' => 'The selected media is not available.',
                ]);
            }

            $query->delete();
            $model->mediaAttachments()->create([
                'media_asset_id' => $media->id,
                'collection' => $collection,
                'sort_order' => 0,
                'is_primary' => true,
                'alt_text_override' => $overrides['alt_text_override'] ?? null,
                'title_override' => $overrides['title_override'] ?? null,
                'caption_override' => $overrides['caption_override'] ?? null,
            ]);
        });
    }

    public function attach(Model $model, string $collection, int $mediaAssetId, array $overrides = []): void
    {
        $media = MediaAsset::active()->find($mediaAssetId);

        if (! $media) {
            throw ValidationException::withMessages([
                'media_asset_id' => 'The selected media is not available.',
            ]);
        }

        $model->mediaAttachments()->firstOrCreate(
            [
                'media_asset_id' => $media->id,
                'collection' => $collection,
            ],
            [
                'sort_order' => $overrides['sort_order'] ?? 0,
                'is_primary' => $overrides['is_primary'] ?? false,
                'alt_text_override' => $overrides['alt_text_override'] ?? null,
                'title_override' => $overrides['title_override'] ?? null,
                'caption_override' => $overrides['caption_override'] ?? null,
            ]
        );
    }

    public function detach(Model $model, string $collection, ?int $mediaAssetId = null): void
    {
        $query = $model->mediaAttachments()->where('collection', $collection);

        if ($mediaAssetId) {
            $query->where('media_asset_id', $mediaAssetId);
        }

        $query->delete();
    }

    public function syncCollection(Model $model, string $collection, array $mediaAssetIds): void
    {
        DB::transaction(function () use ($model, $collection, $mediaAssetIds) {
            $model->mediaAttachments()->where('collection', $collection)->delete();

            foreach (array_values(array_filter($mediaAssetIds)) as $index => $mediaAssetId) {
                $this->attach($model, $collection, (int) $mediaAssetId, [
                    'sort_order' => $index,
                    'is_primary' => $index === 0,
                ]);
            }
        });
    }
}
