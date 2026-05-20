<form action="/perguliran/{{ $perguliran->id }}" method="post" id="FormInput">
    @csrf
    @method('PUT')

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
                                    {{ Tanggal::tglIndo($perguliran->tgl_proposal) }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Pengajuan
                                <span class="badge badge-info badge-pill">
                                    {{ number_format($perguliran->proposal) }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Jenis Jasa
                                <span class="badge badge-info badge-pill">
                                    {{ $perguliran->jasa->nama_jj }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Jasa
                                <span class="badge badge-info badge-pill">
                                    {{ $perguliran->pros_jasa . '% / ' . $perguliran->jangka . ' bulan' }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Angs. Pokok
                                <span class="badge badge-info badge-pill">
                                    {{ $perguliran->sis_pokok->nama_sistem }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Angs. Jasa
                                <span class="badge badge-info badge-pill">
                                    {{ $perguliran->sis_jasa->nama_sistem }}
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
                                    {{ Tanggal::tglIndo($perguliran->tgl_verifikasi) }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Verifikasi
                                <span class="badge badge-danger badge-pill">
                                    {{ number_format($perguliran->verifikasi) }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Jenis Jasa
                                <span class="badge badge-danger badge-pill">
                                    {{ $perguliran->jasa->nama_jj }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Jasa
                                <span class="badge badge-danger badge-pill">
                                    {{ $perguliran->pros_jasa . '% / ' . $perguliran->jangka . ' bulan' }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Angs. Pokok
                                <span class="badge badge-danger badge-pill">
                                    {{ $perguliran->sis_pokok->nama_sistem }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                Angs. Jasa
                                <span class="badge badge-danger badge-pill">
                                    {{ $perguliran->sis_jasa->nama_sistem }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <hr class="horizontal dark">

            <div class="table-responsive">
                <table class="table table-striped align-items-center mb-0" width="100%">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Pengajuan</th>
                            <th>Verifikasi</th>
                            <th>Alokasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $proposal = 0;
                            $verifikasi = 0;
                        @endphp
                        @foreach ($perguliran->pinjaman_anggota as $pinjaman_anggota)
                            @php
                                $proposal += $pinjaman_anggota->proposal;
                                $verifikasi += $pinjaman_anggota->verifikasi;
                            @endphp
                            <tr>
                                <td align="center">{{ $loop->iteration }}</td>
                                <td>
                                    {{ ucwords($pinjaman_anggota->anggota->namadepan) }}
                                    ({{ $pinjaman_anggota->nia }})
                                </td>
                                <td>
                                    <div class="input-group input-group-static">
                                        <input type="text" disabled readonly class="form-control money idpa_proposal"
                                            value="{{ number_format($pinjaman_anggota->proposal, 2) }}">
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-static">
                                        <input type="text" id="{{ $pinjaman_anggota->id }}"
                                            name="idpa_proposal[{{ $pinjaman_anggota->id }}]"
                                            class="form-control money idpa_proposal"
                                            value="{{ number_format($pinjaman_anggota->verifikasi, 2) }}">
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-static">
                                        <input type="text" name="idpa[{{ $pinjaman_anggota->id }}]"
                                            class="form-control money idpa idpa-{{ $pinjaman_anggota->id }}"
                                            value="{{ number_format($pinjaman_anggota->verifikasi, 2) }}">
                                    </div>
                                    <input type="hidden" name="catatan[{{ $pinjaman_anggota->id }}]"
                                        value="{{ $perguliran->catatan_verifikasi }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">Jumlah</th>
                            <th>
                                {{ number_format($proposal, 2) }}
                            </th>
                            <th id="jumlah">
                                {{ number_format($verifikasi, 2) }}
                            </th>
                            <th>
                                <span id="_alokasi">{{ number_format($verifikasi, 2) }}</span>
                                <input type="hidden" name="__alokasi" id="__alokasi" value="{{ $verifikasi }}">
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="card card-body p-2 pb-0 mb-3">
        <div class="d-grid">
            <button type="button" data-bs-toggle="modal" data-bs-target="#CetakDokumenProposal"
                class="btn btn-info btn-sm mb-2">Cetak Dokumen Verifikasi</button>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header pb-0 p-3">
            <h6>
                Input Keputusan Pendanaan
            </h6>
        </div>
        <div class="card-body p-3">
            <input type="hidden" name="_id" id="_id" value="{{ $perguliran->id }}">
            <input type="hidden" name="status" id="status" value="W">
            <div class="row">
                <div class="col-md-3">
                    <div class="input-group input-group-static my-3">
                        <label for="tgl_tunggu">Tgl Tunggu</label>
                        <input autocomplete="off" type="text" name="tgl_tunggu" id="tgl_tunggu"
                            class="form-control date" value="{{ Tanggal::tglIndo($perguliran->tgl_verifikasi) }}">
                        <small class="text-danger" id="msg_tgl_tunggu"></small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-static my-3">
                        <label for="alokasi">Alokasi Rp.</label>
                        <input autocomplete="off" type="text" name="alokasi" id="alokasi"
                            class="form-control money" value="{{ number_format($perguliran->verifikasi, 2) }}">
                        <small class="text-danger" id="msg_alokasi"></small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-static my-3">
                        <label for="jangka">Jangka</label>
                        <input autocomplete="off" type="number" name="jangka" id="jangka" class="form-control"
                            value="{{ $perguliran->jangka }}">
                        <small class="text-danger" id="msg_jangka"></small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-static my-3">
                        <label for="pros_jasa">Prosentase Jasa (%)</label>
                        <input autocomplete="off" type="number" name="pros_jasa" id="pros_jasa"
                            class="form-control" value="{{ $perguliran->pros_jasa }}">
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
                                <option {{ $jj->id == $perguliran->jenis_jasa ? 'selected' : '' }}
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
                                <option {{ $sa->id == $perguliran->sistem_angsuran ? 'selected' : '' }}
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
                                <option {{ $sa->id == $perguliran->sa_jasa ? 'selected' : '' }}
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
                <?php 
                    $tanggalHariIni = date('d-m-Y'); // Format: 29-10-2024
                    ?>
                    <div class="input-group input-group-static my-3">
                        <label for="tgl_cair">Tanggal Cair</label>
                        <input autocomplete="off" type="text" name="tgl_cair" id="tgl_cair"
                            class="form-control date" value="{{ $tanggalHariIni }}">
                        <small class="text-danger" id="msg_tgl_cair"></small>
                    </div>

                </div>
                <div class="col-md-6">
                    <div class="input-group input-group-static my-3">
                        <label for="nomor_spk">Nomor SPK</label>
                        <input autocomplete="off" type="text" name="nomor_spk" id="nomor_spk"
                            class="form-control" value="{{ $perguliran->nomor_spk }}">
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

<form action="/perguliran/kembali_proposal/{{ $perguliran->id }}" method="post" id="formKembaliProposal">
    @csrf
</form>

<form action="/perguliran/tidak_layak/{{ $perguliran->id }}" method="post" id="formTidakLayak">
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

    $('.idpa_proposal').change(function(e) {

        var idpa = $(this).attr('id')
        var value = $(this).val()

        $.ajax({
            url: '/pinjaman_anggota/' + idpa,
            type: 'post',
            data: {
                '_method': 'PUT',
                'idpa': idpa,
                'verifikasi': value,
                'status': 'V',
                '_token': $('[name=_token]').val()
            },
            success: function(result) {
                var total = 0;
                $('.idpa_proposal').map(function() {
                    var idpa = $(this).attr('id')
                    var value = $(this).val()

                    $('.idpa-' + idpa).val(value)

                    value = value.split(',').join('')
                    value = value.split('.00').join('')
                    value = parseFloat(value)

                    total += value

                })

                $('#jumlah').html(result.jumlah)
                $('#alokasi').val(result.jumlah)
                $('#_alokasi').html(result.jumlah)
                $('#__alokasi').val(result.jumlah)
            }
        })
    })

    $(document).on('change', '.idpa', function(e) {
        var total = 0;
        $('.idpa').map(function() {
            var value = $(this).val()
            if (value == '') {
                value = 0
            } else {
                value = value.split(',').join('')
                value = value.split('.00').join('')
            }

            value = parseFloat(value)

            total += value
        })

        $('#__alokasi').val(total)
        $('#_alokasi').html(formatter.format(total))
        $('#alokasi').val(formatter.format(total))
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

        var alokasi = parseInt($('#alokasi').val().split(',').join('').split('.00').join(''))
        var __alokasi = parseInt($('#__alokasi').val())

        var lanjut = true;
        if (alokasi != __alokasi) {
            lanjut = await Swal.fire({
                title: 'Peringatan',
                text: 'Jumlah alokasi Anggota dan Kelompok Berbeda. Tetap lanjutkan?',
                showCancelButton: true,
                confirmButtonText: 'Lanjutkan',
                cancelButtonText: 'Batal',
                icon: 'warning'
            }).then((result) => {
                if (result.isConfirmed) {
                    return true;
                }

                return false
            })
        }

        if (lanjut) {
            var form = $('#FormInput')
            $.ajax({
                type: 'POST',
                url: form.attr('action') + '?save=true',
                data: form.serialize(),
                success: function(result) {
                    Swal.fire('Berhasil', result.msg, 'success').then(() => {
                        window.location.href = '/detail/' + result.id
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
        }
    })
</script>
