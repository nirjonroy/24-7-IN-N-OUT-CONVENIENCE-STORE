<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Redirect extends Model
{
    use HasFactory;

    public const MATCH_EXACT = 'exact';
    public const MATCH_PREFIX = 'prefix';
    public const MATCH_TYPES = [self::MATCH_EXACT, self::MATCH_PREFIX];
    public const STATUS_CODES = [301, 302, 307, 308];

    protected $fillable = [
        'source_path', 'target_url', 'match_type', 'status_code', 'preserve_query_string',
        'hit_count', 'last_hit_at', 'note', 'is_active', 'created_by',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'preserve_query_string' => 'boolean',
        'hit_count' => 'integer',
        'last_hit_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeExact(Builder $query): Builder
    {
        return $query->where('match_type', self::MATCH_EXACT);
    }

    public function scopePrefix(Builder $query): Builder
    {
        return $query->where('match_type', self::MATCH_PREFIX);
    }
}
