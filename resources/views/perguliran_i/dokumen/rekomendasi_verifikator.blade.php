@php
    use App\Utils\Tanggal;
@endphp

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ ucwords(str_replace('_', ' ', $judul)) }}</title>
    <style>
        * {
            font-family: Arial, Helvetica, sans-serif;
        }

        html {
            margin: 75.59px;
            margin-left: 94.48px;
        }

        ul,
        ol {
            margin-left: -10px;
            page-break-inside: auto !important;
        }

        header {
            position: fixed;
            top: -10px;
            left: 0px;
            right: 0px;
        }

        footer {
            position: fixed;
            bottom: -50px;
            left: 0px;
            right: 0px;
        }

        table tr th,
        table tr td {
            padding: 2px 4px;
        }

        table tr td table tr td {
            padding: 0 !important;
        }

        .break {
            page-break-after: always;
        }

        li {
            text-align: justify;
        }

        .l {
            border-left: 1px solid #000;
        }

        .t {
            border-top: 1px solid #000;
        }

        .r {
            border-right: 1px solid #000;
        }

        .b {
            border-bottom: 1px solid #000;
        }
    </style>
</head>

<body>
    <main>
        <table border="0" width="100%" cellspacing="0" cellpadding="0"
            style="font-size: 11px; position: relative; top: -20px;">
            <tr class="b">
                <td align="center">
                    <img src="../storage/app/public/logo/{{ $logo }}" width="70"
                        alt="{{ $logo }}" style="margin-bottom: 8px;">
                </td>
                <td align="center">
                    <div style="font-size: 18px;">
                    {{ $kec->nama_lembaga_sort }} {{ strtoupper($nama_kabupaten) }}
                    </div>
                    <div style="font-size: 18px;">
                        {{ strtoupper($kec->sebutan_kec) }} {{ strtoupper($kec->nama_kec) }}
                    </div>
                    <div align="center"style="font-size: 12px;">
                 Alamat:...........................................................................

                      
                    </div>
                </td>
            </tr>
        </table>

        <table border="0" width="85%" align="center"cellspacing="0" cellpadding="0" style="font-size: 12px;">
            <tr>
                <td align="center">
                    <div style="font-size: 18px;">
                        <b>REKOMENDASI HASIL VERIFIKASI/ANALISA KREDIT</b>
                    </div>
                    <div style="font-size: 12px;">
                       &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;   Nomor:..................................... &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                         &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                       
                    </div>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td height="5"></td>
            </tr>
        </table>
        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 12px;">
            <tr>
                <td colspan=5" align="justify">
                Setelah dilakukan pengkajian dokumen permohonan kredit dan Analisa lapangan atas permohonan kredit / permohonan pinjaman  sebagai  berikut :
                </td>
            </tr>

            <tr>
                <td width="30">&nbsp;</td>
                <td width="120" style="vertical-align: top;">Nama Pemohon Kredit  </td>
                <td width="5" align="center">:</td>
                <td>{{ $pinkel->anggota->namadepan }}  </td>
            </tr>
            <tr>
              <td width="30">&nbsp;</td>
                <td width="120" style="vertical-align: top;">N I K</td>
                <td align="center">:</td>
                <td>{{ $pinkel->anggota->nik }}  </td>
            </tr>
            <tr>
              <td width="30">&nbsp;</td>
                <td width="120" style="vertical-align: top;">Alamat</td>
                <td align="center">:</td>
                <td>
                {{ $pinkel->anggota->d->sebutan_desa->sebutan_desa }}    
                {{ $pinkel->anggota->d->nama_desa }}</td>
            </tr>
            <tr>
            <td width="30">&nbsp;</td>
                <td width="120" style="vertical-align: top;">Tanggal dan Nomor Permohonan Kredit</td>
                <td align="center">:</td>
                <td>____________/____________</td>
            </tr>
            <tr>
            <td width="30">&nbsp;</td>
                <td width="120" style="vertical-align: top;">Nilai Permohonan </td>
                <td align="center">:</td>
                <td> Rp. {{number_format($pinkel->proposal)}}.-</td>
            </tr>
          <tr>
            <td>&nbsp;</td>
          </tr>
            <tr>
                <td align="justify" colspan="5">
                Dengan ini selaku Tim Verifikasi/Analis Kredit menyatakan bahwa Permohonan Kredit sebagaimana dimaksud diatas dinyatakan LAYAK dengan rincian rekomendasi kredit sebagai berikut :
                </td>
            </tr>
            <tr>
                <td width="30">&nbsp;</td>
                <td width="120" style="vertical-align: top;">Nilai Rekomendasi  </td>
                <td width="5" align="center">:</td>
                <td> Rp. {{number_format($pinkel->verifikasi)}}.-</td>
            </tr>
            <tr>
              <td width="30">&nbsp;</td>
                <td width="120" style="vertical-align: top;">Jangka Waktu Pinjaman</td>
                <td align="center">:</td>
                <td>{{$pinkel->jangka}}  bulan</td>
            </tr>
            <tr>
              <td width="30">&nbsp;</td>
                <td width="120" style="vertical-align: top;">Jenis dan besaran Jasa</td>
                <td align="center">:</td>
                <td>{{ $pinkel->jasa->nama_jj }}
                {{ round($pinkel->pros_jasa / $pinkel->jangka, 2) }}% per bulan
                </td>
            </tr>
            <tr>
            <td width="30">&nbsp;</td>
                <td width="120" style="vertical-align: top;">Sistem Angsuran Pokok</td>
                <td align="center">:</td>
                <td> {{ $pinkel->sis_pokok->nama_sistem }}</td>
            </tr>
            <tr>
            <td width="30">&nbsp;</td>
                <td width="120" style="vertical-align: top;">Sistem Angsuran Jasa </td>
                <td align="center">:</td>
                <td>{{ $pinkel->sis_jasa->nama_sistem }}</td>
            </tr>
            <tr>
            <td>&nbsp;</td>
                </tr>
            <tr>
                <td align="justify" colspan="5">

                Demikian rekomendasi ini kami terbitkan untuk dapat ditindaklanjuti sebagaimana mestinya oleh Tim Pemutus Pinjaman Bersama Bagian Kredit {{ $kec->nama_lembaga_sort }} {{ $kec->sebutan_kec }} {{ $kec->nama_kec }}.

                </td>
            </tr>
        </table>

        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 12px;">
            <tr>
                <td width="33%" height="30">&nbsp;</td>
                <td width="33%">&nbsp;</td>
                <td width="33%">&nbsp;</td>
            </tr>
            <tr>
            <td width="200"align="center"colspan="1">&nbsp;</td>
                <td align="center">{{ $kec->nama_kec }},{{ Tanggal::tglLatin($pinkel->tgl_verifikasi) }}</td>
            </tr>
            <tr>
            <td width="200"align="center"colspan="1">&nbsp;</td>
                <td align="center">
                Verifikatis/Analis Kredit                </td>
            </tr>
            <tr>
                <td colspan="3" height="40">&nbsp;</td>
            </tr>
            <tr>
            <td width="200"align="center"colspan="1">&nbsp;</td>
                <td align="center">
                    <u>
                        <p>({{$dir->namadepan }} {{$dir->namabelakang }})<br>
                    Ketua Tim
                   </p>
                    </u>
                    @if ($pinkel->anggota->d->nip)
                        <div><small>NIP. {{ $pinkel->anggota->d->nip }}</small></div>
                    @endif
                </td>
            </tr>
        </table>
    </main>
</body>
