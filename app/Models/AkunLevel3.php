<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AkunLevel3 extends Model
{
    use HasFactory;

    protected $table = 'akun_level_3';
    public $timestamps = false;

    protected $primaryKey = 'kode_akun';
    protected $keyType = 'string';

    public function rek()
    {
        return $this->hasMany(Rekening::class, 'parent_id', 'id')->aktif()->orderBy('kode_akun', 'ASC');
    }
}
