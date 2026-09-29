<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

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
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'replied_at' => 'datetime',
    ];
}
