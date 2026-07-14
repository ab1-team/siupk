@extends('layouts.base')

@section('content')
    <div class="row mb-5">
        <div class="col-lg-3">
            <div class="card position-sticky top-10">
                <ul class="nav flex-column bg-white border-radius-lg p-3">
                    <li class="nav-item mb-2">
                        <b>Pengaturan</b>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark d-flex" data-scroll="" href="#lembaga">
                            <i class="material-icons text-lg me-2">business</i>
                            <span class="text-sm">Identitas Lembaga</span>
                        </a>
                    </li>
                    <li class="nav-item pt-2">
                        <a class="nav-link text-dark d-flex" data-scroll="" href="#pengelola">
                            <i class="material-icons text-lg me-2">assignment_ind</i>
                            <span class="text-sm">Sebutan Pengelola</span>
                        </a>
                    </li>
                    <li class="nav-item pt-2">
                        <a class="nav-link text-dark d-flex" data-scroll="" href="#pinjaman">
                            <i class="material-icons text-lg me-2">equalizer</i>
                            <span class="text-sm">Sistem Pinjaman</span>
                        </a>
                    </li>
                    <li class="nav-item pt-2">
                        <a class="nav-link text-dark d-flex" data-scroll="" href="#kolek">
                            <i class="material-icons text-lg me-2">equalizer</i>
                            <span class="text-sm">Kolektabilitas</span>
                        </a>
                    </li>

                    @if (session('lokasi') == 1 || session('lokasi') == 3)
                        <li class="nav-item pt-2">
                            <a class="nav-link text-dark d-flex" data-scroll="" href="#simpanan">
                                <i class="material-icons text-lg me-2">poll</i>
                                <span class="text-sm">Sistem Simpanan</span>
                            </a>
                        </li>
                    @endif

                    <li class="nav-item pt-2">
                        <a class="nav-link text-dark d-flex" data-scroll="" href="#asuransi">
                            <i class="material-icons text-lg me-2">account_balance_wallet</i>
                            <span class="text-sm">Pengaturan Asuransi</span>
                        </a>
                    </li>
                    <li class="nav-item pt-2">
                        <a class="nav-link text-dark d-flex" data-scroll="" href="#redaksi_spk">
                            <i class="material-icons text-lg me-2">description</i>
                            <span class="text-sm">Redaksi Dok. SPK</span>
                        </a>
                    </li>
                    {{-- <li class="nav-item pt-2">
                        <a class="nav-link text-dark d-flex" data-scroll="" href="#calk">
                            <i class="material-icons text-lg me-2">insert_drive_file</i>
                            <span class="text-sm">Pengaturan CALK</span>
                        </a>
                    </li> --}}
                    <li class="nav-item pt-2">
                        <a class="nav-link text-dark d-flex" data-scroll="" href="#logo">
                            <i class="material-icons text-lg me-2">crop_original</i>
                            <span class="text-sm">Logo</span>
                        </a>
                    </li>
                    <li class="nav-item pt-2">
                        <a class="nav-link text-dark d-flex" data-scroll="" href="#whatsapp">
                            <i class="material-icons text-lg me-2">priority_high</i>
                            <span class="text-sm">Whatsapp</span>
                        </a>
                    </li>
                    @if (in_array('personalisasi_sop.scan_whatsapp', session('tombol') ?? []))
                        <li class="nav-item pt-2">
                            <a class="nav-link text-dark d-flex" data-scroll="" href="#whatsapp">
                                <i class="material-icons text-lg me-2">qr_code_scanner</i>
                                <span class="text-sm">Scan WA</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="col-lg-9 mt-lg-0 mt-4">
            <div class="card" id="lembaga">
                <div class="card-header">
                    <h5 class="mb-0">Identitas Lembaga</h5>
                </div>
                <div class="card-body pt-0">
                    @include('sop.partials._lembaga')
                </div>
            </div>
            <div class="card mt-4" id="pengelola">
                <div class="card-header">
                    <h5 class="mb-0">Sebutan Pengelola Lembaga</h5>
                </div>
                <div class="card-body pt-0">
                    @include('sop.partials._pengelola')
                </div>
            </div>
            <div class="card mt-4" id="pinjaman">
                <div class="card-header">
                    <h5 class="mb-0">Sistem Pinjaman</h5>
                </div>
                <div class="card-body pt-0">
                    @include('sop.partials._pinjaman')
                </div>
            </div>
            <div class="card mt-4" id="kolek">
                <div class="card-header">
                    <h5 class="mb-0">Kolektabilitas</h5>
                </div>
                <div class="card-body pt-0">
                    @include('sop.partials._kolek')
                </div>
            </div>
                    @if (session('lokasi') == 1 || session('lokasi') == 3)
            <div class="card mt-4" id="simpanan">
                <div class="card-header">
                    <h5 class="mb-0">Sistem Simpanan</h5>
                </div>
                <div class="card-body pt-0">
                    @include('sop.partials._simpanan')
                </div>
            </div>
                   @endif
            <div class="card mt-4" id="asuransi">
                <div class="card-header">
                    <h5 class="mb-0">Pengaturan Asuransi</h5>
                </div>
                <div class="card-body pt-0">
                    @include('sop.partials._asuransi')
                </div>
            </div>
            <div class="card mt-4" id="redaksi_spk">
                <div class="card-header">
                    <h5 class="mb-0">Redaksi Dokumen SPK</h5>
                </div>
                <div class="card-body pt-0">
                    @include('sop.partials._spk')
                </div>
            </div>
            <div class="card mt-4" id="logo">
                <div class="card-header">
                    <h5 class="mb-0">Upload Logo</h5>
                </div>
                <div class="card-body pt-0">
                    @include('sop.partials._logo')
                </div>
            </div>
            <div class="card mt-4" id="whatsapp">
                <div class="card-header">
                    <h5 class="mb-0">Pengaturan Whatsapp</h5>
                </div>
                <div class="card-body pt-0">
                    @include('sop.partials._whatsapp')
                </div>
            </div>
        </div>

        {{-- Modal Scan Whatsapp --}}
        <div class="modal fade" id="ModalScanWA" tabindex="-1" aria-labelledby="ModalScanWALabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="ModalScanWALabel">
                            Scan WhatsApp Gateway
                        </h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="LayoutModalScanWA">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-xl-5 col-lg-6 text-center">
                                            <img class="w-100 border-radius-lg shadow-lg mx-auto"
                                                src="/assets/img/no_image.png" id="QrCode" alt="QR Code">
                                            <p id="PairingCode" class="mt-2 fw-bold" style="display:none"></p>
                                        </div>
                                        <div class="col-lg-5 mx-auto">
                                            <h3 class="mt-lg-0 mt-4">Scan kode QR</h3>
                                            <p class="text-sm text-muted">Buka WhatsApp di HP &rarr; Settings &rarr; Linked Devices &rarr; Link a Device.</p>
                                            <p class="text-sm text-info">QR akan auto-refresh setiap 30 detik.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form action="/pengaturan/whatsapp/{{ $kec->id }}" method="post" id="FormWhatsapp">
        @csrf
    </form>
@endsection

@section('script')
    <script>
        const API = '{{ $api }}'
        const MASTER_KEY = '{{ $api_key }}'
        const LOKASI_ID = '{{ $kec->id }}'
        const TOKEN = '{{ $token ?? '' }}'
        const INSTANCE_TOKEN = '{{ $instance_token ?? '' }}'

        let pollTimer = null;
        let refreshTimer = null;

        $(document).ready(function() {
            $.get('/pengaturan/whatsapp/status/' + LOKASI_ID, function(res) {
                if (res.status === 'connected') {
                    $('#HapusWa').show()
                    $('#ScanWA').hide()
                } else {
                    $('#ScanWA').show().prop('disabled', false)
                    $('#HapusWa').hide()
                }
            }).fail(function() {
                $('#ScanWA').show().prop('disabled', false)
                $('#HapusWa').hide()
            })
        })

        function setScanLoading(on) {
            $('#ScanWA').prop('disabled', on);
            $('#ScanWA .btn-text').text(on ? 'Memuat...' : 'Scan WA Gateway');
        }

        function initWa() {
            setScanLoading(true);
            $.ajax({
                url: '/pengaturan/whatsapp/init',
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function(res) {
                    setScanLoading(false);
                    if (!res.success) {
                        Toastr('error', res.message);
                        return;
                    }
                    if (res.base64) {
                        $('#QrCode').attr('src', res.base64);
                        $('#PairingCode').hide();
                        if (res.pairingCode) {
                            $('#PairingCode').text('Kode: ' + res.pairingCode).show();
                        }
                        $('#ModalScanWA').modal('show');
                        startPolling();
                    } else {
                        Toastr('error', 'QR tidak tersedia. Coba lagi.');
                    }
                },
                error: function(xhr) {
                    setScanLoading(false);
                    Toastr('error', 'Gateway error: ' + xhr.status);
                }
            });
        }

        function startPolling() {
            pollTimer = setInterval(function() {
                $.get('/pengaturan/whatsapp/status/' + LOKASI_ID, function(res) {
                    if (res.status === 'connected') {
                        clearInterval(pollTimer);
                        clearTimeout(refreshTimer);
                        Toastr('success', 'WhatsApp Terhubung!');
                        $('#ModalScanWA').modal('hide');
                        $('#HapusWa').show();
                        $('#ScanWA').hide();
                        location.reload();
                    }
                });
            }, 3000);

            refreshTimer = setTimeout(function() {
                if (pollTimer) initWa();
            }, 30000);
        }

        $(document).on('click', '#ScanWA', function(e) {
            e.preventDefault();
            initWa();
        });

        $(document).on('click', '#HapusWa', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Hapus Koneksi WA',
                text: 'Putuskan koneksi WhatsApp ini?',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal',
                icon: 'warning'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('/pengaturan/whatsapp/delete_session', {
                        _token: '{{ csrf_token() }}',
                        lokasi: LOKASI_ID
                    }, function(res) {
                        window.location.reload();
                    });
                }
            });
        });
    </script>

    <script>
        var tahun = "{{ date('Y') }}"
        var bulan = "{{ date('m') }}"

        $(".money").maskMoney();
        new Choices($('#pembulatan')[0], {
            shouldSort: false,
            fuseOptions: {
                threshold: 0.1,
                distance: 1000
            }
        })
        new Choices($('#sistem')[0], {
            shouldSort: false,
            fuseOptions: {
                threshold: 0.1,
                distance: 1000
            }
        })
        new Choices($('#jenis_asuransi')[0], {
            shouldSort: false,
            fuseOptions: {
                threshold: 0.1,
                distance: 1000
            }
        })

        new Choices($('#hitung_bunga')[0], {
            shouldSort: false,
            fuseOptions: {
                threshold: 0.1,
                distance: 1000
            }
        })

        var quill = new Quill('#editor', {
            theme: 'snow'
        });

        $(document).on('click', '.btn-simpan', async function(e) {
            e.preventDefault()

            if ($(this).attr('id') == 'SimpanSPK') {
                await $('#spk').val(quill.container.firstChild.innerHTML)
            }

            var form = $($(this).attr('data-target'))
            $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize(),
                success: function(result) {
                    if (result.success) {
                        Toastr('success', result.msg)

                        if (result.nama_lembaga) {
                            $('#nama_lembaga_sort').html(result.nama_lembaga)
                        }
                    }
                },
                error: function(result) {
                    const respons = result.responseJSON;

                    Swal.fire('Error', 'Cek kembali input yang anda masukkan', 'error')
                    $.map(respons, function(res, key) {
                        $('#' + key).parent('.input-group.input-group-static').addClass(
                            'is-invalid')
                        $('#msg_' + key).html(res)
                    })
                }
            })
        })

        $(document).on('click', '#EditLogo', function(e) {
            e.preventDefault()

            $('#logo_kec').trigger('click')
        })

        $(document).on('change', '#logo_kec', function(e) {
            e.preventDefault()

            var logo = $(this).get(0).files[0]
            if (logo) {
                var form = $('#FormLogo')
                var formData = new FormData(document.querySelector('#FormLogo'));
                $.ajax({
                    type: form.attr('method'),
                    url: form.attr('action'),
                    data: formData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(result) {
                        if (result.success) {
                            var reader = new FileReader();

                            reader.onload = function() {
                                $("#previewLogo").attr("src", reader.result);
                                $(".colored-shadow").css('background-image',
                                    "url(" + reader.result + ")")
                            }

                            reader.readAsDataURL(logo);
                            Toastr('success', result.msg)
                        } else {
                            Toastr('error', result.msg)
                        }
                    }
                })
            }
        })
    </script>
@endsection
