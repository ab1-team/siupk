<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Awobaz\Compoships\Compoships;
use Session;
use Carbon\Carbon;

class Rekening extends Model
{
    use HasFactory, Compoships;
    protected $table;
    public $timestamps = false;

    protected $guarded = ['id'];

    public function __construct()
    {
        $this->table = 'rekening_' . Session::get('lokasi');
    }

    /**
     * Scope untuk filter rekening aktif berdasarkan tgl_nonaktif
     * 
     * Logika:
     * - Rekening tampil jika tgl_nonaktif IS NULL (belum pernah ditetapkan status nonaktif)
     * - Rekening tampil jika tgl_nonaktif > tgl_kondisi (akan nonaktif di MASA DEPAN dari tgl_kondisi)
     * 
     * @param $query
     * @param string|null $tgl_kondisi - Tanggal kondisi untuk filtering. 
     *                                   Jika null, akan gunakan tgl_kondisi dari session atau hari ini
     * @return mixed
     */
    public function scopeAktif($query, $tgl_kondisi = null)
    {
        // Jika tidak ada tgl_kondisi, cek dari session (untuk laporan), jika tidak ada gunakan hari ini
        if ($tgl_kondisi === null) {
            $tgl_kondisi = Session::get('tgl_kondisi_laporan');
            if ($tgl_kondisi === null) {
                $tgl_kondisi = Carbon::now()->toDateString();
            }
        }

        return $query->where(function ($q) use ($tgl_kondisi) {
            $q->whereNull('tgl_nonaktif')
                ->orWhere('tgl_nonaktif', '>', $tgl_kondisi);
        });
    }

    public function trx_debit()
    {
        return $this->hasMany(Transaksi::class, 'rekening_debit', 'kode_akun');
    }

    public function trx_kredit()
    {
        return $this->hasMany(Transaksi::class, 'rekening_kredit', 'kode_akun');
    }

    public function inventaris()
    {
        return $this->hasMany(Inventaris::class, 'kategori', 'lev4');
    }

    public function saldo()
    {
        return $this->hasOne(Saldo::class, 'kode_akun', 'kode_akun');
    }

    public function kom_saldo()
    {
        return $this->hasMany(Saldo::class, 'kode_akun', 'kode_akun');
    }

    public function kom_eb()
    {
        return $this->hasMany(Ebudgeting::class, 'kode_akun', 'kode_akun');
    }

    public function eb()
    {
        return $this->hasOne(Ebudgeting::class, 'kode_akun', 'kode_akun');
    }
}
