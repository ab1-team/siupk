@php
    use App\Utils\Tanggal;
    use Carbon\Carbon;
    Carbon::setLocale('id');
    $waktu = date('H:i');
    $tempat = 'Kantor UPK';
    $wt_cair = explode('_', $pinkel->wt_cair);
    if (count($wt_cair) == 1) {
        $waktu = $wt_cair[0];
    }
    if (count($wt_cair) == 2) {
        $waktu = $wt_cair[0];
        $tempat = $wt_cair[1] ?? ' . . . . . . . ';
    }
    $redaksi_spk = str_replace(' <ol> ', '', str_replace(' </ol> ', '', $kec->redaksi_spk));
    $redaksi_spk = str_replace(' <ul> ', '', str_replace(' </ul> ', '', $redaksi_spk));
@endphp
@extends('perguliran_i.dokumen.layout.base')
@section('content')
    <style>
        /* styles.css */
        .centered-text {
            font-size: 10px;
            text-align: center;
            text-align: justify;
        }
    </style>
    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 12px;">
        <tr>
            <td colspan="3" align="center">
                <div style="font-size: 14px;">
                    <b> SURAT PERJANJIAN KREDIT (SPK) </b>
                </div>
                <div style="font-size: 12px;">
                    Nomor: <strong>{{ $pinkel->spk_no }}</strong>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="3" height="5"> </td>
        </tr>
    </table>
    <div class="centered-text">
        Dengan memohon rahmat Tuhan Yang Maha Kuasa serta kesadaran akan cita-cita luhur pemberdayaan masyarakat desa untuk
        mencapai kemajuan ekonomi dan kemakmuran bersama, pada hari ini <strong>{{ Tanggal::namaHari($pinkel->tgl_cair) }}</strong> tanggal
        <strong>{{ $keuangan->terbilang(Tanggal::hari($pinkel->tgl_cair)) }}</strong> bulan <strong>{{ Tanggal::namaBulan($pinkel->tgl_cair) }}</strong>
        tahun
        <strong>{{ $keuangan->terbilang(Tanggal::tahun($pinkel->tgl_cair)) }}</strong>, bertempat di <strong>{{ $tempat }}</strong> kami yang bertanda
        tangan dibawah ini;
    </div>
    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10px;">
        <tr>
            <td width="5"> &nbsp; </td>
            <td width="90"> Nama Lengkap </td>
            <td width="10" align="center"> : </td>
            <td> <strong>{{ $dir->namadepan }}</strong> <strong>{{ $dir->namabelakang }}</strong> </td>
        </tr>
        <tr>
            <td width="5"> &nbsp; </td>
            <td> Jabatan </td>
            <td align="center"> : </td>
            <td> <strong>{{ $kec->sebutan_level_1 }}</strong> <strong>{{ $kec->nama_lembaga_sort }}</strong> </td>
        </tr>
        <tr>
            <td width="5"> &nbsp; </td>
            <td> NIK </td>
            <td align="center"> : </td>
            <td> <strong>{{ $dir->nik }}</strong> </td>
        </tr>
        <tr>
            <td width="5"> &nbsp; </td>
            <td> Alamat </td>
            <td align="center"> : </td>
            <td> <strong>{{ $kec->alamat_kec }}</strong> </td>
        </tr>
    </table>
    <div class="centered-text">
        Dalam hal ini bertindak untuk dan atas nama Pengurus <strong>{{ $kec->nama_lembaga_sort }}</strong> <strong>{{ $kec->sebutan_kec }}</strong>
        <strong>{{ $kec->nama_kec }}</strong> selaku pengelola pelayanan
        kredit untuk <strong>{{ $pinkel->jpp->deskripsi_jpp }}</strong>
        (<strong>{{ $pinkel->jpp->nama_jpp }}</strong>) di <strong>{{ $kec->sebutan_kec }}</strong>
        <strong>{{ $kec->nama_kec }}</strong>, Selanjutnya disebut
        <b> Pihak Pertama </b> , dan
    </div>
    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10px;">
        <tr>
            <td width="5"> &nbsp; </td>
            <td width="90"> Nama Lengkap </td>
            <td width="10" align="center"> : </td>
            <td> <strong>{{ $pinkel->anggota->namadepan }}</strong> </td>
        </tr>
        <tr>
            <td width="5"> &nbsp; </td>
            <td> Jenis kelamin </td>
            <td align="center"> : </td>
            <td> <strong>{{ $pinkel->anggota->jk }}</strong> </td>
        </tr>
        <tr>
            <td width="5"> &nbsp; </td>
            <td> Tempat, tangal lahir </td>
            <td align="center"> : </td>
            <td> <strong>{{ $pinkel->anggota->tempat_lahir }}</strong>,
                <strong>{{ \Carbon\Carbon::parse($pinkel->anggota->tgl_lahir)->format('d F Y') }}</strong>
            </td>
        </tr>
        <tr>
            <td width="5"> &nbsp; </td>
            <td> NIK </td>
            <td align="center"> : </td>
            <td> <strong>{{ $pinkel->anggota->nik }}</strong> </td>
        </tr>
        <tr>
            <td width="5"> &nbsp; </td>
            <td> Berkedudukan di </td>
            <td align="center"> : </td>
            <td> <strong>{{ $pinkel->anggota->alamat }}</strong> </td>
        </tr>
    </table>
    <div class="centered-text">
        Dalam hubungan ini bertindak untuk dan atas nama diri sendiri yang menjadi bagian tidak terpisahkan dari dokumen
        perjanjian kredit ini, selanjutnya disebut PIHAK KEDUA.
    </div>
    <p class="centered-text">
        Pihak Pertama dan Pihak Kedua dalam kedudukan masing-masing seperti telah diterangkan diatas, Pada hari
        <strong>{{ \Carbon\Carbon::parse($pinkel->anggota->tgl_cair)->format('d F Y') }}</strong>,
        bertempat di <strong>{{ $kec->nama_lembaga_sort }}</strong> <strong>{{ $kec->sebutan_kec }}</strong>
        <strong>{{ $kec->nama_kec }}</strong> dengan sadar dan
        sukarela menyatakan telah membuat perjanjian utang piutang dengan ketentuan-ketentuan yang telah disepakati bersama
        sebagai berikut :
    </p>
    <div style="text-align: center;">
        <b class="centered-text"> PASAL 1 </b>
        <ol class="centered-text">
            <li> <b> Pihak Pertama </b> dengan ini setuju memberikan kredit kepada <b> Pihak Kedua </b> uang sebesar <strong> Rp.
                {{ number_format($pinkel->alokasi) }}</strong> (<strong>{{ $keuangan->terbilang($pinkel->alokasi) }}</strong> Rupiah) Yaitu
                jumlah
                yang telah diputuskan dalam Rekomendasi rapat <b> Tim Pendanaan </b> mendasar pada surat Rekomendasi dari
                <b> Team Verifikasi </b> dan <strong>{{ $kec->nama_lembaga_sort }}</strong>, berdasarkan permohonan dari <b> Pihak Kedua </b> yang
                dilakukan secara perorangan sesuai Surat Permohonan kredit tanggal
                <strong>{{ Tanggal::tglLatin($pinkel->tgl_proposal) }}</strong>.
            </li>
            <li> <b> Pihak Pertama </b> telah menyerahkan uang kepada Pihak Kedua sebagai pinjaman sebesar<strong> Rp.
                {{ number_format($pinkel->alokasi) }}</strong> (<strong>{{ $keuangan->terbilang($pinkel->alokasi) }}</strong> Rupiah) tersebut secara tunai dan sekaligus kepada  <b> Pihak Kedua </b>  pada saat perjanjian ini dibuat dan ditanda tangani, sekaligus membuat dan/atau menandatangani bukti penerimaan uang (kwitansi) yang sah.
            </li>
            <li>
                Selanjutnya <b> Pihak Kedua </b>  mengaku telah menerima uang dalam jumlah sebagaimana yang diterangkan pada ayat 1 dan 2 diatas, yang berlaku sebagai Surat Pengakuan Utang secara perseorangan dan menjadi bagian yang tidak terpisahkan dari Surat Perjanjian Kredit (SPK) ini.
            </li>
        </ol>
    </div>
    <div style="text-align: center;">
        <b class="centered-text"> PASAL 2 </b>
        <h3 class="fa fa-align-center" aria-hidden="true" style="font-size: 10px;"> Jangka Waktu Pembayaran Utang dan Sistem Angsuran
            </i> </h3>
        <div class="centered-text">
            <ol class="centered-text">
                <li>Sejumlah utang dimaksud dalam Pasal 1 wajib dibayar lunas dalam jangka waktu  <strong>{{ $pinkel->jangka }} bulan</strong> ditambah jasa yang dihitung secara Flat sebesar<strong>{{ $pinkel->pros_jasa / $pinkel->jangka }} %</strong>  dari pokok utang oleh <b> Pihak Kedua </b> dengan sistim angsuran Pokok dibayar <strong>{{$pinkel->sis_pokok->deskripsi_sistem}}</strong> dan Jasa dibayar <strong>{{$pinkel->sis_jasa->deskripsi_sistem}}</strong>.
                </li>
                <li> <b> Pihak Kedua </b> wajib membayar angsuran sebagaimana Pasal 2 Ayat 1 dengan jumlah angsuran <strong>Rp. {{ number_format($pinkel->alokasi) }} ({{ $keuangan->terbilang($pinkel->alokasi) }} Rupiah)</strong> untuk angsuran Pokok ditambah Rp. <strong>{{ number_format($pinkel->alokasi * ($pinkel->pros_jasa  / 100)) }}
                    ({{ $keuangan->terbilang($pinkel->alokasi * ($pinkel->pros_jasa  / 100)) }} Rupiah)</strong> setiap bulan, selama  <strong>{{ $pinkel->jangka }} bulan</strong>, yang dimulai pada hari <strong>{{ Tanggal::namaHari($pinkel->tgl_cair) }}</strong>,
                <strong>{{ \Carbon\Carbon::parse($pinkel->anggota->tgl_cair)->translatedFormat('d F Y') }}</strong> dan sampai target pelunasan, sebagaimana jadwal angsuran terlampir yang menjadibagian tidak terpisahkan dari Surat Perjanjian Kredit (SPK) ini.
                </li>
            </ol>
        </div>
    </div>
    <br>
    <div style="text-align: center;">
        <b class="centered-text"> PASAL 3 </b>
        <h3 class="fa fa-align-center" aria-hidden="true" style="font-size: 10px;"> Barang Jaminan/Agunan </i> </h3>
        <div class="centered-text">
            <ol class="centered-text">
                <li>
                    Guna menjamin pembayaran utang dilakukan secara tertib sesuai rencana angsuran sebagaimana dimaksud dalam Pasal 2 dan agar dilakukan sebagaimana mestinya sesuai dengan perjanjian ini, maka berkaitan dengan barang jamainan berupa
                    @php
                        $jaminan = json_decode($pinkel->jaminan, true);
                    @endphp

@if (is_array($jaminan) || is_object($jaminan))
                        : <table>
                            @foreach ($jaminan as $key => $value)
                                @if ($key === 'jenis_jaminan')
                                    @continue
                                @endif
                                <tr>
                                    <td>{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                    <td>:</td>
                                    <td>{{  ucwords($value) }}</td>
                                </tr>
                            @endforeach
                        </table>
                    @else
                         <STRONG> {{$pinkel->jaminan}} </STRONG>
                    @endif
                    yang dijadikan agunan oleh <b> Pihak Kedua </b> kepada <b> Pihak Pertama </b> yang selanjutnya dibuatkan bukti penyerahan barang jaminan/agunan dari <b> Pihak Kedua </b> kepada <b> Pihak Pertama </b> dan selanjutnya bisa diambil kembali apabila sudah dinyatakan lunas atas angsuran pokok maupun jasa dan segala kewajiban yang timbul dari pelayanan atas pelayanan kredit ini terpenuhi.
                </li>
                <li>
                    <b> Pihak Pertama </b> wajib melakukan penyimpanan/pengamanan atas barang jaminan/agunan sebagaimana dimaksud dalam Pasal 3 Ayat 1 secara layak dan sebagaimana mestinya serta menyerahkan kembali kepada <b> Pihak Kedua </b> apabila <b> Pihak Kedua </b> sudah dinyatakan lunas atas seluruh kewajiban angsuran pokok dan jasa beserta segala kewajiban lain yang timbul dalam pelayanan dan penanganan kredit ini.
                </li>
            </ol>
        </div>
    </div>
    <br> <br> <br><br> <br>
    <div style="text-align: center;">
        <b class="centered-text"> PASAL 4 </b>
        <h3 class="fa fa-align-center" aria-hidden="true" style="font-size: 10px;"> Pengalihan Kuasa atas Barang Jaminan/Agunan </i>
        </h3>
        <div class="centered-text">
        Dalam hal <b> Pihak Kedua </b> tidak memenuhi kewajiban angsuran kepada <b> Pihak Pertama </b> sebagaimana dimaksud dalam jadwal angsuran <b>Pasal 2</b>, maka <b> Pihak Kedua </b>  secara suka rela akan melakukan pengalihan kekuasaan atas barang jaminan/agunan dengan mekanisme :
        </div>
        <ol class="centered-text">
            <li> <b> Pihak Kedua </b> menyerahkan barang jaminan/agunan yang <b>SAH </b> kepada <b> Pihak Pertama </b> sebagaimana tersebut dalam  <b>Pasal 3</b> berikut dengan segala hak dan kepentingan yang sekarang atau dikemudian hari akan diperoleh dari barang jaminan tersebut.
            </li>
            <li> <b> Pihak Kedua </b>  memberikan persetujuan untuk dilakukan perikatan agunan sesuai dengan ketentuan peraturan perundang-undangan yang berlaku sesuai dengan jenis barang jaminan/agunan yang diberikan
            </li>
            <li><b> Pihak Kedua </b> akan menyerahkan bukti kepemilikan, izin-izin atau dokumen-dokumen yang berkaitan dengan agunan serta akta-akta berkenaan dengan pengikatan barang yang diagunkan sebagaimana tersebut dalam   <b>Pasal 3</b> secara sukarela untuk dikuasai oleh <strong>{{ $kec->nama_lembaga_sort }}</strong> sampai kredit dinyatakan lunas.
            </li>
            <li>Apabila karena sebab apapun, Agunan yang diserahkan menjadi tidak sah atau berkurang nilainya dan/atau nilainya tidak lagi sesuai dengan kewajiban <b> Pihak Kedua </b>, maka <b> Pihak Kedua </b> wajib menyerahkan Agunan Pengganti yang Sah milik <b> Pihak Kedua </b> dan sedang tidak dalam keadaan sengketa dengan nilai agunan yang dapat memenuhi kewajiban <b> Pihak Kedua </b> dan dapat disetujui oleh <strong>{{ $kec->nama_lembaga_sort }}</strong>.
            </li>
        </ol>
    </div>
    <div style="text-align: center;">
        <b class="centered-text"> PASAL 5 </b>
        <h3 class="fa fa-align-center" aria-hidden="true" style="font-size: 10px;"> Penerbitan Dokumen Pengalihan Kuasa Khusus </i>
        </h3>
        <div class="centered-text">
        Dalam hal terjadi kondisi sebagaimana dimaksud dalam <b>Pasal 4</b> maka selanjutnya  <b> Pihak Kedua </b>  secara sukarela akan membuat dan/atau menandatangani dokumen pengalihan kekauasaan atas barang jaminan/agunan dengan ketentuan sebagai berikut :
        </div>
        <ol class="centered-text">
            <li>Pihak Kedua bersedia membuat dan/atau menandatangani dokumen surat kuasa khusus kepada Pihak Pertama untuk mengambil dan menguasai obyek yang disebutkan sebagai barang jaminan atau agunan dimaksud dalam Pasal 3 juncto Pasal 4 secara sah dan memiliki hak sepenuhnya untuk menjual atau melakukan lelang atau memiliki sendiri atas barang jaminan/agunan tersebut dalam rangka melunasi hutang Pihak Kedua .
            </li>
            <li>Kuasa yang diberikan oleh Pihak Kedua kepada Pihak Pertama didalam atau berdasarkan perjanjian ini, merupakan bagian yang  tidak terpisahkan dari perjanjian ini, dan tidak dapat ditarik kembali serta tidak akan berakhir karena meninggal dunianya Pihak Kedua atau karena sebab apapun juga.
            </li>
            <li>Dalam rangka menjalankan Kuasa Khusus Penjualan dan/atau melakukan pelelangan barang jaminan/agunan sebagaimana disebut dalam Pasal 3 juncto Pasal 4, maka nilai penjualan dan/atau pelelangan setelah dikurangi biaya eksekusi barangjaminan/agunan beserta biaya yang timbul dari proses penjualan/pelelangan barang jaminan/agunan akan diperhitungkan sebagai kelebihan atau kekurangan bayar yang tetap menjadi hak/kewajiban Pihak Kedua .
            </li>
        </ol>
    </div>
    <div style="text-align: center;">
        <b class="centered-text"> PASAL 6 </b>
        <h3 class="fa fa-align-center" aria-hidden="true" style="font-size: 10px;"> Penyelesaian Perselisihan </i> </h3>
        <ol class="centered-text">
            <li> Apabila ada hal-hal yang tidak atau belum diatur dalam perjanjian ini dan juga jika terjadi perbedaan
                penafsiran atas seluruh atau sebagian dari perjanjian ini maka kedua belah pihak sepakat untuk
                menyelesaikannya secara musyawarah untuk mufakat. </li>
            <li> Jika penyelesaian secara musyawarah untuk mufakat juga ternyata tidak menyelesaikan perselisihan tersebut
                maka perselisihan tersebut akan diselesaikan secara hukum yang berlaku di Indonesia dan oleh karena itu
                kedua belah pihak setuju menunjuk Pengadilan Negeri <strong>{{ $nama_kab }}</strong> sebagai upaya hukum dalam
                menyelesaikan persengketaan tersebut. </li>
        </ol>
    </div>
    <div style="text-align: center;">
        <b class="centered-text"> PASAL 7 </b>
        <h3 class="fa fa-align-center" aria-hidden="true" style="font-size: 10px;"> Lain - Lain
            </i> </h3>
        <div class="centered-text">
            Hal-hal yang belum atau belum cukup diatur dalam perjanjian ini akan diatur lebih lanjut dalam bentuk surat
            menyurat dan atau addendum perjanjian yang ditandatangani oleh para pihak yang merupakan satu kesatuan dan
            bagian yang tidak terpisahkan dari perjanjian ini.
        </div>
    </div>
    <div style="text-align: center;" style="font-size: 10px;">
        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10px;" class="p0">
            <tr>
                <td style="padding: 0px !important;">
                    <table class="p0" border="0" width="100%" cellspacing="0" cellpadding="0"
                        style="font-size: 10px;">
                        <br>
                        <tr>
                            <td style="padding: 0px !important;">
                                <div style="text-align: center;">
                                    <b class="centered-text"> PASAL 8 </b>
                                    <h3 class="fa fa-align-center" aria-hidden="true" style="font-size: 10px;">
                                        Penyelesaian
                                        Perselisihan </i> </h3>
                                </div>
                                <div class="centered-text">
                                    Perjanjian Hutang Piutang uang ini dibuat rangkap 2 (dua) di atas kertas bermaterai
                                    cukup untuk masing-masing pihak yang mempunyai kekuatan hukum yang sama dan ditanda
                                    tangani oleh kedua belah pihak dalam keadaan sehat jasmani dan rohani, serta tanpa
                                    unsur paksaan dari pihak manapun.
                                </div>
                            </td>
                        </tr>
                    </table>
                    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10px; font-family: Arial, Helvetica, sans-serif;"
                        class="p">
                        <tr>
                            <td style="font-size: 10px; font-family: Arial, Helvetica, sans-serif;">
                                {!! $ttd !!}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
@endsection
