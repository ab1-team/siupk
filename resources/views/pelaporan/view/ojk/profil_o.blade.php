@php
use App\Utils\Tanggal;
use App\Utils\Keuangan;
$keuangan = new Keuangan();
@endphp

@extends('pelaporan.layout.base')

@section('content')
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
    .style3 {
        font-family: Verdana, Arial, Helvetica, sans-serif;
        font-size: 24px;
        font-weight: bold;
    }

    .style6 {
        font-family: Verdana, Arial, Helvetica, sans-serif;
        font-size: 16px;
    }

    .style9 {
        font-family: Verdana, Arial, Helvetica, sans-serif;
        font-size: 11px;
    }

    .top {
        border-top: 1px solid #000000;
    }

    .bottom {
        border-bottom: 1px solid #000000;
    }

    .left {
        border-left: 1px solid #000000;
    }

    .right {
        border-right: 1px solid #000000;
    }

    .all {
        border: 1px solid #000000;
    }

    .style27 {
        font-family: Verdana, Arial, Helvetica, sans-serif;
        font-size: 11px;
        font-weight: bold;
    }

    .align-justify {
        text-align: justify;
    }

    .align-center {
        text-align: center;
    }

</style>
{{-- <table width="97%" border="0" align="center" cellpadding="3" cellspacing="0">
    <tr align="center">
        <td colspan="3" width="10%" class="style6 bottom">
            <br>
            <span><b>{{$kec->nama_lembaga_long}}</b></span><br>
            {{ strtoupper($nama_kecamatan) }}<br>
            <span class="style9">{{ $info }}<br>


            </span>
        </td>
    </tr>
</table> --}}
<table width="97%" border="0" align="center" cellpadding="3" cellspacing="0">
    <tr>
        <td class="style6 align-center" colspan="3"><b>PROFIL Lembaga</b> <br>
            <span class="style9 align-center">Untuk Periode Yang Berakhir Pada Tanggal {{ $tgl }}</span>
            <p class="style9 align-center"> &nbsp; </p>
        </td>
    </tr>
    <tr>
        <td width="2%" class="style9">1.</td>
        <td width="30%" class="style9">Nama Lembaga</td>
        <td width="60%" class="style9">: {{$kec->nama_lembaga_long}}</td>
    </tr>
    <tr>
        <td width="2%" class="style9">2.</td>
        <td width="30%" class="style9">Nomor Lembaga</td>
        <td width="60%" class="style9">: {{$kec->sandi_Lembaga}}</td>
    </tr>
    <tr>
        <td width="2%" class="style9">3.</td>
        <td width="30%" class="style9">Alamat Lengkap</td>
        <td width="60%" class="style9"></td>
    </tr>
    <tr>
        <td width="2%" class="style9"> </td>
        <td width="30%" class="style9">a. Alamat</td>
        <td width="60%" class="style9">: {{$kec->alamat_kec}} </td>
    </tr>
    <tr>
        <td width="2%" class="style9"> </td>
        <td width="30%" class="style9">b. Kelurahan/Desa</td>
        <td width="60%" class="style9">: {{$kec->desa_kec}} </td>
    </tr>
    <tr>
        <td width="2%" class="style9"> </td>
        <td width="30%" class="style9">c. Kecamatan</td>
        <td width="60%" class="style9">: {{$kec->nama_kec}}</td>
    </tr>
    <tr>
        <td width="2%" class="style9"> </td>
        <td width="30%" class="style9">d. Kabupaten/Kota</td>
        <td width="60%" class="style9">: {{$kab->nama_kab}}</td>
    </tr>
    <tr>
        <td width="2%" class="style9"> </td>
        <td width="30%" class="style9">e. Provinsi</td>
        <td width="60%" class="style9">: {{$kec->provinsi}}</td>
    </tr>
    <tr>
        <td width="2%" class="style9"> </td>
        <td width="30%" class="style9">f. Kode Pos</td>
        <td width="60%" class="style9">: {{$kec->kode_pos}}</td>
    </tr>
    <tr>
        <td width="2%" class="style9">4.</td>
        <td width="30%" class="style9">Telepon dan Fax</td>
        <td width="60%" class="style9">: {{$kec->telpon_kec}}</td>
    </tr>
    <tr>
        <td width="2%" class="style9">5.</td>
        <td width="30%" class="style9">Email</td>
        <td width="60%" class="style9">: {{$kec->email_kec}}</td>
    </tr>
    <tr>
        <td width="2%" class="style9">6.</td>
        <td width="30%" class="style9">No. dan Tanggal Izin Usaha</td>
        <td width="50%" class="style9">: {{$kec->ijin_usaha}}</td>
    </tr>
    <tr>
        <td width="2%" class="style9">7.</td>
        <td width="30%" class="style9">Dasar Pencatatan</td>
        <td width="60%" class="style9">: {{$kec->dasar_catat}}</td>
    </tr>
    <tr>
        <td width="2%" class="style9">8.</td>
        <td width="30%" class="style9">Pemegang Saham</td>
        <td width="60%" class="style9">: </td>
    </tr>
    <tr>
        <td width="2%" class="style9"></td>
        <td colspan="2">
            <br>
                
            <table border="0" width="100%">
                <tr>
                    <td class="style9" width="33%">&nbsp; </td>
                    <td colspan="2" class="style9 align-center">Kepemilikan Saham **)</td>
                </tr>
                <tr>
                    <th class="style9 bottom top align-center">Nama Pemegang Saham</th>
                    <th class="style9 bottom top align-center" width="33%">Rupiah</th>
                    <th class="style9 bottom top align-center" width="33%">Persentase(%)</th>
                </tr>

                @php
                    $jrp_saham1 = 0;
                    $pros_saham1 = 0;
                @endphp
                <tr>
                    <td class="style9 bottom align-center">&nbsp;</td>
                    <td class="style9 bottom align-center" width="33%">&nbsp;</td>
                    <td class="style9 bottom align-center" width="33%">&nbsp;</td>
                </tr>
                <tr>
                    <td class="style9 bottom align-center">&nbsp;</td>
                    <td class="style9 bottom align-center" width="33%">&nbsp;</td>
                    <td class="style9 bottom align-center" width="33%">&nbsp;</td>
                </tr>
                <tr>
                    <td class="style9 bottom align-center">&nbsp;</td>
                    <td class="style9 bottom align-center" width="33%">&nbsp;</td>
                    <td class="style9 bottom align-center" width="33%">&nbsp;</td>
                </tr>
                <tr>
                    <td class="style9 bottom align-center">&nbsp;</td>
                    <td class="style9 bottom align-center" width="33%">&nbsp;</td>
                    <td class="style9 bottom align-center" width="33%">&nbsp;</td>
                </tr>
                <tr>
                    <td class="style9 bottom align-center">&nbsp;</td>
                    <td class="style9 bottom align-center" width="33%">&nbsp;</td>
                    <td class="style9 bottom align-center" width="33%">&nbsp;</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td width="2%" class="style9">&nbsp;</td>
    </tr>
    <tr>
        <td width="2%" class="style9">9.</td>
        <td width="30%" class="style9">Direksi dan Komisaris :</td>
        <td width="60%" class="style9"> </td>
    </tr>
    <tr>
        <td width="2%" class="style9"></td>
        <td colspan="2">
            
            <table border="0" width="100%">
                <tr>
                    <th class="style9 bottom top align-center" width="50%">Nama Direksi</th>
                    <th class="style9 bottom top align-center" width="50%">Jabatan</th>
                    <th>&nbsp;</th>
                    <th class="style9 bottom top align-center" width="50%">Nama Komisaris</th>
                    <th class="style9 bottom top align-center" width="50%">Jabatan</th>
                </tr>

                <tr>
                    <td class="style9 " width="50%">&nbsp;</td>
                    <td class="style9 " width="50%">&nbsp;</td>
                </tr>

                <tr>
                    <td class="style9 " width="50%">&nbsp;</td>
                    <td class="style9 " width="50%">&nbsp;</td>
                </tr>

                <tr>
                    <td class="style9 " width="50%">&nbsp;</td>
                    <td class="style9 " width="50%">&nbsp;</td>
                </tr>

                <tr>
                    <td class="style9 " width="50%">&nbsp;</td>
                    <td class="style9 " width="50%">&nbsp;</td>
                </tr>

                <tr>
                    <td class="style9 " width="50%">&nbsp;</td>
                    <td class="style9 " width="50%">&nbsp;</td>
                </tr>

            </table>
        </td>
    </tr>
    <tr>
        <td width="2%" class="style9"></td>
        <td width="40%" class="style9">*) coret yang tidak perlu
            <br> **) hanya diisi untuk Lembaga berbentuk PT
        </td>
</table>
<table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
    <tr>
        <td colspan="3">&nbsp;</td>
        <td align="center">
            {{ $kec->nama_kec }}, {{ Tanggal::tglOjk($tgl_kondisi) }}
        </td>
    </tr>
    <tr>
        <td width="10" align="center"> &nbsp; </td>
        <td width="70" align="center"></td>
        <td width="60" align="center"> &nbsp; </td>
        <td width="50" align="center"> 
            {{ $nama_lembaga }}
        </td>
    </tr>
    <tr>
        <td colspan="4" height="30">&nbsp;</td>
    </tr>
    <tr>
        <td width="10" align="center">&nbsp;</td>
        <td width="70" align="center"></td>
        <td width="50" align="center"></td>
        <td width="60" align="center">
            <strong><u>{{ $dir->namadepan ?? ''}} {{$dir->namabelakang ?? ''}}</u></strong>
        </td>        
    </tr> 
    <tr>
        <td width="10" align="center">&nbsp;</td>
        <td width="70" align="center"></td>
        <td width="50" align="center"></td>
        <td width="60" align="center">
            <strong>{{ $kec->ttd_mengetahui_lap == '2' ? 'Direktur' : $kec->sebutan_level_1 }}</strong>
        </td>
        
    </tr> 
</table>
                        
@endsection
