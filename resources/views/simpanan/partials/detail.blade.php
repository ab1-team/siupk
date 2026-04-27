@extends('layouts.base')
@section('content')
<div class="nav-wrapper position-relative end-0">
    <div class="tab-content mt-2">
        <div class="tab-pane fade show active" id="ProfilDebitur" role="tabpanel" aria-labelledby="ProfilDebitur">
            <div class="card">
                <div class="card mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card-body p-3">
                                <h5 class="mb-1">
                                @php
                                    $qq="";
                                    if($nia->lembaga != 0){
                                        $qq= "qq. ".$nia->lembaga;
                                    }
                                @endphp
                                    Nasabah {{ $nia->anggota->namadepan }} {{$qq}} CIF. {{$nia->id}}
                                    ({{ $nia->js->nama_js }})
                                </h5>
                                <p class="mb-0">
                                    <span class="badge badge-{{ $nia->sts->warna_status }}">{{ $nia->anggota->nia }}</span>
                                    <span class="badge badge-{{ $nia->sts->warna_status }}">{{ $nia->anggota->alamat_anggota }}</span>
                                    <span class="badge badge-{{ $nia->sts->warna_status }}">
                                        {{ $nia->anggota->d->sebutan_desa->sebutan_desa }}
                                        {{ $nia->anggota->d->nama_desa }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-body p-3">
                                <button class="btn btn-info btn-sm float-end ms-2" type="button" onclick="window.location.href='/simpanan'">
                                    <i class="fa fa-arrow-left"></i> Kembali
                                </button>
                                <button class="btn btn-warning btn-sm float-end ms-2" onclick="window.open('/cetak_kop/{{ $nia->id }}')" type="button">
                                    <i class="fa fa-print"></i> Cetak KOP Buku
                                </button>
                                <button  onclick="window.open('/form_simp/')" class="btn btn-primary btn-sm float-end ms-2"><i class="fas fa-file-alt"></i> Form Simpanan</button>
                                <button  onclick="generateSimpanan({{ $nia->id }})" class="btn btn-secondary btn-sm float-end ms-2"> <i class="fa fa-refresh"></i> Generate</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="input-group input-group-static my-3">
                                        <label for="nomor_rekening">Nomor Rekening</label>
                                        <input autocomplete="off" type="text" name="nomor_rekening" id="nomor_rekening" class="form-control" value="{{$nia->nomor_rekening}}" disabled>
                                        <small class="text-danger" id="msg_nomor_rekening"></small>
                                    </div>
                                    <div class="input-group input-group-static my-3">
                                        <label for="nama_debitur">Nama Debitur</label>
                                        <input autocomplete="off" type="text" name="nama_debitur" id="nama_debitur" class="form-control" value="{{$nia->anggota->namadepan}}" disabled>
                                        <small class="text-danger" id="msg_nama_debitur"></small>
                                    </div>
                                    <div class="input-group input-group-static my-3">
                                        <label for="nik">NIK</label>
                                        <input autocomplete="off" type="text" name="nik" id="nik" class="form-control" value="{{$nia->anggota->nik}}" disabled>
                                        <small class="text-danger" id="msg_nik"></small>
                                    </div>
                                    <div class="input-group input-group-static my-3">
                                        <label>TRANSAKSI SIMPANAN</label>
                                    </div>
                                    <div class="input-group input-group-static my-3">
                                        <label for="tgl_transaksi">Tgl Transaksi</label>
                                        <input autocomplete="off" type="text" name="tgl_transaksi" id="tgl_transaksi" class="form-control date" value="{{ date('d/m/Y') }}">
                                        <small class="text-danger" id="msg_tgl_transaksi"></small>
                                    </div>
                                    <div class="input-group input-group-static my-3">
                                        <label>Jenis Mutasi</label>
                                        <div class="row">
                                            <div class="col-xs-1">
                                                <div class="radio radio-success">
                                                    <input type="radio" name="jenis_mutasi" id="setor_tunai" value="1">
                                                    <label for="setor_tunai">Setor Tunai</label>
                                                </div>
                                            </div>
                                            <div class="col-xs-1">
                                                <div class="radio radio-danger">
                                                    <input type="radio" name="jenis_mutasi" id="tarik_tunai" value="2">
                                                    <label for="tarik_tunai">Tarik Tunai</label>
                                                </div>
                                            </div>
                                        </div>
                                        <small class="text-danger" id="msg_jenis_mutasi"></small>
                                    </div>
                                    <div class="input-group input-group-static my-3">
                                        <label for="jumlah">Jumlah (Rp.)</label>
                                        <input autocomplete="off" type="text" name="jumlah" id="jumlah" class="form-control" value="">
                                        <small class="text-danger" id="msg_jumlah"></small>
                                    </div>
                                    <button id="simpanTransaksi" class="btn btn-primary btn-sm float-end ms-2" type="button">
                                        Simpan Transaksi
                                    </button>
                                </div>





                                <div class="col-md-9">
                                    <div class="row mb-3">
                                        <div class="col-md-6">&nbsp;</div>
                                        <div class="col-md-3">
<!-- Dropdown Bulan -->
    <div style="position: relative;">
        <select id="bulants" name="bulants" class="form-control select2" 
                style="padding-left: 10px; padding-right: 30px; appearance: none; 
                       -webkit-appearance: none; -moz-appearance: none; border: 1px solid #ccc;">
                       
            <option value="0"> <!-- Cek jika bulan ini -->
                Semua Bulan
            </option>
            @foreach(range(1, 12) as $bulan)
            <option value="{{ $bulan }}" 
                    {{ $bulan == date('n') ? 'selected' : '' }}> <!-- Cek jika bulan ini -->
                {{ date('F', mktime(0, 0, 0, $bulan, 1)) }}
            </option>
            @endforeach
        </select>
        <span style="position: absolute; right: 10px; top: 50%; 
                     transform: translateY(-50%); pointer-events: none;">
            ▼
        </span>
    </div>
</div>
<!-- Dropdown Tahun -->
<div class="col-md-3">
    <div style="position: relative;">
        <select id="tahunts" name="tahunts" class="form-control select2" 
                style="padding-left: 10px; padding-right: 30px; appearance: none; 
                       -webkit-appearance: none; -moz-appearance: none; border: 1px solid #ccc;">
            <option value="0"> <!-- Cek jika bulan ini -->
                Semua Tahun
            </option>
            @foreach(range(date('Y') - 5, date('Y') + 5) as $tahun)
            <option value="{{ $tahun }}" 
                    {{ $tahun == date('Y') ? 'selected' : '' }}> <!-- Cek jika tahun ini -->
                {{ $tahun }}
            </option>
            @endforeach
        </select>
    </div>
</div>

                                    </div>
                                    <div class="card-body pb-2">
                                        <div id="transaksi-container">
                                            <!-- Tabel transaksi akan dimuat di sini -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
$(".date").flatpickr({
    dateFormat: "d/m/Y"
});

function tableTransaksi(bulan, tahun) {
    $.get('/simpanan/get-transaksi', {
        cif: '{{ $nia->id }}',
        bulan: bulan,
        tahun: tahun
    }, function(result) {
        $('#transaksi-container').html(result);
    }).fail(function(xhr, status, error) {
        console.error("Error loading transactions:", error);
        $('#transaksi-container').html('<p>Terjadi error pada Transaksi, silakan hubungi teknikal support untuk perbaikan data.</p>');
    });
}

$(document).ready(function() {
    var currentDate = new Date();
    var currentMonth = currentDate.getMonth() + 1; 
    var currentYear = currentDate.getFullYear();  
    tableTransaksi(currentMonth, currentYear);

    $('#bulants, #tahunts').change(function() {
        var bulan = $('#bulants').val();
        var tahun = $('#tahunts').val();
        tableTransaksi(bulan, tahun);
    });

    $('#simpanTransaksi').click(function() {
        var jenisMutasi = $('input[name="jenis_mutasi"]:checked').val();
        var tglTransaksi = $('#tgl_transaksi').val();
        var jumlah = $('#jumlah').val();
        var nomorRekening = $('#nomor_rekening').val();
        var namaDebitur = $('#nama_debitur').val();
        var nia = '{{ $nia->id }}';

        if (!jenisMutasi || !tglTransaksi || !jumlah) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Mohon lengkapi semua field.',
            });
            return;
        }

        Swal.fire({
            title: 'Mohon menunggu',
            text: 'Sedang memproses transaksi...',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            },
        });

        $.ajax({
            url: '/simpanan/simpan-transaksi',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                jenis_mutasi: jenisMutasi,
                tgl_transaksi: tglTransaksi,
                jumlah: jumlah,
                nomor_rekening: nomorRekening,
                nama_debitur: namaDebitur,
                nia: nia
            },
            success: function(response) {
                if (response.success) {
                    Swal.close();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Transaksi ' + (jenisMutasi == '1' ? 'setor' : 'tarik') + ' berhasil disimpan',
                        confirmButtonText: 'Oke'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            tableTransaksi(currentMonth, currentYear);
                            resetForm();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal menyimpan transaksi: ' + response.message,
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan: ' + error,
                });
            }
        });
    });


    function resetForm() {
        $('input[name="jenis_mutasi"]').prop('checked', false);
        $('#tgl_transaksi').val('');
        $('#jumlah').val('');
    }
});

function generateSimpanan(cif) {
    Swal.fire({
        title: 'Generate Real Simpanan?',
        text: 'Proses ini akan menghapus dan membangun ulang data real simpanan untuk CIF ' + cif + '. Lanjutkan?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Generate!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Mohon menunggu...',
                text: 'Sedang memproses generate real simpanan...',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: '/simpanan/generate/' + cif,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message || 'Generate real simpanan berhasil.',
                            confirmButtonText: 'Oke'
                        }).then(() => {
                            var bulan = $('#bulants').val();
                            var tahun = $('#tahunts').val();
                            tableTransaksi(bulan, tahun);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message || 'Generate gagal.'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan: ' + (xhr.responseJSON?.message || xhr.statusText)
                    });
                }
            });
        }
    });
}
</script>
@endsection
