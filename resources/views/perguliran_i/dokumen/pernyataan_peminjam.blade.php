@php
    use App\Utils\Tanggal;
@endphp

@extends('perguliran_i.dokumen.layout.base')

@section('content')
    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 12px;">
        <tr class="b">
            <td colspan="3" align="center">
                <div style="font-size: 18px;">
                    <b>SURAT PERNYATAAN PEMINJAM</b>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="3" height="5"></td>
        </tr>
    </table>
    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 12px; text-align: justify;">
        <tr>
            <td colspan="3">Yang bertanda tangan di bawah ini,</td>
        </tr>
        <tr>
            <td width="100">Nama Lengkap</td>
            <td width="5" align="right">:</td>
            <td>{{ $pinkel->anggota->namadepan }}</td>
        </tr>
        <tr>
            <td>Jenis Kelamin</td>
            <td align="right">:</td>
            <td>{{ $pinkel->anggota->jk }}</td>

        </tr>
        <tr>
            <td>Tempat, Tangal lahir</td>
            <td align="right">:</td>
            <td>{{ $pinkel->anggota->tempat_lahir }}
                {{ Tanggal::tglLatin($pinkel->anggota->tgl_lahir) }}
            </td>
        </tr>
        <tr>
            <td>NIK</td>
            <td align="right">:</td>
            <td>{{ $pinkel->anggota->nik }}</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td align="right">:</td>
            <td>{{ $pinkel->anggota->d->nama_desa }}</td>
        </tr>
        <tr>
            <td>Jenis Usaha</td>
            <td align="right">:</td>
            <td>{{ $pinkel->anggota->usaha }}</td>
        </tr>
        <tr>
            <td width="100" colspan="3">
                <div>
                    Dengan ini menyatakan dengan sebenarnya dan pernyataan ini tidak dapat ditarik kembali, bahwa:
                </div>

                <ol>
                    <li>
                        Saya selaku Pemanfaat pinjaman individu pada Kecamatan {{ $kec->nama_kec }}
                        melalui {{ $pinkel->anggota->d->sebutan_desa->sebutan_desa }} {{ $pinkel->anggota->d->nama_desa }},
                        Kecamatan {{ $kec->nama_kec }}
                        {{ $nama_kabupaten }}, benar-benar telah meminjam uang sebesar Rp. _____________________,
                        dengan jaminan berupa barang sebagai berikut :
                        <ul style="list-style: disc;">
                            <li>
@php
    // Dekode JSON, jika gagal, jadikan string apa adanya.
    $jaminan = json_decode($pinkel->jaminan, true) ?? $pinkel->jaminan;
@endphp

<table border="0" width="100%" cellspacing="0" cellpadding="5" style="font-size: 12px; border-collapse: collapse;">
    @if (is_array($jaminan))
        @foreach ($jaminan as $key => $value)
            <tr>
                <td height="12" width="80">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                <td width="10" align="center">:</td>
                <td>
                    @if (is_numeric($value))
                        Rp {{ number_format($value, 0, ',', '.') }}
                    @else
                        {{ $value }}
                    @endif
                </td>
            </tr>
        @endforeach
    @else
        <tr>
            <td height="12" width="80">Nama barang</td>
            <td width="10" align="center">:</td>
            <td>
                <b>{{ $jaminan }}</b>
            </td>
        </tr>
                                        <tr>
                                            <td height="12" width="80">Nilai Jual</td>
                                            <td width="10" align="center">:</td>
                                            <td>
                                                <b>Rp. _______________________</b>
                                            </td>
                                        </tr>
    @endif
</table>

                                </li>
                            @for ($i = 0; $i < 3; $i++)
                                <li>
                                    <table border="0" width="100%" cellspacing="0" cellpadding="0"
                                        style="font-size: 12px;">
                                        <tr>
                                            <td height="12" width="80">Nama barang</td>
                                            <td width="10" align="center">:</td>
                                            <td>
                                                <b>________________________________________________________</b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="12" width="80">Nilai Jual</td>
                                            <td width="10" align="center">:</td>
                                            <td>
                                                <b>Rp. _______________________</b>
                                            </td>
                                        </tr>
                                    </table>
                                </li>
                            @endfor
                        </ul>
                    </li>
                    <li>
                        Barang yang saya jaminkan adalah benar benar milik saya sendiri dan bukan milik orang lain, dan saya bersedia bertanggung jawab secara hukum apabila di kemudian hari terbukti bahwa barang jaminan tersebut bukan milik saya sendiri,
                    </li>
                    <li>
                        Saya berkewajiban merawat dan melindungi barang jaminan tersebut dan tidak akan menjual,
                        menggadaikan, dan/atau memindahtangankan kepada pihak lain sebelum kredit/pinjaman saya tersebut
                        lunas,
                    </li>
                    <li>
                        Apabila terjadi kemacetan atas kredit saya tersebut, saya bersedia menyerahkan barang jaminan
                        tersebut kepada pihak yang berwenang, guna menyelesaikan kredit/pinjaman saya kepada
                        {{ $kec->nama_lembaga_sort }} Kecamatan {{ $kec->nama_kec }},
                    </li>
                    <li>
                        Saya berjanji akan mengembalikan pinjaman saya tersebut sesuai dengan peraturan yang ada di
                        {{ $kec->nama_lembaga_sort }},
                    </li>
                    <li>
                        Apabila di kemudian hari saya melanggar isi dari surat pernyataan ini, maka saya bersedia dilaporkan
                        kepada pihak yang berwajib dan/atau diproses secara hukum.
                    </li>
                    <li>
                        Jika dikemudian hari terjadi force majeure seperti banjir, gempa bumi, tanah longsor, petir, angin
                        topan, kebakaran, huru-hara, kerusuhan, pemberontakan, dan perang atau saya berhalangan tetap
                        seperti sakit atau meninggal dunia yang mengakibatkan tidak dapat terpenuhinya kewajiban saya sesuai
                        poin 5 (lima) diatas, maka sisa angsuran akan ditanggung oleh ahli waris.
                    </li>
                </ol>

                <div>
                    Demikian surat pernyataan ini saya buat dengan sebenarnya dan dengan penuh kesadaran serta rasa tanggung
                    jawab.


                </div>
            </td>
        </tr>
    </table>
    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 12px;">
        <tr>
            <td colspan="3" height="20">&nbsp;</td>
        </tr>
        <tr>
            <td width="33%">&nbsp;</td>
            <td width="33%">&nbsp;</td>
            <td width="33%" align="center">{{ $kec->nama_kec }}, _________________</td>
        </tr>
        <tr>
            <td align="center">Saksi 1</td>
            <td align="center">Saksi 2</td>
            <td align="center">Yang Menyatakan</td>
        </tr>
        <tr>
            <td colspan="3" height="50">&nbsp;</td>
        </tr>
        <tr>
            <td align="center">
                <b>____________________</b>
            </td>
            <td align="center">
                <b>____________________</b>
            </td>
            <td align="center">
                <b>____________________</b>
            </td>
        </tr>
    </table>
@endsection
