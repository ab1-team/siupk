@extends('layouts.base')

@section('content')
    <form action="" method="post" id="defaultForm">
        @csrf

        <input type="hidden" name="tgl" id="tgl" value="{{ date('d/m/Y') }}">
    </form>

    <style>
        .text-justify {
            text-align: justify;
        }
    </style>

    <div class="row">
        @if ($jumlah_saldo <= $jumlah)
            <div class="col-12">
                <div class="alert alert-warning text-white" role="alert">
                    Sepertinya saldo transaksi anda belum tersimpan di aplikasi. Silahkan Klik <a href="#"
                        data-href="/simpan_saldo?bulan=00&tahun={{ date('Y') }}" class="alert-link"
                        id="simpanSaldo">Disini</a> untuk menyimpan
                    saldo transaksi anda
                </div>
            </div>
        @endif
        <div class="col-sm-4">
            <div class="card">
                <div class="card-body p-3 position-relative pointer" id="btnAktif">
                    <div class="row">
                        <div class="col-7 text-start">
                            <p class="text-sm mb-1 text-capitalize font-weight-bold">Pinjaman Aktif</p>
                            <h5 class="font-weight-bolder mb-0">
                                {{ $pinjaman_kelompok }} Kelompok
                            </h5>
                            <span class="text-sm text-end text-success font-weight-bolder mt-auto mb-0">
                                {{ $pinjaman_anggota }}
                                <span class="font-weight-normal text-secondary">Individu</span>
                            </span>
                        </div>
                        <div class="col-5">
                            <div class="dropdown text-end">
                                <span class="text-xs text-secondary">Periode {{ date('d/m/y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4 mt-sm-0 mt-4">
            <div class="card">
                <div class="card-body p-3 position-relative pointer" id="btnpinjaman">
                    <div class="row">
                        <div class="col-7 text-start">
                            <p class="text-sm mb-1 text-capitalize font-weight-bold">Proposal Pinjaman</p>
                            <h5 class="font-weight-bolder mb-0">
                                {{ $proposal }} Proposal
                            </h5>
                            <span class="text-sm text-end text-success font-weight-bolder mt-auto mb-0">
                                {{ $verifikasi }}
                                <span class="font-weight-normal text-secondary">verifikasi</span>
                            </span>
                        </div>
                        <div class="col-5">
                            <div class="dropdown text-end">
                                <span class="text-xs text-secondary">{{ $waiting }} waiting</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4 mt-sm-0 mt-4">
            <div class="card">
                <div class="card-body p-3 position-relative pointer" id="btnjatuhTempo">
                    <div class="row">
                        <div class="col-7 text-start">
                            <p class="text-sm mb-1 text-capitalize font-weight-bold">Jatuh Tempo</p>
                            <h5 class="font-weight-bolder mb-0">
                                <span id="jatuh_tempo">
                                    <span class="jt-spinner spinner-border sm text-info" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </span>
                                    <span class="jt-value" style="display:none">0</span>
                                </span> Hari Ini
                            </h5>
                            <span class="font-weight-normal text-secondary text-sm">
                                <span class="font-weight-bolder text-success" id="nunggak">
                                    <span class="jt-spinner spinner-border xs text-info" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </span>
                                    <span class="jt-value" style="display:none">0</span>
                                </span> menunggak
                            </span>
                        </div>
                        <div class="col-5">
                            <div class="dropdown text-end">
                                <span class="text-xs text-warning">&#33; tagihan</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-lg-4 col-sm-6">
            <div class="card h-100">
                <div class="card-header pb-0 p-3">
                    <div class="d-flex justify-content-between">
                        <h6 class="mb-0">Angsuran Hari Ini</h6>
                        <button type="button"
                            class="btn btn-icon-only btn-rounded btn-outline-secondary mb-0 ms-2 btn-sm d-flex align-items-center justify-content-center"
                            data-bs-toggle="tooltip" data-bs-placement="bottom" title=""
                            data-bs-original-title="See traffic channels">
                            <i class="material-icons text-sm">priority_high</i>
                        </button>
                    </div>
                </div>
                <div class="card-body pb-0 p-3 pt-0 mt-4">
                    <div class="row">
                        <div class="col-7 text-start">
                            <div class="chart">
                                <canvas id="chart-pie" class="chart-canvas" height="400"
                                    style="display: block; box-sizing: border-box; height: 200px; width: 169.7px;"
                                    width="339"></canvas>
                            </div>
                        </div>
                        <div class="col-5 my-auto">
                            <span class="badge badge-md badge-dot me-4 d-block text-start">
                                <i class="bg-info"></i>
                                <span class="text-dark text-xs">SPP Pokok</span>
                            </span>
                            <span class="badge badge-md badge-dot me-4 d-block text-start">
                                <i class="bg-success"></i>
                                <span class="text-dark text-xs">SPP Jasa</span>
                            </span>
                            <span class="badge badge-md badge-dot me-4 d-block text-start">
                                <i class="bg-dark"></i>
                                <span class="text-dark text-xs">UEP Pokok</span>
                            </span>
                            <span class="badge badge-md badge-dot me-4 d-block text-start">
                                <i class="bg-secondary"></i>
                                <span class="text-dark text-xs">UEP Jasa</span>
                            </span>
                            <span class="badge badge-md badge-dot me-4 d-block text-start">
                                <i class="bg-danger"></i>
                                <span class="text-dark text-xs">PL Pokok</span>
                            </span>
                            <span class="badge badge-md badge-dot me-4 d-block text-start">
                                <i class="bg-warning"></i>
                                <span class="text-dark text-xs">PL Jasa</span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-footer pt-0 pb-2 p-3 d-flex align-items-center">
                    <div class="w-60">
                        <div class="text-sm">
                            Total Angsuran
                            <div>
                                <b>Rp. <span id="total_angsur"></span></b>
                            </div>
                        </div>
                    </div>
                    <div class="w-40 text-end">
                        <button type="button" id="btnDetailAngsuran" class="btn bg-light mb-0 text-end">Detail</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8 col-sm-6 mt-sm-0 mt-4">
            <div class="card">
                <div class="card-header pb-0 p-3">
                    <div class="d-flex justify-content-between">
                        <h6 class="mb-0">Pendapatan dan Beban</h6>
                        <button type="button"
                            class="btn btn-icon-only btn-rounded btn-outline-secondary mb-0 ms-2 btn-sm d-flex align-items-center justify-content-center"
                            data-bs-toggle="tooltip" data-bs-placement="left"
                            data-bs-original-title="See which ads perform better">
                            <i class="material-icons text-sm">priority_high</i>
                        </button>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge badge-md badge-dot me-4">
                            <i class="bg-success"></i>
                            <span class="text-dark text-xs">Pendapatan</span>
                        </span>
                        <span class="badge badge-md badge-dot me-4">
                            <i class="bg-warning"></i>
                            <span class="text-dark text-xs">Beban</span>
                        </span>
                        <span class="badge badge-md badge-dot me-4">
                            <i class="bg-info"></i>
                            <span class="text-dark text-xs">Laba</span>
                        </span>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="chart">
                        <canvas id="chart-line" class="chart-canvas" height="400"
                            style="display: block; box-sizing: border-box; height: 210px; width: 844.4px;"
                            width="1688"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Jatuh Dempo --}}
    <div class="modal fade" id="jatuhTempo" aria-labelledby="jatuhTempoLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="jatuhTempoLabel">Jatuh Tempo</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="nav-wrapper position-relative end-0">
                        <ul class="nav nav-pills nav-fill p-1" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1 active text-danger" data-bs-toggle="tab"
                                    href="#tagihan_hari_ini" role="tab" aria-controls="tagihan_hari_ini"
                                    aria-selected="true">
                                    Hari Ini
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1 text-warning" data-bs-toggle="tab" href="#menunggak"
                                    role="tab" aria-controls="menunggak" aria-selected="false">
                                    Menunggak
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1 text-info" data-bs-toggle="tab" href="#tagihan"
                                    role="tab" aria-controls="tagihan" aria-selected="false">
                                    Tagihan
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content mt-2">
                            <div class="tab-pane fade show active" id="tagihan_hari_ini" role="tabpanel"
                                aria-labelledby="tagihan_hari_ini">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped midle" width="100%">
                                                <thead>
                                                    <tr>
                                                        <td align="center">No</td>
                                                        <td align="center">Nama Kelompok</td>
                                                        <td align="center">Tanggal Cair</td>
                                                        <td align="center">Alokasi</td>
                                                        <td align="center">Tunggakan Pokok</td>
                                                        <td align="center">Tunggakan Jasa</td>
                                                    </tr>
                                                </thead>
                                                <tbody id="TbHariIni"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="menunggak" role="tabpanel" aria-labelledby="menunggak">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped midle" width="100%">
                                                <thead>
                                                    <tr>
                                                        <td align="center">No</td>
                                                        <td align="center">Nama Kelompok</td>
                                                        <td align="center">Tanggal Cair</td>
                                                        <td align="center">Alokasi</td>
                                                        <td align="center">Tunggakan Pokok</td>
                                                        <td align="center">Tunggakan Jasa</td>
                                                    </tr>
                                                </thead>
                                                <tbody id="TbMenunggak"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tagihan" role="tabpanel" aria-labelledby="tagihan">
                                <div class="card">
                                    <div class="card-body p-3">
                                        <form action="/dashboard/tagihan" method="post" id="formTagihan">
                                            @csrf

                                            <div class="alert alert-info text-white">
                                                Kirim Whatsapp Tagihan berdasarkan tanggal jatuh tempo. Pastikan nomor HP
                                                kelompok dapat menerima pesan Whatsapp.
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="input-group input-group-static mb-3">
                                                        <label for="tgl_tagihan">Tgl Jatuh Tempo</label>
                                                        <input autocomplete="off" type="text" name="tgl_tagihan"
                                                            id="tgl_tagihan" class="form-control date pesan"
                                                            value="{{ date('d/m/Y') }}">
                                                        <small class="text-danger" id="msg_tgl_tagihan"></small>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="input-group input-group-static mb-3">
                                                        <label for="tgl_pembayaran">Tgl Pembayaran</label>
                                                        <input autocomplete="off" type="text" name="tgl_pembayaran"
                                                            id="tgl_pembayaran" class="form-control date pesan"
                                                            value="{{ date('d/m/Y') }}">
                                                        <small class="text-danger" id="msg_tgl_pembayaran"></small>
                                                    </div>
                                                </div>
                                            </div>

                                            <textarea class="form-control d-none" name="pesan_whatsapp" id="pesan_whatsapp"></textarea>
                                        </form>

                                        <div class="d-flex justify-content-end">
                                            <button type="button" id="CekTagihan" class="btn btn-sm btn-github"
                                                {{ strlen($user->hp) >= 11 ? '' : 'disabled' }}>Preview</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info btn-sm me-2 btn-pelaporan">
                        Print
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Pinjaman --}}
    <div class="modal fade" id="pinjaman" aria-labelledby="pinjamanLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="pinjamanLabel">Pinjaman</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="nav-wrapper position-relative end-0">
                        <ul class="nav nav-pills nav-fill p-1" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1 active text-info" data-bs-toggle="tab" href="#proposal"
                                    role="tab" aria-controls="proposal" aria-selected="true">
                                    Proposal
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1 text-danger" data-bs-toggle="tab" href="#verifikasi"
                                    role="tab" aria-controls="verifikasi" aria-selected="false">
                                    Verifikasi
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1 text-warning" data-bs-toggle="tab" href="#waiting"
                                    role="tab" aria-controls="waiting" aria-selected="false">
                                    Waiting
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content mt-2">
                            <div class="tab-pane fade show active" id="proposal" role="tabpanel"
                                aria-labelledby="proposal">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped midle" width="100%">
                                                <thead>
                                                    <tr>
                                                        <td align="center">No</td>
                                                        <td align="center">Tanggal Proposal</td>
                                                        <td align="center">Nama Kelompok</td>
                                                        <td align="center">Alokasi</td>
                                                        <td align="center">Anggota</td>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbProposal"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="verifikasi" role="tabpanel" aria-labelledby="verifikasi">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped midle" width="100%">
                                                <thead>
                                                    <tr>
                                                        <td align="center">No</td>
                                                        <td align="center">Tanggal Verifikasi</td>
                                                        <td align="center">Nama Kelompok</td>
                                                        <td align="center">Alokasi</td>
                                                        <td align="center">Anggota</td>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbVerifikasi"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="waiting" role="tabpanel" aria-labelledby="waiting">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped midle" width="100%">
                                                <thead>
                                                    <tr>
                                                        <td align="center">No</td>
                                                        <td align="center">Tanggal Tunggu</td>
                                                        <td align="center">Nama Kelompok</td>
                                                        <td align="center">Alokasi</td>
                                                        <td align="center">Anggota</td>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbWaiting"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info btn-sm me-2 btn-pelaporan">
                        Print
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Kelompok Aktif --}}
    <div class="modal fade" id="aktif" aria-labelledby="aktifLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="aktifLabel">Pinjaman Aktif</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="nav-wrapper position-relative end-0">
                        <ul class="nav nav-pills nav-fill p-1" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1 active text-info" data-bs-toggle="tab"
                                    href="#kelompok_aktif" role="tab" aria-controls="kelompok_aktif"
                                    aria-selected="true">
                                    Kelompok
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1 text-danger" data-bs-toggle="tab" href="#anggota"
                                    role="tab" aria-controls="anggota" aria-selected="false">
                                    Individu
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content mt-2">
                            <div class="tab-pane fade show active" id="kelompok_aktif" role="tabpanel"
                                aria-labelledby="kelompok_aktif">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped midle" width="100%">
                                                <thead>
                                                    <tr>
                                                        <td align="center">No</td>
                                                        <td align="center">Tanggal Cair</td>
                                                        <td align="center">Nama Kelompok</td>
                                                        <td align="center">Alokasi</td>
                                                        <td align="center">Saldo</td>
                                                        <td align="center">Anggota</td>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbKelompok"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="anggota" role="tabpanel" aria-labelledby="anggota">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped midle" width="100%">
                                                <thead>
                                                    <tr>
                                                        <td align="center">No</td>
                                                        <td align="center">Nik</td>
                                                        <td align="center">Nama Anggota</td>
                                                        <td align="center">Alamat</td>
                                                        <td align="center">Tanggal Cair</td>
                                                        <td align="center">Alokasi</td>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbAnggota"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info btn-sm me-2 btn-pelaporan" data-print="pdf">
                        Print
                    </button>
                    <button type="button" class="btn btn-success btn-sm me-2 btn-pelaporan" data-print="excel">
                        Excel
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Detail Angsuran --}}
    <div class="modal fade" id="detailAngsuran" aria-labelledby="detailAngsuranLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="detailAngsuranLabel">Pinjaman detailAngsuran</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tagihan --}}
    <div class="modal fade" id="tagihanPinjaman" aria-labelledby="tagihanPinjamanLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="tagihanPinjamanLabel">Tagihan</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="TbTagihan"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-github btn-sm" id="KirimPesan">Kirim Pesan</button>
                    <button type="button" class="btn btn-danger btn-sm" id="closeTagihan">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <form action="/pelaporan/preview" method="post" id="FormLaporanDashboard" target="_blank">
        @csrf

        <input type="hidden" name="type" id="type" value="pdf">
        <input type="hidden" name="tahun" id="tahun" value="{{ date('Y') }}">
        <input type="hidden" name="bulan" id="bulan" value="{{ date('m') }}">
        <input type="hidden" name="hari" id="hari" value="{{ date('d') }}">
        <input type="hidden" name="laporan" id="laporan" value="">
        <input type="hidden" name="sub_laporan" id="sub_laporan" value="">
    </form>

    @php
        $p = $saldo[4];
        $b = $saldo[5];
        $surplus = $saldo['surplus'];
    @endphp

    <textarea name="msgInvoice" id="msgInvoice" class="d-none">{{ Session::get('msg') }}</textarea>
@endsection

@section('script')
    <script>
        $(".date").flatpickr({
            dateFormat: "d/m/Y"
        })

        $.ajax({
            type: 'post',
            url: '/dashboard/jatuh_tempo',
            data: $('#defaultForm').serialize(),
            success: function(result) {
                if (result.success) {
                    $('#jatuh_tempo .jt-spinner').hide()
                    $('#jatuh_tempo .jt-value').text(result.jatuh_tempo).show()

                    if (result.jatuh_tempo != '00') {
                        $('#TbHariIni').html(result.hari_ini)
                    }
                } else {
                    $('#jatuh_tempo .jt-spinner').hide()
                    $('#jatuh_tempo .jt-value').text('-').show()
                }
            },
            error: function() {
                $('#jatuh_tempo .jt-spinner').hide()
                $('#jatuh_tempo .jt-value').text('-').show()
            }
        })

        $.ajax({
            type: 'post',
            url: '/dashboard/nunggak',
            data: $('#defaultForm').serialize(),
            success: function(result) {
                if (result.success) {
                    $('#nunggak .jt-spinner').hide()
                    $('#nunggak .jt-value').text(result.nunggak).show()

                    if (result.nunggak != '00') {
                        $('#TbMenunggak').html(result.table)
                    }
                } else {
                    $('#nunggak .jt-spinner').hide()
                    $('#nunggak .jt-value').text('-').show()
                }
            },
            error: function() {
                $('#nunggak .jt-spinner').hide()
                $('#nunggak .jt-value').text('-').show()
            }
        })

        function tagihan() {
            var tgl_tagihan = $('#tgl_tagihan').val()

            $.ajax({
                url: '/dashboard/tagihan',
                type: 'post',
                data: $('#formTagihan').serialize(),
                success: function(result) {
                    if (result.success) {
                        $('#TbTagihan').html(result.tagihan)
                        $('#tagihanPinjaman').modal('show')
                    }
                }
            })
        }

        $(document).on('click', '#CekTagihan', function(e) {
            e.preventDefault()

            tagihan()
        })

        $(document).on('click', '#closeTagihan', function() {
            $('#tagihanPinjaman').modal('hide')
            $('#jatuhTempo').modal('show')
        })

        $.get('/dashboard/pinjaman?status=P', function(result) {
            if (result.success) {
                $('#tbProposal').html(result.table)
            }
        })

        $.get('/dashboard/pinjaman?status=V', function(result) {
            if (result.success) {
                $('#tbVerifikasi').html(result.table)
            }
        })

        $.get('/dashboard/pinjaman?status=W', function(result) {
            if (result.success) {
                $('#tbWaiting').html(result.table)
            }
        })

        $.get('/dashboard/pinjaman?status=A', function(result) {
            if (result.success) {
                $('#tbKelompok').html(result.table)
            }
        })
        $.get('/dashboard/pinjamanI?status=A', function(result) {
            if (result.success) {
                $('#tbAnggota').html(result.table)
            }
        })

        $.get('/dashboard/pemanfaat?status=A', function(result) {
            if (result.success) {
                $('#d').html(result.table)
            }
        })

        $(document).on('click', '#btnjatuhTempo', function(e) {
            e.preventDefault()

            $('#jatuhTempo').modal('show')


            let tab = $('#jatuhTempo').find('ul li a.active')
            if (tab.length > 0) {
                if (tab.attr('aria-controls') != 'tagihan') {
                    $('.btn-pelaporan').show()
                    setLaporan('5', tab.attr('aria-controls'))
                } else {
                    $('.btn-pelaporan').hide()
                }
            }
        })

        $(document).on('click', '#btnpinjaman', function(e) {
            e.preventDefault()

            $('#pinjaman').modal('show')
            $('.btn-pelaporan').show()

            let tab = $('#pinjaman').find('ul li a.active')
            if (tab.length > 0) {
                setLaporan('5', tab.attr('aria-controls'))
            }
        })

        $(document).on('click', '#btnAktif', function(e) {
            e.preventDefault()

            $('#aktif').modal('show')
            $('.btn-pelaporan').show()

            let tab = $('#aktif').find('ul li a.active')
            if (tab.length > 0) {
                setLaporan('5', tab.attr('aria-controls'))
            }
        })

        $(document).on('click', '.nav.nav-pills .nav-item', function() {
            var a = $(this).find('a')

            if (a.attr('aria-controls') == 'tagihan') {
                $('.btn-pelaporan').hide()
            } else {
                $('.btn-pelaporan').show()
            }
            setLaporan('5', a.attr('aria-controls'))
        })

        $(document).on('click', '.btn-pelaporan', function(e) {
            var id = $(this).attr('data-print')
            $('#FormLaporanDashboard #type').val(id)

            $('#FormLaporanDashboard').submit()
            $('#FormLaporanDashboard #type').val('pdf')
        })

        function setLaporan(laporan, subLaporan = null) {
            $('#FormLaporanDashboard #laporan').val(laporan);
            $('#FormLaporanDashboard #sub_laporan').val(subLaporan);
        }

        $(document).on('click', '#KirimPesan', function(e) {
            e.preventDefault()

            var messages = []
            $('[data-input=checked]:checked').each(function(i) {
                var pesan = this.value
                var number = pesan.split('||')[0]
                var msg = pesan.split('||')[2]

                if (!number.startsWith('08') && !number.startsWith('628')) {
                    number = '0' + number
                }

                messages.push({
                    number: number,
                    message: msg
                })
            })

            if (messages.length == 0) return

            var INSTANCE_NAME = (window.WAGATEWAY && window.WAGATEWAY.name) || ''
            var INSTANCE_TOKEN = (window.WAGATEWAY && window.WAGATEWAY.token) || ''
            var KECAMATAN_ID = window.APP_LOKASI || ''

            $.ajax({
                type: 'POST',
                url: '{{ url("/pengaturan/whatsapp/send_bulk") }}',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                data: JSON.stringify({
                    kecamatan_id: KECAMATAN_ID,
                    messages: messages.map(function(m) {
                        return { to: m.number, message: m.message }
                    })
                }),
                success: function(result) {
                    if (result.success) {
                        Swal.fire('Berhasil', 'Pesan Berhasil Masuk Antrean', 'success')
                    }
                }
            })
        })
    </script>

    @if (Session::get('invoice'))
        <script>
            function msgInvoice(number, msg, repeat) {
                repeat = repeat || 0
                var INSTANCE_NAME = (window.WAGATEWAY && window.WAGATEWAY.name) || ''
                var INSTANCE_TOKEN = (window.WAGATEWAY && window.WAGATEWAY.token) || ''
                $.ajax({
                    type: 'POST',
                    url: '{{ $api }}/message/sendText/' + INSTANCE_NAME,
                    timeout: 0,
                    headers: { "Content-Type": "application/json", "apikey": INSTANCE_TOKEN },
                    data: JSON.stringify({ number: number, text: msg }),
                    success: function(result) {
                        if (!result) {
                            setTimeout(function() { msgInvoice(number, msg, repeat + 1) }, 1000)
                        }
                    },
                    error: function() {
                        if (repeat < 1) {
                            setTimeout(function() { msgInvoice(number, msg, repeat + 1) }, 1000)
                        }
                    }
                })
            }

            msgInvoice("{{ Session::get('hp_dir') }}", $('#msgInvoice').val())
            setTimeout(() => {
                msgInvoice('0882006644656', $('#msgInvoice').val())
            }, 1000);
        </script>
    @endif

    <script>
        var formatter = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })

        var ctx1 = document.getElementById("chart-line").getContext("2d");
        var ctx2 = document.getElementById("chart-pie").getContext("2d");

        // Line chart
        new Chart(ctx1, {
            type: "line",
            data: {
                labels: [
                    "Jan",
                    "Feb",
                    "Mar",
                    "Apr",
                    "Mei",
                    "Jun",
                    "Jul",
                    "Agu",
                    "Sep",
                    "Okt",
                    "Nov",
                    "Des",
                ],
                datasets: [{
                        label: "Pendapatan",
                        tension: 0,
                        pointRadius: 5,
                        pointBackgroundColor: "#4CAF50",
                        pointBorderColor: "transparent",
                        borderColor: "#4CAF50",
                        borderWidth: 2,
                        backgroundColor: "transparent",
                        fill: true,
                        data: [
                            "{{ $p['1'] }}",
                            "{{ $p['2'] }}",
                            "{{ $p['3'] }}",
                            "{{ $p['4'] }}",
                            "{{ $p['5'] }}",
                            "{{ $p['6'] }}",
                            "{{ $p['7'] }}",
                            "{{ $p['8'] }}",
                            "{{ $p['9'] }}",
                            "{{ $p['10'] }}",
                            "{{ $p['11'] }}",
                            "{{ $p['12'] }}"
                        ],
                        maxBarThickness: 6
                    },
                    {
                        label: "Beban",
                        tension: 0,
                        borderWidth: 0,
                        pointRadius: 5,
                        pointBackgroundColor: "#fb8c00",
                        pointBorderColor: "transparent",
                        borderColor: "#fb8c00",
                        borderWidth: 2,
                        backgroundColor: "transparent",
                        fill: true,
                        data: [
                            "{{ $b['1'] * -1 }}",
                            "{{ $b['2'] * -1 }}",
                            "{{ $b['3'] * -1 }}",
                            "{{ $b['4'] * -1 }}",
                            "{{ $b['5'] * -1 }}",
                            "{{ $b['6'] * -1 }}",
                            "{{ $b['7'] * -1 }}",
                            "{{ $b['8'] * -1 }}",
                            "{{ $b['9'] * -1 }}",
                            "{{ $b['10'] * -1 }}",
                            "{{ $b['11'] * -1 }}",
                            "{{ $b['12'] * -1 }}"
                        ],
                        maxBarThickness: 6
                    },
                    {
                        label: "Laba",
                        tension: 0,
                        borderWidth: 0,
                        pointRadius: 5,
                        pointBackgroundColor: "#1A73E8",
                        pointBorderColor: "transparent",
                        borderColor: "#1A73E8",
                        borderWidth: 2,
                        backgroundColor: "transparent",
                        fill: true,
                        data: [
                            "{{ $surplus['1'] }}",
                            "{{ $surplus['2'] }}",
                            "{{ $surplus['3'] }}",
                            "{{ $surplus['4'] }}",
                            "{{ $surplus['5'] }}",
                            "{{ $surplus['6'] }}",
                            "{{ $surplus['7'] }}",
                            "{{ $surplus['8'] }}",
                            "{{ $surplus['9'] }}",
                            "{{ $surplus['10'] }}",
                            "{{ $surplus['11'] }}",
                            "{{ $surplus['12'] }}"
                        ],
                        maxBarThickness: 6
                    }
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                scales: {
                    y: {
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [5, 5],
                            color: '#c1c4ce5c'
                        },
                        ticks: {
                            display: true,
                            padding: 10,
                            color: '#9ca2b7',
                            font: {
                                size: 14,
                                weight: 300,
                                family: "Roboto",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: true,
                            borderDash: [5, 5],
                            color: '#c1c4ce5c'
                        },
                        ticks: {
                            display: true,
                            color: '#9ca2b7',
                            padding: 10,
                            font: {
                                size: 14,
                                weight: 300,
                                family: "Roboto",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },
                },
            },
        });

        // Pie chart
        new Chart(ctx2, {
            type: "pie",
            data: {
                labels: [
                    'SPP Pokok',
                    'SPP Jasa',
                    'UEP Pokok',
                    'UEP Jasa',
                    'PL Pokok',
                    'PL Jasa'
                ],
                datasets: [{
                    label: "Projects",
                    weight: 9,
                    cutout: 0,
                    tension: 0.9,
                    pointRadius: 2,
                    borderWidth: 1,
                    backgroundColor: [
                        '#1a73e8',
                        '#4caf50',
                        '#344767',
                        '#7b809a',
                        '#f44335',
                        '#fb8c00',
                    ],
                    data: [
                        "{{ $pokok_spp }}",
                        "{{ $jasa_spp }}",
                        "{{ $pokok_uep }}",
                        "{{ $jasa_uep }}",
                        "{{ $pokok_pl }}",
                        "{{ $jasa_pl }}",
                    ],
                    fill: false
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                scales: {
                    y: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false,
                            color: '#c1c4ce5c'
                        },
                        ticks: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false,
                            color: '#c1c4ce5c'
                        },
                        ticks: {
                            display: false,
                        }
                    },
                },
            },
        });

        var total_angsur =
            "{{ $pokok_spp + $jasa_spp + $pokok_uep + $jasa_uep + $pokok_pl + $jasa_pl }}"

        $('#total_angsur').html(formatter.format(total_angsur))

        let childWindow, loading;
        $(document).on('click', '#simpanSaldo', function(e) {
            var link = $(this).attr('data-href')

            loading = Swal.fire({
                title: "Mohon Menunggu..",
                html: "Menyimpan Saldo Januari sampai Desember Th. {{ date('Y') }}",
                timerProgressBar: true,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            })

            childWindow = window.open(link, '_blank');
        })

        window.addEventListener('message', function(event) {
            if (event.data === 'closed') {
                loading.close()
                window.location.reload()
            }
        })
    </script>
@endsection
