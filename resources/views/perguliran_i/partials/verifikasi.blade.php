<div class="card mb-3">
    <div class="card-body">
        <div class="row mt-0">
            <div class="col-md-6 mb-3">
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
            <div class="col-md-6 mb-3">
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
        </div>

        <hr class="horizontal dark">

    </div>
</div>

<div class="card card-body p-2 pb-0 mb-3">
    <form action="/perguliran_i/dokumen?status=P" target="_blank" method="post">
        @csrf

        <input type="hidden" name="id" value="{{ $perguliran_i->id }}">
        <div class="d-grid">
            <button type="submit" name="report" value="RekomendasiVerifikator#pdf" class="btn btn-info btn-sm mb-2">Cetak Rekomendasi Verifikator/Analis</button>
        </div>
        <div class="d-grid">
            <button type="button" data-bs-toggle="modal" data-bs-target="#CetakDokumenProposal"
                class="btn btn-success btn-sm mb-2" style="background-color: green;">Cetak Dokumen Proposal</button>
        </div>

    </form>
</div>

<form action="/perguliran_i/{{ $perguliran_i->id }}" method="post" id="FormInput">
    @csrf
    @method('PUT')

    <div class="card mb-3">
        <div class="card-header pb-0 p-3">
            <h6>
                Input Keputusan Pendanaan
            </h6>
        </div>
        <div class="card-body p-3">
            <input type="hidden" name="_id" id="_id" value="{{ $perguliran_i->id }}">
            <input type="hidden" name="status" id="status" value="W">
            <div class="row">
                <div class="col-md-3">
                    <div class="input-group input-group-static my-3">
                        <label for="tgl_tunggu">Tgl Tunggu</label>
                        <input autocomplete="off" type="text" name="tgl_tunggu" id="tgl_tunggu"
                            class="form-control date" value="{{ Tanggal::tglIndo($perguliran_i->tgl_verifikasi) }}">
                        <small class="text-danger" id="msg_tgl_tunggu"></small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-static my-3">
                        <label for="alokasi">Alokasi Rp.</label>
                        <input autocomplete="off" type="text" name="alokasi" id="alokasi"
                            class="form-control money" value="{{ number_format($perguliran_i->verifikasi, 2) }}">
                        <small class="text-danger" id="msg_alokasi"></small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-static my-3">
                        <label for="jangka">Jangka</label>
                        <input autocomplete="off" type="number" name="jangka" id="jangka" class="form-control"
                            value="{{ $perguliran_i->jangka }}">
                        <small class="text-danger" id="msg_jangka"></small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-static my-3">
                        <label for="pros_jasa">Prosentase Jasa (%)</label>
                        <input autocomplete="off" type="number" name="pros_jasa" id="pros_jasa" class="form-control"
                            value="{{ $perguliran_i->pros_jasa }}">
                        <small class="text-danger" id="msg_pros_jasa"></small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="my-2">
                        <label class="form-label" for="jenis_jasa">Jenis Jasa</label>
                        <select class="form-control" name="jenis_jasa" id="jenis_jasa">
                            @foreach ($jenis_jasa as $jj)
                                <option {{ $jj->id == $perguliran_i->jenis_jasa ? 'selected' : '' }}
                                    value="{{ $jj->id }}">
                                    {{ $jj->nama_jj }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-danger" id="msg_jenis_jasa"></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="my-2">
                        <label class="form-label" for="sistem_angsuran_pokok">Sistem Angs. Pokok</label>
                        <select class="form-control" name="sistem_angsuran_pokok" id="sistem_angsuran_pokok">
                            @foreach ($sistem_angsuran as $sa)
                                <option {{ $sa->id == $perguliran_i->sistem_angsuran ? 'selected' : '' }}
                                    value="{{ $sa->id }}">
                                    {{ $sa->nama_sistem }} ({{ $sa->deskripsi_sistem }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-danger" id="msg_sistem_angsuran_pokok"></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="my-2">
                        <label class="form-label" for="sistem_angsuran_jasa">Sistem Angs. Jasa</label>
                        <select class="form-control" name="sistem_angsuran_jasa" id="sistem_angsuran_jasa">
                            @foreach ($sistem_angsuran as $sa)
                                <option {{ $sa->id == $perguliran_i->sa_jasa ? 'selected' : '' }}
                                    value="{{ $sa->id }}">
                                    {{ $sa->nama_sistem }} ({{ $sa->deskripsi_sistem }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-danger" id="msg_sistem_angsuran_jasa"></small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group input-group-static my-3">
                        <label for="tgl_cair">Tanggal Cair</label>
                        <input autocomplete="off" type="text" name="tgl_cair" id="tgl_cair"
                            class="form-control date" value="{{ Tanggal::tglIndo($perguliran_i->tgl_verifikasi) }}">
                        <small class="text-danger" id="msg_tgl_cair"></small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group input-group-static my-3">
                        <label for="nomor_spk">Nomor SPK</label>
                        <input autocomplete="off" type="text" name="nomor_spk" id="nomor_spk"
                            class="form-control" value="{{ $perguliran_i->spk_no }}">
                        <small class="text-danger" id="msg_nomor_spk"></small>
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
                    @if (!in_array('perguliran.simpan_dana', Session::get('tombol', [])))
                        disabled
                    @endif
                >
                    Tidak Layak
                </button>
                <button type="button" id="Simpan" class="btn btn-github ms-1 btn-sm"
                    @if (!in_array('perguliran.simpan_dana', Session::get('tombol', [])))
                        disabled
                    @endif
                >
                    Simpan Keputusan Pendanaan
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

    new Choices($('#jenis_jasa')[0], {
        shouldSort: false,
        fuseOptions: {
            threshold: 0.1,
            distance: 1000
        }
    })
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

        var form = $('#FormInput')
        $.ajax({
            type: 'POST',
            url: form.attr('action') + '?save=true',
            data: form.serialize(),
            success: function(result) {
                Swal.fire('Berhasil', result.msg, 'success').then(() => {
                    window.location.href = '/detail_i/' + result.id
                })
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
    })
</script>
