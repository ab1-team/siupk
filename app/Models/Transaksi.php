<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 
use \Awobaz\Compoships\Compoships;
use Session;

class Transaksi extends Model
{
    use HasFactory, Compoships, SoftDeletes; 

    protected $table;
    public $timestamps = true; 

    protected $primaryKey = 'idt';
    protected $guarded = ['idt'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = 'transaksi_' . Session::get('lokasi');
    }

    public function angs()
    {
        return $this->hasMany(Transaksi::class, ['idtp', 'tgl_transaksi'], ['idtp', 'tgl_transaksi']);
    }

    public function rek_debit()
    {
        return $this->belongsTo(Rekening::class, 'rekening_debit', 'kode_akun');
    }

    public function rek_kredit()
    {
        return $this->belongsTo(Rekening::class, 'rekening_kredit', 'kode_akun');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function tr_idtp()
    {
        return $this->hasMany(Transaksi::class, 'idtp', 'idtp');
    }

    public function simpanan()
    {
        return $this->belongsTo(Simpanan::class, 'id_simp', 'id');
    }

    public function realSimpanan()
    {
        return $this->hasOne(RealSimpanan::class, 'idt', 'idt');
    }
}
