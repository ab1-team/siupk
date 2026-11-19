<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Storage;

class Kabupaten extends Authenticatable
{
    use HasFactory;

    protected $table = 'kabupaten';

    /**
     * Apakah logo kabupaten deterministik sudah diunggah?
     * File: storage/app/public/logo_kab/{id}.png
     */
    public function logoKabTersedia(): bool
    {
        return Storage::disk('public')->exists('logo_kab/' . $this->id . '.png');
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'kd_prov', 'kode');
    }

    public function kec()
    {
        return $this->hasMany(Kecamatan::class, 'kd_kab', 'kd_kab');
    }
}
