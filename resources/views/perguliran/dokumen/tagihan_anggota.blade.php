@php
    use App\Utils\Tanggal;
@endphp

@extends('perguliran.dokumen.layout.base')

@section('content')
    @foreach ($pinkel->pinjaman_anggota as $pinj)
        @php
            $tgl_cair = $pinj->tgl_cair ?: ($pinkel->tgl_cair ?: date('Y-m-d'));
            $jangka = $pinj->jangka ?: $pinkel->jangka;
            $pros_jasa = $pinj->pros_jasa ?: $pinkel->pros_jasa;
            $pros_jasa_bulan = $jangka > 0 ? $pros_jasa / $jangka : 0;
            $nama_sistem = $pinj->sis_pokok->nama_sistem ?? ($pinkel->sis_pokok->nama_sistem ?? '-');
        @endphp

        @if ($loop->iteration > 1)
            <div class="break">&nbsp;</div>
        @endif

        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 12px;">
            <tr>
                <td width="50">Nomor</td>
                <td width="10" align="center">:</td>
                <td colspan="2">
                    <b>__________/{{ Tanggal::tglRomawi(date('Y-m-d')) }}</b>
                </td>
            </tr>
            <tr>
                <td>Sifat</td>
                <td align="center">:</td>
                <td colspan="2">
                    <b>Penting dan Rahasia</b>
                </td>
            </tr>
            <tr>
                <td>Perihal</td>
                <td align="center">:</td>
                <td colspan="2">
                    <b>Surat Tagihan</b>
                </td>
            </tr>
            <tr>
                <td colspan="3">&nbsp;</td>
                <td align="left" width="160">
                    <div>KEPADA YTH.</div>
                    <div style="font-weight: bold;">
                        {{ $pinj->anggota->namadepan }}
                    </div>
                    <div>
                        {{ $pinj->anggota->alamat }}
                        {{ $pinj->anggota->d->sebutan_desa->sebutan_desa }}
                        {{ $pinj->anggota->d->nama_desa }}
                    </div>
                    <div style="font-weight: bold;">Di</div>
                    <div style="font-weight: bold; text-align: center;">
                        {{ strtoupper($pinj->anggota->d->nama_desa) }}
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="4">&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan="3">
                    <div>Dengan hormat,</div>
                    <div style="text-align: justify;">
                        Mendasar kepada Surat Perjanjian Kredit ({{ $pinkel->jpp->nama_jpp }}) antara
                        {{ $pinj->anggota->namadepan }}, anggota kelompok
                        {{ $pinkel->kelompok->nama_kelompok }}, {{ $pinj->anggota->d->sebutan_desa->sebutan_desa }}
                        {{ $pinj->anggota->d->nama_desa }} dengan {{ $kec->nama_lembaga_sort }} Tanggal
                        {{ Tanggal::tglLatin($tgl_cair) }} dengan rincian pinjaman sebagai berikut ;
                    </div>
                    <table>
                        <tr>
                            <td width="10">1.</td>
                            <td width="140">Alokasi Pinjaman</td>
                            <td width="5">:</td>
                            <td>
                                <b>Rp. {{ number_format($pinj->alokasi) }}</b>
                            </td>
                        </tr>
                        <tr>
                            <td>2.</td>
                            <td>Tanggal Pencairan</td>
                            <td>:</td>
                            <td>
                                <b>{{ Tanggal::tglLatin($tgl_cair) }}</b>
                            </td>
                        </tr>
                        <tr>
                            <td>3.</td>
                            <td>Prosentase Jasa</td>
                            <td>:</td>
                            <td>
                                <b>{{ $pros_jasa_bulan }}% per Bulan</b>
                            </td>
                        </tr>
                        <tr>
                            <td>4.</td>
                            <td>Masa Angsuran</td>
                            <td>:</td>
                            <td>
                                <b>{{ $jangka }} Bulan</b>
                            </td>
                        </tr>
                        <tr>
                            <td>5.</td>
                            <td>Sistem Angsuran</td>
                            <td>:</td>
                            <td>
                                <b>{{ $nama_sistem }}</b>
                            </td>
                        </tr>
                    </table>

                    <div style="text-align: justify;">
                        dan mendasar pada catatan pembukuan kami sampai dengan diterbitkannya Surat Tagihan ini
                        masih tercatat memiliki tunggakan sebagai berikut ;
                    </div>

                    <table>
                        <tr>
                            <td width="10">1.</td>
                            <td width="140">Tunggakan Pokok</td>
                            <td width="5">:</td>
                            <td>
                                <b>Rp. __________</b>
                            </td>
                        </tr>
                        <tr>
                            <td>2.</td>
                            <td>Tunggakan Jasa</td>
                            <td>:</td>
                            <td>
                                <b>Rp. __________</b>
                            </td>
                        </tr>
                        <tr>
                            <td>3.</td>
                            <td><b>Total Tunggakan (Pokok+jasa)</b></td>
                            <td>:</td>
                            <td>
                                <b>Rp. __________</b>
                            </td>
                        </tr>
                    </table>

                    <p style="text-align: justify;">
                        Demikian surat ini kami sampaikan, apabila terjadi perbedaan hasil perhitungan angsuran/
                        tunggakan mohon untuk melakukan klarifikasi dengan {{ $kec->nama_lembaga_sort }} dan
                        terima kasih untuk segera melakukan pelunasan tunggakan pinjamannya
                    </p>
                </td>
            </tr>
        </table>

        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 12px;">
            <tr>
                <td colspan="2" height="24">&nbsp;</td>
            </tr>
            <tr>
                <td width="50%">&nbsp;</td>
                <td width="50%" align="center">
                    {{ $kec->nama_kec }}, {{ Tanggal::tglLatin(date('Y-m-d')) }}
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td align="center">{{ $kec->sebutan_level_1 }}</td>
            </tr>
            <tr>
                <td colspan="2" height="40">&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td align="center">{{ $dir->namadepan }} {{ $dir->namabelakang }}</td>
            </tr>
        </table>
    @endforeach
@endsection
