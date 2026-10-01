<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;
    use HasSeoMeta;

    public const STATUSES = ['new', 'read', 'replied', 'spam'];

    protected $fillable = [
        'name',
        'email',
        'topic',
        'message',
        'status',
        'ip_address',
        'user_agent',
        'read_at',
        'replied_at',
        'page_name',
        'seo_title',
        'seo_description',
        'meta_title',
        'meta_description',
        'meta_image',
        'author',
        'publisher',
        'copyright',
        'site_name',
        'keywords',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'replied_at' => 'datetime',
    ];
}
