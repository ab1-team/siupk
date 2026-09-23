<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Session;

class PinjamanAnggota extends Model
{
    use HasFactory;
    protected $table;
    public $timestamps = false;

    protected $guarded = ['id'];

    public function __construct()
    {
        $this->table = 'pinjaman_anggota_' . Session::get('lokasi');
    }
 

    public function sts()
    {
        return $this->belongsTo(StatusPinjaman::class, 'status', 'kd_status');
    }

    public function sis_pokok()
    {
        return $this->belongsTo(SistemAngsuran::class, 'sistem_angsuran');
    }

    public function sis_jasa()
    {
        return $this->belongsTo(SistemAngsuran::class, 'sa_jasa');
    }

    public function pinkel()
    {
        return $this->belongsTo(PinjamanKelompok::class, 'id_pinkel');
    }

    public function target()
    {
        return $this->hasOne(RencanaAngsuranI::class, 'loan_id')->orderBy('jatuh_tempo', 'DESC');
    }

    public function jasa()
    {
        return $this->belongsTo(JenisJasa::class, 'jenis_jasa');
    }

    public function kelompok()
    {
        return $this->belongsTo(Kelompok::class, 'id_kel');
    }

    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'nia', 'id');
    }

    public function pinjaman()
    {
        return $this->hasOne(PinjamanAnggota::class, 'nia', 'nia');
    }

    public function pemanfaat()
    {
        return $this->hasOne(DataPemanfaat::class, 'nia', 'nia');
    }

    public function pinj_ang()
    {
        return $this->hasOne(PinjamanAnggota::class, 'nia', 'nia')->orderBy('tgl_cair', 'DESC');
    }

    public function angsuran_pokok()
    {
        return $this->belongsTo(SistemAngsuran::class, 'sistem_angsuran');
    }

    public function angsuran_jasa()
    {
        return $this->belongsTo(SistemAngsuran::class, 'sa_jasa');
    }

    public function rencana()
    {
        return $this->hasMany(RencanaAngsuranI::class, 'loan_id')->orderBy('jatuh_tempo', 'ASC');
    }

    public function getTableStructure()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
    public function jpp()
    {
        return $this->belongsTo(JenisProdukPinjaman::class, 'jenis_pp', 'id');
    }
    public function saldo()
    {
        return $this->hasOne(RealAngsuranI::class, 'loan_id')->orderBy('tgl_transaksi', 'DESC')->orderBy('id', 'DESC');
    }

    public function saldo2()
    {
        return $this->hasOne(RealAngsuranI::class, 'loan_id')->orderBy('tgl_transaksi', 'ASC')->orderBy('id', 'ASC');
    }
    public function saldo_pinjaman()
    {
        return $this->hasOne(Penghapusan::class, 'id_pinj', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function real()
    {
        return $this->hasMany(RealAngsuran::class, 'loan_id')->orderBy('tgl_transaksi', 'ASC')->orderBy('id', 'ASC');
    }
    public function real_i()
    {
        return $this->hasMany(RealAngsuranI::class, 'loan_id')->orderBy('tgl_transaksi', 'ASC')->orderBy('id', 'ASC');
    }

    /**
     * Hitung tanggal jatuh tempo efektif berdasarkan rencana angsuran.
     *
     * Logika (sesuai requirement user):
     * 1. Jika status L/R/H -> pakai tgl_lunas langsung
     * 2. Ambil baris di rencana_angsuran_i dengan angsuran_ke terbesar untuk loan ini
     *    (tanpa filter target_pokok, hanya angsuran_ke terbesar)
     * 3. Kembalikan jatuh_tempo dari baris tersebut
     */
    public function getJatuhTempoEfektif()
    {
        if (in_array($this->status, ['L', 'R', 'H'])) {
            return $this->tgl_lunas;
        }

        $tableRencana = 'rencana_angsuran_i_' . Session::get('lokasi');

        $row = RencanaAngsuranI::on('mysql')
            ->from($tableRencana)
            ->where('loan_id', $this->id)
            ->orderBy('id', 'DESC')
            ->first();
        return $row ? $row->jatuh_tempo : null;
    }
}
