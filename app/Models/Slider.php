<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;
    use HasSeoMeta;

    protected $fillable = [
        'title',
        'description',
        'primary_button_text',
        'primary_button_url',
        'secondary_button_text',
        'secondary_button_url',
        'image',
        'image_alt',
        'address_title',
        'address_subtitle',
        'map_button_text',
        'map_url',
        'status',
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
        'status' => 'boolean',
    ];
}
