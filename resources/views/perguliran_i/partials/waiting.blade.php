<form action="/perguliran_i/{{ $perguliran_i->id }}" method="post" id="FormInput">
    @csrf
    @method('PUT')

    @if ($pinj_aktif)
        <div class="alert alert-danger text-white" role="alert">
            <span class="text-sm">
                <b>{{ ucwords(strtolower($pinj_aktif->anggota->namadepan)) }}</b> masih memiliki kewajiban
                angsuran pinjaman dengan
                <a href="/detail_i/{{ $pinj_aktif->id }}" target="_blank" class="alert-link text-white">
                    Loan ID. {{ $pinj_aktif->id }}
                </a>
            </span>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <div class="row mt-0">
                <div class="col-md-4 mb-3">
                    <div class="border border-light border-2 border-radius-md p-3">
                        <h6 class="text-info text-gradient mb-0">
                            Proposal
                        </h6>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Tgl Pengajuan
                                <span class="badge badge-info badge-pill">
                                    {{ Tanggal::tglIndo($perguliran_i->tgl_proposal) }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Pengajuan
                                <span class="badge badge-info badge-pill">
                                    {{ number_format($perguliran_i->proposal) }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Jenis Jasa
                                <span class="badge badge-info badge-pill">
                                    {{ $perguliran_i->jasa->nama_jj }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Jasa
                                <span class="badge badge-info badge-pill">
                                    {{ $perguliran_i->pros_jasa . '% / ' . $perguliran_i->jangka . ' bulan' }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Angs. Pokok
                                <span class="badge badge-info badge-pill">
                                    {{ $perguliran_i->sis_pokok->nama_sistem }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Angs. Jasa
                                <span class="badge badge-info badge-pill">
                                    {{ $perguliran_i->sis_jasa->nama_sistem }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="border border-light border-2 border-radius-md p-3">
                        <h6 class="text-danger text-gradient mb-0">
                            Verified
                        </h6>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Tgl Verifikasi
                                <span class="badge badge-danger badge-pill">
                                    {{ Tanggal::tglIndo($perguliran_i->tgl_verifikasi) }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Verifikasi
                                <span class="badge badge-danger badge-pill">
                                    {{ number_format($perguliran_i->verifikasi) }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Jenis Jasa
                                <span class="badge badge-danger badge-pill">
                                    {{ $perguliran_i->jasa->nama_jj }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Jasa
                                <span class="badge badge-danger badge-pill">
                                    {{ $perguliran_i->pros_jasa . '% / ' . $perguliran_i->jangka . ' bulan' }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Angs. Pokok
                                <span class="badge badge-danger badge-pill">
                                    {{ $perguliran_i->sis_pokok->nama_sistem }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Angs. Jasa
                                <span class="badge badge-danger badge-pill">
                                    {{ $perguliran_i->sis_jasa->nama_sistem }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="border border-light border-2 border-radius-md p-3">
                        <h6 class="text-warning text-gradient mb-0">
                            Waiting
                        </h6>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Tgl Tunggu
                                <span class="badge badge-warning badge-pill">
                                    {{ Tanggal::tglIndo($perguliran_i->tgl_tunggu) }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Pendanaan
                                <span class="badge badge-warning badge-pill">
                                    {{ number_format($perguliran_i->alokasi) }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Jenis Jasa
                                <span class="badge badge-warning badge-pill">
                                    {{ $perguliran_i->jasa->nama_jj }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Jasa
                                <span class="badge badge-warning badge-pill">
                                    {{ $perguliran_i->pros_jasa . '% / ' . $perguliran_i->jangka . ' bulan' }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Angs. Pokok
                                <span class="badge badge-warning badge-pill">
                                    {{ $perguliran_i->sis_pokok->nama_sistem }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Angs. Jasa
                                <span class="badge badge-warning badge-pill">
                                    {{ $perguliran_i->sis_jasa->nama_sistem }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <hr class="horizontal dark">
        </div>
    </div>

    <div class="card card-body p-2 pb-0 mb-3">
        <div class="row">
            <div class="col-12 col-sm-6 col-md-6">
                <div class="d-grid">
                    <button type="button" data-bs-toggle="modal" data-bs-target="#CetakDokumenProposal"
                        class="btn btn-info btn-sm mb-2">Cetak Dokumen Proposal</button>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-6">
                <div class="d-grid">
                    <button type="button" data-bs-toggle="modal" data-bs-target="#CetakDokumenPencairan"
                        class="btn btn-info btn-sm mb-2">Cetak Dokumen Pencairan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header pb-0 p-3">
            <h6>
                Input Realisasi Pencairan
            </h6>
        </div>
        <div class="card-body p-3">
            <input type="hidden" name="_id" id="_id" value="{{ $perguliran_i->id }}">
            <input type="hidden" name="status" id="status" value="A">
            <input type="hidden" name="debet" id="debet" value="{{ $debet->kode_akun }}">
            <div class="row">
                <div class="col-md-4">
                    <div class="input-group input-group-static my-3">
                        <label for="tgl_cair">Tgl Cair</label>
                        <input autocomplete="off" type="text" name="tgl_cair" id="tgl_cair"
                            class="form-control date" value="{{ Tanggal::tglIndo($perguliran_i->tgl_cair) }}">
                        <small class="text-danger" id="msg_tgl_cair"></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group input-group-static my-3">
                        <label for="alokasi">Alokasi Rp.</label>
                        <input autocomplete="off" readonly type="text" name="alokasi" id="alokasi"
                            class="form-control money" value="{{ number_format($perguliran_i->alokasi, 2) }}">
                        <small class="text-danger" id="msg_alokasi"></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="my-2">
                        <label class="form-label" for="sumber_pembayaran">Sumber Pembayaran (Kredit)</label>
                        <select class="form-control" name="sumber_pembayaran" id="sumber_pembayaran">
                            @foreach ($sumber_bayar as $sb)
                                <option value="{{ $sb->kode_akun }}">
                                    {{ $sb->kode_akun }}. {{ $sb->nama_akun }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-danger" id="msg_sistem_angsuran_jasa"></small>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button type="button" id="kembaliProposal" class="btn btn-warning btn-sm"
                    @if (!in_array('perguliran.balik_proposal', Session::get('tombol', [])))
                        disabled
                    @endif
                >
                    Kembalikan Ke Proposal
                </button>
                <button type="button" id="tidakLayak" class="btn btn-danger ms-1 btn-sm"
                    @if (!in_array('perguliran.cairkan', Session::get('tombol', [])))
                        disabled
                    @endif
                >
                    Tidak Layak
                </button>
                <button type="button" id="Simpan" class="btn btn-github ms-1 btn-sm"
                    @if (!in_array('perguliran.cairkan', Session::get('tombol', [])) || $pinj_aktif)
                        disabled
                    @endif
                >
                    Cairkan Sekarang
                </button>
            </div>
        </div>
    </div>
</form>

<form action="/perguliran_i/kembali_proposal/{{ $perguliran_i->id }}" method="post" id="formKembaliProposal">
    @csrf
</form>

<form action="/perguliran_i/tidak_layak/{{ $perguliran_i->id }}" method="post" id="formTidakLayak">
    @csrf
</form>

<script>
    var formatter = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })

    new Choices($('#sumber_pembayaran')[0], {
        shouldSort: false,
        fuseOptions: {
            threshold: 0.1,
            distance: 1000
        }
    })

    $(".money").maskMoney();

    $(".date").flatpickr({
        dateFormat: "d/m/Y"
    })

    $(document).on('click', '#kembaliProposal', function(e) {
        $('#formKembaliProposal').submit()
    })

    $(document).on('click', '#tidakLayak', function(e) {
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Tandai pengajuan ini sebagai Tidak Layak?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Tidak Layak',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33',
        }).then((result) => {
            if (result.isConfirmed) {
                $('#formTidakLayak').submit()
            }
        })
    })

    $(document).on('click', '#Simpan', async function(e) {
        e.preventDefault()
        $('small').html('')

        var lanjut = true;
        lanjut = await Swal.fire({
            title: 'Peringatan',
            text: 'Anda akan melakukan Pencairan Piutang sebesar Rp. ' + $('#alokasi').val().split(
                    '.00').join('') +
                ' untuk kelompok tersebut? Setelah klik tombol Lanjutkan data tidak dapat diubah kembali !',
            showCancelButton: true,
            confirmButtonText: 'Lanjutkan',
            cancelButtonText: 'Batal',
            icon: 'warning'
        }).then((result) => {
            if (result.isConfirmed) {
                return true
            }

            return false
        })

        if (lanjut) {
            var form = $('#FormInput')
            $.ajax({
                type: 'POST',
                url: form.attr('action') + '?save=true',
                data: form.serialize(),
                success: function(result) {
                    if (result.success) {
                        Swal.fire('Berhasil', result.msg, 'success').then(() => {
                            window.location.href = '/detail_i/' + result.id
                        })
                    } else {
                        Swal.fire('Error', result.msg, 'error')
                    }
                },
                error: function(result) {
                    const respons = result.responseJSON;

                    Swal.fire('Error', 'Cek kembali input yang anda masukkan', 'error')
                    $.map(respons, function(res, key) {
                        $('#' + key).parent('.input-group.input-group-static')
                            .addClass(
                                'is-invalid')
                        $('#msg_' + key).html(res)
                    })
                }
            })
        }
    })
</script>
