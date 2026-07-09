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
                                        </div>
                                        <div class="col-lg-5 mx-auto">
                                            <h3 class="mt-lg-0 mt-4">Scan kode QR</h3>
                                            <p class="text-sm text-muted">Buka WhatsApp di HP &rarr; Perangkat Tertaut &rarr; Tautkan Perangkat.</p>
                                            <ul class="list-group list-group-flush rounded" id="ListConnection">
                                                <li class="list-group-item">Menunggu QR Code...</li>
                                            </ul>
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

    <form action="/pengaturan/whatsapp/{{ $token }}" method="post" id="FormWhatsapp">
        @csrf
    </form>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.7.5/socket.io.min.js"></script>

    <script>
        const API = '{{ $api }}'
        const MASTER_KEY = '{{ $api_key }}'
        const SAVED_ID = '{{ $device_id ?? '' }}'
        const SAVED_KEY = '{{ $device_key ?? '' }}'
        const KODE_KEC = '{{ $kec->nama_lembaga_sort ?? $kec->nama_kec ?? "BUMDESMA" }}'
        const CURRENT_ID = SAVED_ID || '{{ $token }}'
        const ListContainer = $('#ListConnection')
        let deviceSocket = null

        function initDeviceSocket(deviceId, deviceKey) {
            if (deviceSocket) {
                deviceSocket.disconnect()
                deviceSocket = null
            }

            deviceSocket = io(API, {
                query: {
                    device_id: deviceId,
                    api_key: deviceKey,
                },
                transports: ['polling'],
                reconnection: false,
                timeout: 5000,
            })

            deviceSocket.on('connect', () => {
                console.log('[WA] socket device terkoneksi')
            })

            deviceSocket.on('connect_error', (err) => {
                console.log('[WA] socket error:', err.message)
            })

            deviceSocket.on('qr', (data) => {
                if (data && data.qr_image) {
                    $('#QrCode').attr('src', data.qr_image)
                }
            })

            deviceSocket.on('ready', () => {
                ListContainer.html(
                    '<li class="list-group-item list-group-item-success fw-bold">WhatsApp Aktif</li>'
                )
                MultiToast('success', 'WhatsApp Gateway berhasil terhubung.')
                setTimeout(function() {
                    $('#ModalScanWA').modal('hide')
                }, 1500)
            })

            deviceSocket.on('status', (data) => {
                if (data && data.status === 'disconnected') {
                    ListContainer.html(
                        '<li class="list-group-item list-group-item-danger fw-bold">WhatsApp Terputus</li>'
                    )
                    MultiToast('warning', 'WhatsApp Gateway terputus.')
                }
            })
        }

        const IS_LOCAL_GATEWAY = API.indexOf('localhost') !== -1 || API.indexOf('127.0.0.1') !== -1

        $(document).on('click', '#ScanWA', function(e) {
            e.preventDefault()

            Swal.fire({
                title: 'Scan WhatsApp Gateway?',
                text: 'Buat device baru dan tampilkan QR code untuk dipindai.',
                showCancelButton: true,
                confirmButtonText: 'Scan',
                cancelButtonText: 'Batal',
                icon: 'question',
            }).then((r) => {
                if (!r.isConfirmed) return

                ListContainer.html('<li class="list-group-item">Membuat device...</li>')
                $('#QrCode').attr('src', '/assets/img/no_image.png')
                $('#ModalScanWA').modal('show')

                $.ajax({
                    type: 'POST',
                    url: API + '/api/devices',
                    headers: { 'Content-Type': 'application/json', 'x-api-key': MASTER_KEY },
                    data: JSON.stringify({ device_id: CURRENT_ID, name: 'siupk-' + KODE_KEC }),
                    success: function(result) {
                        if (result && (result.device_id || result.id)) {
                            const newId = result.device_id || result.id
                            const newKey = result.device_key || result.api_key || result.key || ''
                            initDeviceSocket(newId, newKey || newId)

                            $.post('/pengaturan/whatsapp/save_device', {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                device_id: newId,
                                device_key: newKey,
                            }, function(saveRes) {
                                if (!saveRes.success) {
                                    Swal.fire('Peringatan', saveRes.msg || 'Gagal menyimpan device.', 'warning')
                                }
                            }).fail(function() {
                                Swal.fire('Peringatan', 'Gagal menyimpan device ke server SIUPK.', 'warning')
                            })
                        } else {
                            ListContainer.html(
                                '<li class="list-group-item list-group-item-danger fw-bold">Response tidak valid dari gateway.</li>'
                            )
                        }
                    },
                    error: function(xhr) {
                        const status = xhr && xhr.status
                        let msg = 'Gagal membuat device di gateway.'

                        if (xhr.responseJSON && (xhr.responseJSON.msg || xhr.responseJSON.message)) {
                            msg = xhr.responseJSON.msg || xhr.responseJSON.message
                        } else if (status === 0 || xhr.statusText === 'error') {
                            msg = IS_LOCAL_GATEWAY
                                ? 'WhatsApp Gateway tidak aktif di lingkungan lokal. Fitur ini hanya berjalan di server produksi.'
                                : 'Tidak dapat terhubung ke WhatsApp Gateway. Periksa koneksi internet Anda.'
                        }

                        ListContainer.html(
                            '<li class="list-group-item list-group-item-danger fw-bold">' + msg + '</li>'
                        )

                        if (typeof MultiToast === 'function') {
                            MultiToast('warning', msg)
                        }
                    },
                })
            })
        })

        $(document).on('click', '#HapusWa', function(e) {
            e.preventDefault()

            Swal.fire({
                title: 'Putuskan Koneksi WhatsApp?',
                text: 'Koneksi WhatsApp Gateway akan dihapus.',
                showCancelButton: true,
                confirmButtonText: 'Putuskan',
                cancelButtonText: 'Batal',
                icon: 'warning',
            }).then((result) => {
                if (!result.isConfirmed) return

                const doLogout = function() {
                    $.post('/pengaturan/whatsapp/delete_session', {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                    }, function(res) {
                        if (deviceSocket) {
                            deviceSocket.disconnect()
                            deviceSocket = null
                        }
                        MultiToast('success', res.msg || 'Koneksi WhatsApp dihapus.')
                        setTimeout(() => location.reload(), 800)
                    }).fail(function() {
                        Swal.fire('Error', 'Gagal menghubungi server SIUPK.', 'error')
                    })
                }

                if (SAVED_ID) {
                    $.ajax({
                        type: 'POST',
                        url: API + '/api/devices/' + SAVED_ID + '/logout',
                        headers: { 'x-api-key': SAVED_KEY || MASTER_KEY },
                        complete: doLogout,
                    })
                } else {
                    doLogout()
                }
            })
        })

        if (SAVED_ID) {
            $.ajax({
                type: 'GET',
                url: API + '/api/devices/' + SAVED_ID,
                headers: { 'x-api-key': SAVED_KEY || MASTER_KEY },
                success: function(result) {
                    if (result && result.connected) {
                        initDeviceSocket(SAVED_ID, SAVED_KEY)
                        ListContainer.html(
                            '<li class="list-group-item list-group-item-success fw-bold">WhatsApp Aktif</li>'
                        )
                    } else {
                        ListContainer.html(
                            '<li class="list-group-item list-group-item-warning fw-bold">WhatsApp Belum Terhubung</li>'
                        )
                    }
                },
                error: function() {
                    ListContainer.html(
                        '<li class="list-group-item list-group-item-warning fw-bold">Status device tidak diketahui</li>'
                    )
                },
            })
        }
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
