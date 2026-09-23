<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Session;

class PinjamanIndividu extends Model
{
    use HasFactory;
    protected $table;
    public $timestamps = false;

    protected $guarded = ['id'];

    public function __construct()
    {
        $this->table = 'pinjaman_anggota_' . Session::get('lokasi');
    }

    public function jpp()
    {
        return $this->belongsTo(JenisProdukPinjaman::class, 'jenis_pp');
    }

    public function jasa()
    {
        return $this->belongsTo(JenisJasa::class, 'jenis_jasa');
    }

    public function angsuran_pokok()
    {
        return $this->belongsTo(SistemAngsuran::class, 'sistem_angsuran');
    }

    public function angsuran_jasa()
    {
        return $this->belongsTo(SistemAngsuran::class, 'sa_jasa');
    }

    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'nia', 'id');
    }

    public function sts()
    {
        return $this->belongsTo(StatusPinjaman::class, 'status', 'kd_status');
    }

    public function ra_i()
    {
        return $this->hasMany(RencanaAngsuranI::class, 'loan_id')->orderBy('angsuran_ke', 'ASC');
    }

    public function real_i()
    {
        return $this->hasMany(RealAngsuranI::class, 'loan_id')->orderBy('tgl_transaksi', 'ASC')->orderBy('id', 'ASC');
    }

    public function sis_pokok()
    {
        return $this->belongsTo(SistemAngsuran::class, 'sistem_angsuran');
    }

    public function sis_jasa()
    {
        return $this->belongsTo(SistemAngsuran::class, 'sa_jasa');
    }

    public function saldo()
    {
        return $this->hasOne(RealAngsuranI::class, 'loan_id')->orderBy('tgl_transaksi', 'DESC')->orderBy('id', 'DESC');
    }

    public function saldo2()
    {
        return $this->hasOne(RealAngsuranI::class, 'loan_id')->orderBy('tgl_transaksi', 'ASC')->orderBy('id', 'ASC');
    }

    public function target()
    {
        return $this->hasOne(RencanaAngsuranI::class, 'loan_id')->orderBy('jatuh_tempo', 'DESC');
    }

    public function rencana()
    {
        return $this->hasMany(RencanaAngsuranI::class, 'loan_id')->orderBy('jatuh_tempo', 'ASC');
    }

    public function rencana1()
    {
        return $this->hasOne(RencanaAngsuranI::class, 'loan_id')->orderBy('jatuh_tempo', 'ASC');
    }

    public function trx()
    {
        return $this->hasMany(Transaksi::class, 'id_pinj_i', 'id')->orderBy('tgl_transaksi', 'ASC')->orderBy('idtp', 'ASC');
    }

    public function saldo_pinjaman()
    {
        return $this->hasOne(Penghapusan::class, 'id_pinj_i', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pinj_i()
    {
        return $this->hasOne(PinjamanAnggota::class, 'nia', 'nia')->orderBy('tgl_cair', 'DESC');
    }

    public function getTableStructure()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }

    /**
     * Delegasi ke PinjamanAnggota::getJatuhTempoEfektif() jika ada record terkait,
     * jika tidak return null (loan belum ada data).
     */
    public function getJatuhTempoEfektif()
    {
        $pinj = $this->pinj_i;
        if ($pinj) {
            return $pinj->getJatuhTempoEfektif();
        }
        return null;
    }
}
