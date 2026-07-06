@php
    use App\Utils\Tanggal;
    $waktu = date('H:i');
    $tempat = '';

    $sum_pokok = 0;
    if ($real) {
        $sum_pokok = $real->sum_pokok;
    }

    $saldo_pokok = $perguliran_i->alokasi - $sum_pokok;
    if ($saldo_pokok < 0) {
        $saldo_pokok = 0;
    }
    $dokumen_proposal = [
        [
            'title' => 'Cover',
            'file' => 'coverProposal',
            'withExcel' => false,
        ],
        [
            'title' => 'Surat Permohonan Pinjaman',
            'file' => 'suratPengajuanPinjaman',
            'withExcel' => false,
        ],
        [
            'title' => 'Surat Rekomendasi Kredit',
            'file' => 'suratRekomendasi',
            'withExcel' => false,
        ],

        [
            'title' => 'Surat Pernyataan Peminjam',
            'file' => 'pernyataanPeminjam',
            'withExcel' => false,
        ],

        [
            'title' => 'Form Verifikasi',
            'file' => 'formVerifikasi',
            'withExcel' => false,
        ],

        [
            'title' => 'Rencana Angsuran',
            'file' => 'rencanaAngsuran',
            'withExcel' => false,
        ],
        [
            'title' => 'Surat Kuasa Khusus',
            'file' => 'SuratPersetujuanKuasa',
            'withExcel' => false,
        ],
        [
            'title' => 'Penyerahan Jaminan',
            'file' => 'tandaTerimaJaminan',
            'withExcel' => false,
        ],
    ];

    $dokumen_pencairan = [
        [
            'title' => 'Cover',
            'file' => 'coverPencairan',
            'withExcel' => false,
        ],
        [
            'title' => 'Surat Perjanjian Kredit',
            'file' => 'spk',
            'withExcel' => false,
        ],
        [
            'title' => 'Rencana Angsuran',
            'file' => 'rencanaAngsuran',
            'withExcel' => false,
        ],
        [
            'title' => 'Berita Acara Pencairan',
            'file' => 'BaPencairan',
            'withExcel' => false,
        ],
        [
            'title' => 'Kuitansi',
            'file' => 'kuitansi',
            'withExcel' => false,
        ],
        [
            'title' => 'Kartu Angsuran',
            'file' => 'kartuAngsuran',
            'withExcel' => false,
        ],
        [
            'title' => 'analisis keputusan kredit',
            'file' => 'analisiskeputusankredit',
            'withExcel' => false,
        ],
        [
            'title' => 'surat pemberitahuan', 
            'file' => 'suratpemberitahuan',
            'withExcel' => false,
        ],
        [
            'title' => 'pengikat diri sebagai penjamin',
            'file' => 'pengikatdirisebagaipenjamin',
            'withExcel' => false,
        ],
        [
            'title' => 'surat pernyataan suami',
            'file' => 'suratpernyataansuami',
            'withExcel' => false,
        ],
        [
            'title' => 'Surat Tagihan',
            'file' => 'suratTagihan',
            'withExcel' => false,
        ],
        [
            'title' => 'Surat Kelayakan',
            'file' => 'suratKelayakan',
            'withExcel' => false,
        ],
        [
            'title' => 'Daftar Hadir Pencairan',
            'file' => 'daftarHadirPencairan',
            'withExcel' => false,
        ],
        [
            'title' => 'Pemberitahuan Ke Desa',
            'file' => 'pemberitahuanDesa',
            'withExcel' => false,
        ],

        [
            'title' => 'Surat Ahli Waris',
            'file' => 'suratAhliWaris',
            'withExcel' => false,
        ],
        [
            'title' => 'Form Verifikasi',
            'file' => 'formVerifikasi',
            'withExcel' => false,
        ],
    ];
@endphp

@extends('layouts.base')

@section('content')
    <div class="card mb-3">
        <div class="card-body p-3">
            <h5 class="mb-1">
                Anggota {{ $perguliran_i->anggota->namadepan }} Loan ID. {{ $perguliran_i->id }}
                ({{ $perguliran_i->jpp->nama_jpp }})
            </h5>
            <p class="mb-0">
                <span class="badge badge-{{ $perguliran_i->sts->warna_status }}">{{ $perguliran_i->anggota->nia }}</span>
                <span
                    class="badge badge-{{ $perguliran_i->sts->warna_status }}">{{ $perguliran_i->anggota->alamat_anggota }}</span>
                <span class="badge badge-{{ $perguliran_i->sts->warna_status }}">
                    {{ $perguliran_i->anggota->d->sebutan_desa->sebutan_desa }}
                    {{ $perguliran_i->anggota->d->nama_desa }}
                </span>
            </p>
        </div>
    </div>

    @if ($perguliran_i->status == 'P')
        <div class="card mb-3">
            <div class="card-body p-2 pb-0">
                <button type="button" class="btn btn-success btn-sm mb-2" id="BtnEditProposal">Edit Proposal</button>
                <button type="button" id="HapusProposal" class="btn btn-danger btn-sm mb-2">Hapus Proposal</button>
            </div>
        </div>
    @endif

    <div id="layout">

    </div>
    
    <div class="card mt-3">
        <div class="card-body p-2">
            @if ($perguliran_i->status == 'L' || $perguliran_i->status == 'H')
                @if ($perguliran_i->status != 'H')
                    <button class="btn btn-warning btn-sm float-end ms-2"
                        onclick="window.open('/cetak_keterangan_lunas/{{ $perguliran_i->id }}')" type="button">
                        <i class="fa fa-print"></i> Cetak Keterangan Pelunasan
                    </button>
                @endif
                <a href="/database/anggota/{{ $perguliran_i->anggota->nia }}"
                    class="btn btn-info float-end btn-sm mb-0">Kembali</a>
            @else
                <a href="/perguliran_i?status={{ $perguliran_i->status }}"
                    class="btn btn-info float-end btn-sm mb-0">Kembali</a>
                @if ($perguliran_i->status == 'W' || $perguliran_i->status == 'A')
                    <button class="btn btn-success btn-sm float-end me-2" 
                        onclick="sinkronisasiIndividu({{ $perguliran_i->id }})" type="button">
                        <i class="fa fa-sync"></i> Sinkronisasi
                    </button>
                @endif
            @endif
        </div>
    </div>

    {{-- Modal Edit Proposal --}}
    <div class="modal fade" id="EditProposal" tabindex="-1" aria-labelledby="EditProposalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="EditProposalLabel">
                        Edit Proposal Individu {{ $perguliran_i->anggota->namadepan }} Loan ID. {{ $perguliran_i->id }}
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="LayoutEditProposal">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" id="SimpanEditProposal" class="btn btn-github btn-sm">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Cetak Dokumen Proposal --}}
    <div class="modal fade" id="CetakDokumenProposal" tabindex="-1" aria-labelledby="CetakDokumenProposalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="CetakDokumenProposalLabel">Cetak Dokumen Proposal</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="input-group input-group-outline my-3">
                                <label class="form-label" for="tglProposal">Tanggal Proposal</label>
                                <input autocomplete="off" type="text" name="tglProposal" id="tglProposal"
                                    class="form-control" readonly
                                    value="{{ Tanggal::tglIndo($perguliran_i->tgl_proposal) }}">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="input-group input-group-outline my-3">
                                <label class="form-label" for="tglVerifikasi">Tanggal Verifikasi</label>
                                <input autocomplete="off" type="text" name="tglVerifikasi" id="tglVerifikasi"
                                    class="form-control" readonly
                                    value="{{ Tanggal::tglIndo($perguliran_i->tgl_verifikasi) }}">
                            </div>
                        </div>
                    </div>

                    <form action="/perguliran_i/dokumen?status=P" target="_blank" method="post">
                        @csrf

                        <input type="hidden" name="id" value="{{ $perguliran_i->id }}">
                        <div class="row">
                            @foreach ($dokumen_proposal as $doc => $val)
                                <div class="col-md-3 d-grid">
                                    @if ($val['withExcel'])
                                        <div class="btn-group">
                                            <button class="btn btn-linkedin btn-sm text-start" type="submit" name="report"
                                                value="{{ $val['file'] }}#pdf">
                                                {{ $loop->iteration }}. {{ $val['title'] }}
                                            </button>
                                            <button class="btn btn-icon btn-sm btn-instagram" type="submit" name="report"
                                                value="{{ $val['file'] }}#excel">
                                                <i class="fas fa-file-excel"></i>
                                            </button>
                                        </div>
                                    @else
                                        <button class="btn btn-linkedin btn-sm text-start" type="submit" name="report"
                                            value="{{ $val['file'] }}#pdf">
                                            {{ $loop->iteration }}. {{ $val['title'] }}
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @php
        $readonly = 'readonly';
        if ($perguliran_i->status == 'W') {
            $readonly = '';
        }

        $wt_cair = explode('_', $perguliran_i->wt_cair);
        if (count($wt_cair) == 1) {
            $waktu = $wt_cair[0];
        }

        if (count($wt_cair) == 2) {
            $waktu = $wt_cair[0];
            $tempat = $wt_cair[1];
        }
    @endphp

    {{-- Modal Cetak Dokumen Pencairan --}}
    <div class="modal fade" id="CetakDokumenPencairan" tabindex="-1" aria-labelledby="CetakDokumenPencairanLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="CetakDokumenPencairanLabel">Cetak Dokumen Pencairan</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="/perguliran_i/simpan_data/{{ $perguliran_i->id }}?save=true" method="post"
                        id="simpanData">
                        @csrf

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="input-group input-group-outline my-3">
                                    <label class="form-label" for="spk_no">Nomor SPK</label>
                                    <input autocomplete="off" type="text" name="spk_no" id="spk_no"
                                        class="form-control save" {{ $readonly }}
                                        value="{{ $perguliran_i->spk_no }}">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="input-group input-group-outline my-3">
                                    <label class="form-label" for="tempat">Tempat</label>
                                    <input autocomplete="off" type="text" name="tempat" id="tempat"
                                        class="form-control save" {{ $readonly }} value="{{ $tempat }}">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="input-group input-group-outline my-3">
                                    <label class="form-label" for="tgl_cair">Tanggal Cair</label>
                                    <input autocomplete="off" type="text" name="tgl_cair" id="tgl_cair"
                                        class="form-control date save" {{ $readonly }}
                                        value="{{ Tanggal::tglIndo($perguliran_i->tgl_cair) }}">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="input-group input-group-outline my-3">
                                    <label class="form-label" for="waktu">Waktu</label>
                                    <input autocomplete="off" type="text" name="waktu" id="waktu"
                                        class="form-control save" {{ $readonly }} value="{{ $waktu }}">
                                </div>
                            </div>
                        </div>
                    </form>

                    <form action="/perguliran_i/dokumen?status={{ $perguliran_i->status }}" target="_blank"
                        method="post">
                        @csrf

                        <input type="hidden" name="id" value="{{ $perguliran_i->id }}">
                        <div class="row">
                            @foreach ($dokumen_pencairan as $doc => $val)
                                <div class="col-md-3 d-grid">
                                    @if ($val['withExcel'])
                                        <div class="btn-group">
                                            <button class="btn btn-linkedin btn-sm text-start" type="submit"
                                                name="report" value="{{ $val['file'] }}#pdf">
                                                {{ $loop->iteration }}. {{ $val['title'] }}
                                            </button>
                                            <button class="btn btn-icon btn-sm btn-instagram" type="submit"
                                                name="report" value="{{ $val['file'] }}#excel">
                                                <i class="fas fa-file-excel"></i>
                                            </button>
                                        </div>
                                    @else
                                        <button class="btn btn-linkedin btn-sm text-start" type="submit" name="report"
                                            value="{{ $val['file'] }}#pdf">
                                            {{ $loop->iteration }}. {{ $val['title'] }}
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Rescedule Pinjaman --}}
    <div class="modal fade" id="Rescedule" tabindex="-1" aria-labelledby="ResceduleLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="ResceduleLabel">
                        Resceduling <span class="badge badge-info">Loan ID. {{ $perguliran_i->id }}</span>
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-justify">
                        Fitur ini dapat Anda gunakan jika Anda akan menjadwal ulang (<b>Resceduling</b>) Pinjaman.
                        Dengan klik tombol <b>Rescedule Pinjaman</b> maka pinjaman ini akan berstatus <b>R</b>, dan akan
                        membuat pinjaman baru dengan Alokasi sebesar saldo yang ada, yaitu
                        <b>Rp. {{ number_format($saldo_pokok) }}</b> ;
                    </div>

                    <form action="/perguliran_i/rescedule" method="post" id="formRescedule">
                        @csrf

                        <input type="hidden" name="id" id="id" value="{{ $perguliran_i->id }}">
                        <input type="hidden" name="_pengajuan" id="_pengajuan" value="{{ $saldo_pokok }}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group input-group-static my-3">
                                    <label for="tgl_resceduling">Tanggal Resceduling</label>
                                    <input autocomplete="off" type="text" name="tgl_resceduling" id="tgl_resceduling"
                                        class="form-control date" value="{{ date('d/m/Y') }}">
                                    <small class="text-danger" id="msg_tgl_resceduling"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group input-group-static my-3">
                                    <label for="pengajuan">Pengajuan Rp.</label>
                                    <input autocomplete="off" type="text" name="pengajuan" id="pengajuan"
                                        class="form-control money" disabled value="{{ number_format($saldo_pokok, 2) }}">
                                    <small class="text-danger" id="msg_pengajuan"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="my-2">
                                    <label class="form-label" for="sistem_angsuran_pokok">Sistem Angs. Pokok</label>
                                    <select class="form-control" name="sistem_angsuran_pokok" id="sistem_angsuran_pokok">
                                        @foreach ($sistem_angsuran as $sa)
                                            <option {{ $perguliran_i->sistem_angsuran == $sa->id ? 'selected' : '' }}
                                                value="{{ $sa->id }}">
                                                {{ $sa->nama_sistem }} ({{ $sa->deskripsi_sistem }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-danger" id="msg_sistem_angsuran_pokok"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="my-2">
                                    <label class="form-label" for="sistem_angsuran_jasa">Sistem Angs. Jasa</label>
                                    <select class="form-control" name="sistem_angsuran_jasa" id="sistem_angsuran_jasa">
                                        @foreach ($sistem_angsuran as $sa)
                                            <option {{ $perguliran_i->sa_jasa == $sa->id ? 'selected' : '' }}
                                                value="{{ $sa->id }}">
                                                {{ $sa->nama_sistem }} ({{ $sa->deskripsi_sistem }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-danger" id="msg_sistem_angsuran_jasa"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group input-group-static my-3">
                                    <label for="jangka">Jangka</label>
                                    <input autocomplete="off" type="number" name="jangka" id="jangka"
                                        class="form-control" value="{{ $perguliran_i->jangka }}">
                                    <small class="text-danger" id="msg_jangka"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group input-group-static my-3">
                                    <label for="pros_jasa">Prosentase Jasa (%)</label>
                                    <input autocomplete="off" type="number" name="pros_jasa" id="pros_jasa"
                                        class="form-control" value="{{ $perguliran_i->pros_jasa }}">
                                    <small class="text-danger" id="msg_pros_jasa"></small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" id="SimpanRescedule" class="btn btn-github btn-sm">
                        Rescedule Pinjaman
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Penghapusan Pinjaman --}}
    <div class="modal fade" id="Penghapusan" tabindex="-1" aria-labelledby="PenghapusanLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="PenghapusanLabel">
                        Penghapusan Piutang <span class="badge badge-info">Loan ID. {{ $perguliran_i->id }}</span>
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="/perguliran_i/hapus" method="post" id="FormPenghapusanPinjaman">
                        @csrf

                        <input type="hidden" name="id" id="id" value="{{ $perguliran_i->id }}">
                        <input type="hidden" name="saldo" id="saldo" value="{{ $saldo_pokok }}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group input-group-static my-3">
                                    <label for="tgl_penghapusan">Tanggal Penghapusan</label>
                                    <input autocomplete="off" type="text" name="tgl_penghapusan" id="tgl_penghapusan"
                                        class="form-control date" value="{{ date('d/m/Y') }}">
                                    <small class="text-danger" id="msg_tgl_penghapusan"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group input-group-static my-3">
                                    <label for="alokasi">Alokasi Rp.</label>
                                    <input autocomplete="off" type="text" name="alokasi" id="alokasi"
                                        class="form-control money" disabled value="{{ number_format($saldo_pokok, 2) }}">
                                    <small class="text-danger" id="msg_alokasi"></small>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="input-group input-group-static my-3">
                                    <label for="alasan_penghapusan">Alasan Penghapusan</label>
                                    <textarea class="form-control" name="alasan_penghapusan" id="alasan_penghapusan"></textarea>
                                    <small class="text-danger" id="msg_alasan_penghapusan"></small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" id="SimpanHapus" class="btn btn-github btn-sm">
                        Hapus Pinjaman
                    </button>
                </div>
            </div>
        </div>
    </div>

    <form action="/perguliran_i/{{ $perguliran_i->id }}" method="post" id="FormDeleteProposal">
        @csrf
        @method('DELETE')
    </form>

    <div id="placeholder" class="d-none">
        <div class="row">
            <div class="col-lg-4 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <span class="placeholder rounded-circle border" style="width: 150px; height: 150px;"></span>

                        <h5 class="mb-2">
                            <b><span class="placeholder col-12"></span></b>
                        </h5>

                        <div class="text-muted">
                            <span class="placeholder col-12"></span>
                        </div>
                        <div class="text-muted"><span class="placeholder col-12"></span></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="alert placeholder col-12 p-4"></div>
                <div class="alert placeholder col-12 p-5"></div>
                <div class="alert placeholder col-12 p-5"></div>
                <div class="alert placeholder col-12 p-4"></div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        new Choices($('#sistem_angsuran_pokok')[0], {
            shouldSort: false,
            fuseOptions: {
                threshold: 0.1,
                distance: 1000
            }
        })
        new Choices($('#sistem_angsuran_jasa')[0], {
            shouldSort: false,
            fuseOptions: {
                threshold: 0.1,
                distance: 1000
            }
        })

        $(".date").flatpickr({
            dateFormat: "d/m/Y"
        })

        var formatter = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })

        $.get('/perguliran_i/{{ $perguliran_i->id }}', function(result) {
            $('#layout').html(result)
        })

        $('#BtnEditProposal').click(function(e) {
            e.preventDefault()

            $.get('/perguliran_i/{{ $perguliran_i->id }}/edit', function(result) {
                $('#LayoutEditProposal').html(result)
                $('#EditProposal').modal('show')
            })
        })

        $('#HapusProposal').click(function(e) {
            e.preventDefault()

            Swal.fire({
                title: 'Hapus Proposal Ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = $('#FormDeleteProposal')
                    $.ajax({
                        type: 'post',
                        url: form.attr('action'),
                        data: form.serialize(),
                        success: function(result) {
                            if (result.hapus) {
                                Swal.fire('Berhasil!', result.msg, 'success').then(() => {
                                    window.location.href = '/perguliran_i'
                                })
                            } else {
                                Swal.fire('Peringatan', result.msg, 'warning')
                            }
                        }
                    })
                }
            })
        })

        $(document).on('click', '#SimpanEditProposal', function(e) {
            e.preventDefault()
            $('small').html('')

            var form = $('#FormEditProposal')
            $.ajax({
                type: 'POST',
                url: form.attr('action'),
                data: form.serialize(),
                success: function(result) {
                    Swal.fire('Berhasil', result.msg, 'success').then(() => {
                        $.get('/perguliran_i/{{ $perguliran_i->id }}', function(result) {
                            $('#layout').html(result)

                            $('#EditProposal').modal('hide')
                            window.location.reload()
                        })
                    })
                },
                error: function(result) {
                    const respons = result.responseJSON;

                    Swal.fire('Error', 'Cek kembali input yang anda masukkan', 'error')
                    $.map(respons, function(res, key) {
                        $('#' + key).parent('.input-group.input-group-static')
                            .addClass(
                                'is-invalid')
                        $('#FormEditProposal #msg_' + key).html(res)
                    })
                }
            })
        })

        $(document).on('change', '.save', function() {
            var form = $('#simpanData')
            $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize(),
                success: function(result) {
                    if (result.success) {
                        $('[name=tgl_cair]').val(result.tgl_cair)
                        Swal.fire('Berhasil', result.msg, 'success')
                    }
                }
            })
        })

        $(document).on('click', '#kembaliProposal', function() {
            Swal.fire({
                title: 'Peringatan',
                text: 'Anda yakin ingin mengembalikan pinjaman menjadi P (Pengajuan/Proposal)?',
                showCancelButton: true,
                confirmButtonText: 'Kembalikan',
                cancelButtonText: 'Batal',
                icon: 'warning'
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = $('#formKembaliProposal')
                    $.ajax({
                        type: form.attr('method'),
                        url: form.attr('action'),
                        data: form.serialize(),
                        success: function(result) {
                            if (result.success) {
                                Swal.fire('Berhasil', result.msg, 'success').then(() => {
                                    window.location.href = '/detail_i/' + result
                                        .id_pinkel
                                })
                            }
                        }
                    })
                }
            })
        })

        $(document).on('click', '#SimpanRescedule', async function(e) {
            e.preventDefault()
            $('#Rescedule').modal('hide')

            const {
                value: spk
            } = await Swal.fire({
                title: 'Masukkan Nomor SPK baru',
                input: 'text',
                inputLabel: 'Spk No.',
                confirmButtonText: 'Simpan Perubahan'
            })

            if (spk) {
                var form = $('#formRescedule')
                $.ajax({
                    type: 'POST',
                    url: form.attr('action') + '?spk=' + spk,
                    data: form.serialize(),
                    success: function(result) {
                        if (result.success) {
                            var id = result.id
                            $.get('/perguliran_i/generate/' + result.id + '?status=' + result
                                .status +
                                '&save',
                                function(result) {
                                    if (result.success) {
                                        Swal.fire('Berhasil', result.msg, 'success').then(
                                            () => {
                                                window.location.href = '/detail_i/' + id
                                            })
                                    }
                                })
                        }
                    }
                })
            }
        })

        $(document).on('click', '#SimpanHapus', function(e) {
            e.preventDefault()

            Swal.fire({
                title: 'Peringatan',
                text: 'Anda yakin ingin melakukan penghapusan untuk pinjaman ini?',
                showCancelButton: true,
                confirmButtonText: 'Hapus Pinjaman',
                cancelButtonText: 'Batal',
                icon: 'warning'
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = $('#FormPenghapusanPinjaman')
                    $.ajax({
                        type: form.attr('method'),
                        url: form.attr('action'),
                        data: form.serialize(),
                        success: function(result) {
                            if (result.success) {
                                Swal.fire('Berhasil', result.msg, 'success').then(() => {
                                    window.location.href = '/perguliran_i'
                                })
                            }
                        }
                    })
                }
            })
        })

        $(document).on('click', '.btn-link', function(e) {
            var action = $(this).attr('data-action')

            open_window(action)
        })

        function sinkronisasiIndividu(id) {
            Swal.fire({
                title: 'Konfirmasi Sinkronisasi',
                text: 'Apakah Anda yakin ingin melakukan sinkronisasi data pinjaman individu ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Sinkronisasi!',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: '/sinkronisasi/individu/' + id,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            return response;
                        },
                        error: function(xhr) {
                            Swal.showValidationMessage(
                                'Gagal melakukan sinkronisasi: ' + (xhr.responseJSON?.message || 'Terjadi kesalahan')
                            );
                        }
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value.success) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: result.value.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload halaman setelah sukses
                        location.reload();
                    });
                }
            });
        }

        $(".money").maskMoney();
    </script>
@endsection
