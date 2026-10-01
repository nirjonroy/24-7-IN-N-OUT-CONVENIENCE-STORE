<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siteinfo extends Model
{
    use HasFactory;
    use HasSeoMeta;

    protected $fillable = [
        'logo',
        'name',
        'favicon',
        'email',
        'phone',
        'address',
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
}
