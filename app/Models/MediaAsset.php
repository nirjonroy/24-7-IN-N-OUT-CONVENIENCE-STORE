<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class MediaAsset extends Model
{
    use HasFactory;
    use HasSeoMeta;
    use SoftDeletes;

    protected $fillable = [
        'disk', 'directory', 'path', 'file_name', 'original_name', 'mime_type', 'extension',
        'file_size', 'width', 'height', 'checksum', 'title', 'alt_text', 'caption', 'credit',
        'source_url', 'uploaded_by', 'is_active', 'page_name', 'seo_title', 'seo_description',
        'meta_title', 'meta_description', 'meta_image', 'author', 'publisher', 'copyright',
        'site_name', 'keywords',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'uploaded_by' => 'integer',
        'is_active' => 'boolean',
    ];

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(MediaVariant::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MediaAttachment::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getHumanFileSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1).' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }

    public function getVariant(string $variant): ?MediaVariant
    {
        return $this->relationLoaded('variants')
            ? $this->variants->firstWhere('variant', $variant)
            : $this->variants()->where('variant', $variant)->first();
    }

    public function getVariantUrl(string $variant): string
    {
        $mediaVariant = $this->getVariant($variant);

        return $mediaVariant
            ? Storage::disk($mediaVariant->disk)->url($mediaVariant->path)
            : $this->url;
    }
}
