<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Whatsapp extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'whatsapp';

    protected $fillable = [
        'lokasi',
        'nama',
        'token',
        'device_id',
        'device_key',
        'status',
        'phone_number',
        'last_seen',
    ];

    protected $casts = [
        'last_seen' => 'datetime',
    ];

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'lokasi', 'id');
    }

    public function isConnected(): bool
    {
        return $this->status === 'connected' && !empty($this->device_id) && !empty($this->device_key);
    }
}
