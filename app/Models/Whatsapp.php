<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Whatsapp extends Model
{
    use HasFactory;

    protected $table = 'whatsapp';
    public $timestamps = false;

    protected $fillable = [
        'lokasi',
        'nama',
        'token',
        'device_id',
        'device_key',
        'instance_token',
        'status',
        'phone_number',
        'last_seen',
        'deleted_at',
    ];

    protected $casts = [
        'last_seen'  => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'lokasi', 'id');
    }
}
