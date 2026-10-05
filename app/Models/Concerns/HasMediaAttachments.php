<?php

namespace App\Models\Concerns;

use App\Models\MediaAsset;
use App\Models\MediaAttachment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasMediaAttachments
{
    public function mediaAttachments(): MorphMany
    {
        return $this->morphMany(MediaAttachment::class, 'mediable')->with('media');
    }

    public function getMediaAttachment(string $collection): ?MediaAttachment
    {
        return $this->mediaAttachments()
            ->where('collection', $collection)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();
    }

    public function getMediaAsset(string $collection): ?MediaAsset
    {
        return $this->getMediaAttachment($collection)?->media;
    }

    public function getMediaUrl(string $collection, ?string $fallback = null, ?string $variant = null): ?string
    {
        $media = $this->getMediaAsset($collection);

        if (! $media) {
            return $fallback;
        }

        return $variant ? $media->getVariantUrl($variant) : $media->url;
    }

    public function getMediaAlt(string $collection, ?string $fallback = null): string
    {
        $attachment = $this->getMediaAttachment($collection);

        return $attachment?->alt_text_override ?: ($attachment?->media?->alt_text ?: ($fallback ?? ''));
    }
}
