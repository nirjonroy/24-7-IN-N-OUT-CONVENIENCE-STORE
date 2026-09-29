<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactInfo extends Model
{
    use HasFactory;

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
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
