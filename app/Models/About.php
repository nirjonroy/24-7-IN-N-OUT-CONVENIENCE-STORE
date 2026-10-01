<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class About extends Model
{
    use HasFactory;
    use HasSeoMeta;

    protected $fillable = [
        'eyebrow',
        'title',
        'description_one',
        'description_two',
        'image',
        'image_alt',
        'button_text',
        'button_url',
        'identity_eyebrow',
        'identity_title',
        'identity_description',
        'category_one_label',
        'category_one_title',
        'category_two_label',
        'category_two_title',
        'category_three_label',
        'category_three_title',
        'category_four_label',
        'category_four_title',
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
