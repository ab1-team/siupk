<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatatanBp extends Model
{
    protected $table      = 'catatan_bp';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    protected $fillable = [
        'tanggal',
        'lokasi',
        'bulan_laporan',
        'isi',
        'id_sender',
    ];

    // Relasi ke tabel users
    public function sender()
    {
        return $this->belongsTo(User::class, 'id_sender', 'id');
    }
}
