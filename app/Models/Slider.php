<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;

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
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
