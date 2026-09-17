<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siteinfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'logo',
        'name',
        'favicon',
        'email',
        'phone',
        'address',
    ];
}
