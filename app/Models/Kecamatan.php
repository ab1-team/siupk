<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    use HasFactory;

    protected $table = 'kecamatan';
    public $timestamps = false;

    protected $fillable = [
        'web_kec',
        'web_alternatif'
    ];

    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class, 'kd_kab', 'kd_kab');
    }

    public function desa()
    {
        return $this->hasMany(Desa::class, 'kd_kec', 'kd_kec')->orderBy('kd_desa', 'ASC');
    }

    public function ttd()
    {
        return $this->belongsTo(TandaTanganLaporan::class, 'id', 'lokasi');
    }

    public function saldo()
    {
        return $this->hasMany(Saldo::class, 'kode_akun', 'kd_kec');
    }
}
