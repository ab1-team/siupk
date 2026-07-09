<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiEndpoint extends Model
{
    use HasFactory;

    protected $table = 'api_endpoint';

    protected $fillable = [
        'whatsapp_api',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function activeWhatsappApi(): ?string
    {
        $row = static::where('is_active', true)->first();
        return $row?->whatsapp_api;
    }
}
