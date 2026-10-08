<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactInfo extends Model
{
    use HasFactory;
    use HasSeoMeta;

    protected $fillable = [
        'eyebrow',
        'title',
        'description',
        'address_title',
        'address',
        'google_map_text',
        'google_map_url',
        'business_details_title',
        'business_details_description',
        'business_profile_button_text',
        'business_profile_url',
        'map_embed_url',
        'form_eyebrow',
        'form_title',
        'form_description',
        'recipient_email',
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
