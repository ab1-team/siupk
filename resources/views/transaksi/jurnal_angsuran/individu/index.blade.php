@extends('layouts.base')

@section('content')
    <div class="row">
        <div class="col-12" id="notif">

        </div>
        <div class="col-md-8 mb-3">
            <div class="card mb-3">
                <div class="card-body py-2">
                    <form action="/transaksi/angsuran_individu" method="post" id="FormAngsuranIndividu">
                        @csrf

                        <input type="hidden" name="id" id="id" value="{{ Request::get('pinkel') ?: 0 }}">
                        <input type="hidden" name="_pokok" id="_pokok">
                        <input type="hidden" name="_jasa" id="_jasa">
                        <input type="hidden" name="tgl_pakai_aplikasi" id="tgl_pakai_aplikasi"
                            value="{{ $kec->tgl_pakai }}">
                        <div class="row">
                            <div class="col-12">
                                <div class="input-group input-group-static my-3">
                                    <label for="tgl_transaksi">Tanggal Transaksi</label>
                                    <input autocomplete="off" type="text" name="tgl_transaksi" id="tgl_transaksi"
                                        class="form-control{{ session('lokasi') != 197 ? ' date' : '' }}"
                                        value="{{ date('d/m/Y') }}"
                                        @if(session('lokasi') == 197) readonly @endif>
                                    <small class="text-danger" id="msg_tgl_transaksi"></small>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="input-group input-group-static my-3">
                                    <label for="pokok">Pokok</label>
                                    <input autocomplete="off" type="text" name="pokok" id="pokok"
                                        class="form-control">
                                    <small class="text-danger" id="msg_pokok"></small>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="input-group input-group-static my-3">
                                    <label for="jasa">Jasa</label>
                                    <input autocomplete="off" type="text" name="jasa" id="jasa"
                                        class="form-control">
                                    <small class="text-danger" id="msg_jasa"></small>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="input-group input-group-static my-3">
                                    <label for="denda">Denda</label>
                                    <input autocomplete="off" type="text" name="denda" id="denda"
                                        class="form-control">
                                    <small class="text-danger" id="msg_denda"></small>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="input-group input-group-static my-3">
                                    <label for="total">Total Bayar</label>
                                    <input autocomplete="off" readonly disabled type="text" name="total" id="total"
                                        class="form-control">
                                    <small class="text-danger" id="msg_total"></small>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="d-flex justify-content-end">
                        <button type="button" id="btnDetailIndividu" class="btn btn-info btn-sm me-3">
                            Detail Pemanfaat <span class="badge badge-info" id="loan-id"></span>
                        </button>
                        <button type="button" id="SimpanAngsuran" class="btn btn-github btn-sm">Posting</button>
                    </div>
                </div>
            </div>

            <div class="card card-body p-2 pb-0 mb-3">
                <div class="row">
                    <div class="col-4">
                        <div class="d-grid">
                            <a id="cetakKartuAngsuran" class="btn btn-success btn-sm mb-2">Kartu</a>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="d-grid">
                            <button class="btn btn-danger btn-sm mb-2" id="btnDetailAngsuran">Detail</button>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="d-grid">
                            <button class="btn btn-info btn-sm mb-2" id=cetakLPP>LPP per bulan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="nav-wrapper position-relative end-0">
                <ul class="nav nav-pills nav-fill p-1" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link mb-0 px-0 py-1 active" data-bs-toggle="tab" href="#Pokok" role="tab"
                            aria-controls="Pokok" aria-selected="true">
                            Pokok
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#Jasa" role="tab"
                            aria-controls="Jasa" aria-selected="false">
                            Jasa
                        </a>
                    </li>
                </ul>

                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="Pokok" role="tabpanel" aria-labelledby="Pokok">
                        <div class="card card-body p-2">
                            <canvas id="chartP"></canvas>
                            <div class="d-flex justify-content-between mt-3 mb-1 mx-3 text-sm fw-bold">
                                <span>Alokasi</span>
                                <span id="alokasi_pokok"></span>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="Jasa" role="tabpanel" aria-labelledby="Jasa">
                        <div class="card card-body p-2">
                            <canvas id="chartJ"></canvas>
                            <div class="d-flex justify-content-between mt-3 mb-1 mx-3 text-sm fw-bold">
                                <span>Jasa</span>
                                <span id="alokasi_jasa"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="DetailIndividu" tabindex="-1" aria-labelledby="DetailIndividuLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="DetailIndividuLabel">

                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="LayoutDetailIndividu"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="DetailAngsuran" tabindex="-1" aria-labelledby="DetailAngsuranLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="DetailAngsuranLabel">

                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="LayoutDetailAngsuran"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info btn-sm" id="cetakBuktiAngsuran">
                        Cetak Bukti Angsuran
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="BuktiAngsuran" tabindex="-1" aria-labelledby="BuktiAngsuranLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="BuktiAngsuranLabel">

                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="LayoutBuktiAngsuran"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="BtnCetakBkm" class="btn btn-info btn-sm">
                        Print
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="tutupBuktiAngsuran">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="AngsuranAnggota" tabindex="-1" aria-labelledby="AngsuranAnggotaLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="AngsuranAnggotaLabel">

                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="LayoutAngsuranAnggota"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <form action="/transaksi/hapus" method="post" id="formHapusAngsuranI">
        @csrf

        <input type="hidden" name="del_idt" id="del_idt_angsuran_i">
        <input type="hidden" name="del_idtp" id="del_idtp_angsuran_i">
        <input type="hidden" name="del_id_pinj" id="del_id_pinj_angsuran_i">
        <input type="hidden" name="del_id_pinj_i" id="del_id_pinj_i_angsuran_i">
    </form>
@endsection

@section('script')
    <script>
        var formatter = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })

        var pokok, jasa = 0;

        $("#pokok").maskMoney({
            allowNegative: true
        });

        $("#jasa").maskMoney({
            allowNegative: true
        });

        $("#denda").maskMoney({
            allowNegative: true
        });

        $("#total").maskMoney({
            allowNegative: true
        });


        $(".date").flatpickr({
            dateFormat: "d/m/Y"
        })

        var id_pinkel = "{{ Request::get('pinkel') ?: 0 }}"

        if (id_pinkel > 0) {
            var ch_pokok = document.getElementById('chartP').getContext("2d");
            var ch_jasa = document.getElementById('chartJ').getContext("2d");

            $.get('/transaksi/form_angsuran_individu/' + id_pinkel, function(result) {
                angsuran(false, result)

                makeChart('pokok', ch_pokok, result.sisa_pokok, result.sum_pokok)
                makeChart('jasa', ch_jasa, result.sisa_jasa, result.sum_jasa)

                $('#loan-id').html(id_pinkel)
            })
        }

        $(document).on('change', '#tgl_transaksi', function(e) {
            var tanggal = $(this).val()
            var id_pinj = $('#id').val()

            $.get('/transaksi/angsuran_individu/target/' + id_pinj, {
                tanggal
            }, function(result) {
                $('#pokok').val(formatter.format(result.saldo_pokok))
                $('#jasa').val(formatter.format(result.saldo_jasa))
            })
        })

        $(document).on('change', '#pokok,#jasa,#denda', function(e) {
            var pokok = $('#pokok').val()
            var jasa = $('#jasa').val()
            var denda = $('#denda').val()

            pokok = parseFloat(pokok.split(',').join('').split('.00').join(''))
            if (!pokok) {
                pokok = 0;
                $('#pokok').val(formatter.format('0'))
            }

            jasa = parseFloat(jasa.split(',').join('').split('.00').join(''))
            if (!jasa) {
                jasa = 0;
                $('#jasa').val(formatter.format('0'))
            }

            denda = parseFloat(denda.split(',').join('').split('.00').join(''))
            if (!denda) {
                $('#denda').val(formatter.format('0'))
                denda = 0;
            }

            var total = pokok + jasa + denda
            $('#total').val(formatter.format(total))
        })

        $(document).on('click', '#SimpanAngsuran', function(e) {
            $('#notif').html('')
            e.preventDefault()

            var sisa_pokok = $('#_pokok').val()
            var pokok = $('#pokok').val()
            pokok = parseFloat(pokok.split(',').join('').split('.00').join(''))

            var next = true
            if (pokok > 0 && pokok > sisa_pokok) {
                Swal.fire('Error', 'Angsuran pokok tidak boleh melebihi saldo pinjaman saat ini.', 'warning')
                return false

                next = false
            }

            if (next == true) {
                var form = $('#FormAngsuranIndividu')

                var loading = Swal.fire({
                    title: "Mohon Menunggu..",
                    html: "Memproses transaksi angsuran.",
                    timerProgressBar: true,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                })

                $.ajax({
                    type: 'POST',
                    url: form.attr('action'),
                    data: form.serialize(),
                    success: function(result) {
                        var ch_pokok = document.getElementById('chartP').getContext("2d");
                        var ch_jasa = document.getElementById('chartJ').getContext("2d");

                        loading.close()
                        if (result.success) {
                            $.get('/angsuran/notifikasi_i/' + result.idtp, function(res) {
                                $('#notif').html(res.view)
                            })

                            Swal.fire('Berhasil!', result.msg, 'success').then(() => {
                                $.get('/transaksi/form_angsuran_individu/' + result
                                    .id_pinj_i,
                                    function(result) {
                                        angsuran(true, result)

                                        makeChart('pokok', ch_pokok, result
                                            .sisa_pokok, result
                                            .sum_pokok)
                                        makeChart('jasa', ch_jasa, result
                                            .sisa_jasa,
                                            result.sum_jasa)
                                    })
                            })

                            if (result.whatsapp) {
                                sendMsg(result.number, result.nama_kelompok, result
                                    .pesan)
                            }
                        } else {
                            loading.close()
                            Swal.fire('Error', result.msg, 'warning')
                        }
                    },
                    error: function(e) {
                        loading.close()
                        Swal.fire('Error', '', 'warning')
                    }
                })
            }
        })

        $(document).on('click', '#cetakKartuAngsuran', function(e) {
            e.preventDefault()
            var id_pinj = $('#id').val()

            open_window('/perguliran_i/dokumen/kartu_angsuran/' + id_pinj)
        })

        $(document).on('click', '#cetakLPP', function(e) {
            e.preventDefault()
            var id_pinj = $('#id').val()

            open_window('/transaksi/angsuran_i/lpp/' + id_pinj)
        })

        $(document).on('click', '#btnDetailIndividu', function(e) {
            var id = $('#id').val()

            $.get('/database/anggota/detail_anggota/' + id, function(result) {
                $('#DetailIndividu').modal('show')

                $('#DetailIndividuLabel').html(result.label)
                $('#LayoutDetailIndividu').html(result.view)
            })
        })

        $(document).on('click', '#btnDetailAngsuran', function(e) {
            var id = $('#id').val()

            $.get('/transaksi/angsuran/detail_angsuran_i/' + id, function(result) {
                $('#DetailAngsuran').modal('show')

                $('#DetailAngsuranLabel').html(result.label)
                $('#LayoutDetailAngsuran').html(result.view)

                $('#BuktiAngsuranLabel').html(result.label_cetak)
                $('#LayoutBuktiAngsuran').html(result.cetak)
            })
        })

        $(document).on('click', '#cetakBuktiAngsuran, #tutupBuktiAngsuran', function(e) {
            e.preventDefault()

            $('#BuktiAngsuran').modal('toggle');
        })

        $(document).on('click', '#BtnCetakBkm', function(e) {
            e.preventDefault()

            $('#FormCetakBuktiAngsuran').attr('action', '/transaksi/angsuran/cetak_bkm');
            $('#FormCetakBuktiAngsuran').submit();
        })

        $(document).on('click', '#btnAngsuranAnggota', function(e) {
            e.preventDefault()
            $('#AngsuranAnggota').modal('show')
        })

        $(document).on('click', '.btn-link', function(e) {
            var action = $(this).attr('data-action')

            open_window(action)
        })

        $(document).on('click', '.btn-struk', function(e) {
            e.preventDefault()

            var idtp = $(this).attr('data-idtp')
            Swal.fire({
                title: "Cetak Kuitansi Angsuran",
                showDenyButton: true,
                confirmButtonText: "Biasa",
                denyButtonText: "Dot Matrix",
                confirmButtonColor: "#3085d6",
                denyButtonColor: "#3085d6",
            }).then((result) => {
                if (result.isConfirmed) {
                    open_window('/transaksi/angsuran/struk/' + idtp)
                } else if (result.isDenied) {
                    open_window('/transaksi/angsuran/struk_matrix/' + idtp)
                }
            });
        })

        $(document).on('click', '.btn-delete-angsuran', function(e) {
            e.preventDefault()

            var idt = $(this).attr('data-idt')
            var idtp = $(this).attr('data-idtp')
            var id_pinj = $(this).attr('data-id-pinj')
            var id_pinj_i = $(this).attr('data-id-pinj-i')

            $('#del_idt_angsuran_i').val(idt)
            $('#del_idtp_angsuran_i').val(idtp)
            $('#del_id_pinj_angsuran_i').val(id_pinj)
            $('#del_id_pinj_i_angsuran_i').val(id_pinj_i)

            Swal.fire({
                title: 'Peringatan',
                text: 'Setelah menekan tombol Hapus Transaksi, maka transaksi ini akan dihapus (soft delete) dan data pinjaman akan di-generate ulang.',
                showCancelButton: true,
                confirmButtonText: 'Hapus Transaksi',
                cancelButtonText: 'Batal',
                icon: 'warning',
                confirmButtonColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = $('#formHapusAngsuranI')
                    var id = $('#id').val()
                    var ch_pokok = document.getElementById('chartP').getContext("2d");
                    var ch_jasa = document.getElementById('chartJ').getContext("2d");

                    var loading = Swal.fire({
                        title: "Mohon Menunggu..",
                        html: "Menghapus transaksi & generate ulang pinjaman.",
                        timerProgressBar: true,
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    })

                    $.ajax({
                        type: form.attr('method'),
                        url: form.attr('action'),
                        data: form.serialize(),
                        success: function(result) {
                            if (result.success) {
                                $.get('/transaksi/regenerate_real_i/' + id_pinj_i, function() {
                                    loading.close()
                                    Swal.fire('Berhasil!', result.msg, 'success')
                                        .then(() => {
                                            $.get('/transaksi/angsuran/detail_angsuran_i/' + id,
                                                function(res) {
                                                    $('#LayoutDetailAngsuran').html(res.view)
                                                    $('#LayoutBuktiAngsuran').html(res.cetak)
                                                })
                                            $.get('/transaksi/form_angsuran_individu/' + id,
                                                function(res) {
                                                    angsuran(true, res)
                                                    makeChart('pokok', ch_pokok, res.sisa_pokok, res.sum_pokok)
                                                    makeChart('jasa', ch_jasa, res.sisa_jasa, res.sum_jasa)
                                                })
                                        })
                                })
                            } else {
                                loading.close()
                                Swal.fire('Error', result.msg, 'warning')
                            }
                        },
                        error: function(xhr) {
                            loading.close()
                            var msg = 'Gagal menghapus transaksi.'
                            if (xhr.responseJSON && xhr.responseJSON.msg) {
                                msg = xhr.responseJSON.msg
                            }
                            Swal.fire('Error', msg, 'warning')
                        }
                    })
                }
            })
        })

        function sendMsg(number, nama, msg, repeat = 0) {
            const DEVICE_ID = '{{ $wa_device_id ?? "" }}'
            const DEVICE_KEY = '{{ $wa_device_key ?? "" }}'

            $.ajax({
                type: 'POST',
                url: '{{ $api }}/api/send/text',
                headers: { 'x-api-key': DEVICE_KEY },
                data: { device_id: DEVICE_ID, to: number, message: msg },
                success: function(result) {
                    if (result.success) {
                        MultiToast('success', 'Pesan untuk kelompok ' + nama + ' berhasil dikirim')
                    } else if (repeat < 1) {
                        setTimeout(function() { sendMsg(number, nama, msg, repeat + 1) }, 1000)
                    } else {
                        MultiToast('error', 'Pesan untuk kelompok ' + nama + ' gagal dikirim')
                    }
                },
                error: function() {
                    if (repeat < 1) {
                        setTimeout(function() { sendMsg(number, nama, msg, repeat + 1) }, 1000)
                    } else {
                        MultiToast('error', 'Pesan untuk kelompok ' + nama + ' gagal dikirim')
                    }
                }
            })
        }
    </script>
@endsection
