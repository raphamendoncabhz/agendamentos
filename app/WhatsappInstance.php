<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'instance_name',
        'instance_key',
        'status',
        'connection_data',
        'qrcode'
    ];

    protected $casts = [
        'connection_data' => 'array',
    ];
}