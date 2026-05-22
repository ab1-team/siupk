@extends('layouts.base')

@section('title', 'Form Pengawas')

@section('content')
<div class="container-fluid py-4">

    {{-- Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">

        {{-- ===== FORM ===== --}}
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header pb-0">
                    <h6 class="mb-0">
                        @isset($catatan)
                            <i class="fas fa-edit me-1"></i> Edit Catatan
                        @else
                            <i class="fas fa-plus-circle me-1"></i> Tambah Catatan
                        @endisset
                    </h6>
                </div>
                <div class="card-body">

                    @isset($catatan)
                        <form action="{{ route('form_pengawas.update', $catatan->id) }}" method="POST">
                            @method('PUT')
                    @else
                        <form action="{{ route('form_pengawas.store') }}" method="POST">
                    @endisset

                        @csrf

                        {{-- Bulan Laporan --}}
                        <div class="mb-3">
                            <label class="form-label text-sm fw-bold">Bulan Laporan</label>
                            <input type="month"
                                   name="bulan_laporan"
                                   class="form-control form-control-sm @error('bulan_laporan') is-invalid @enderror"
                                   value="{{ isset($catatan) ? $catatan->bulan_laporan : old('bulan_laporan', now()->format('Y-m')) }}">
                            @error('bulan_laporan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Isi Catatan --}}
                        <div class="mb-3">
                            <label class="form-label text-sm fw-bold">Isi Catatan / Temuan</label>
                            <textarea name="isi"
                                      rows="6"
                                      class="form-control form-control-sm @error('isi') is-invalid @enderror"
                                      placeholder="Tuliskan catatan atau temuan pengawas...">{{ isset($catatan) ? $catatan->isi : old('isi') }}</textarea>
                            @error('isi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-save me-1"></i>
                                @isset($catatan) Perbarui @else Simpan @endisset
                            </button>
                            @isset($catatan)
                                <a href="{{ route('form_pengawas.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endisset
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{-- ===== TABEL DATA ===== --}}
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h6 class="mb-0"><i class="fas fa-list me-1"></i> Daftar Catatan Pengawas</h6>
{{-- Cetak Bulanan --}}
@if(!empty($filterBulan))
<form action="{{ url('pelaporan/preview') }}" method="POST" target="_blank" class="d-inline">
    @csrf
    <input type="hidden" name="laporan" value="catatan_pengawas">
    <input type="hidden" name="type"    value="pdf">
    <input type="hidden" name="tahun"   value="{{ $filterTahun }}">
    <input type="hidden" name="bulan"   value="{{ str_pad($filterBulan, 2, '0', STR_PAD_LEFT) }}">
    <input type="hidden" name="hari"    value="">
    <button type="submit" class="btn btn-sm btn-outline-danger">
        <i class="fas fa-file-pdf me-1"></i> Cetak Bulanan
    </button>
</form>
@endif

{{-- Cetak Tahunan --}}
<form action="{{ url('pelaporan/preview') }}" method="POST" target="_blank" class="d-inline">
    @csrf
    <input type="hidden" name="laporan" value="catatan_pengawas">
    <input type="hidden" name="type"    value="pdf">
    <input type="hidden" name="tahun"   value="{{ $filterTahun }}">
    <input type="hidden" name="bulan"   value="">
    <input type="hidden" name="hari"    value="">
    <button type="submit" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-file-pdf me-1"></i> Cetak Tahunan
    </button>
</form>
                    </div>

                    {{-- Filter --}}
                    <div class="d-flex gap-2 mt-2 flex-wrap">

                        {{-- Dropdown Bulan --}}
                        <select id="filterBulan" class="form-select form-select-sm" style="width: auto; min-width: 150px;">
                            <option value="">Semua Bulan</option>
                            @php
                                $namaBulan = [
                                    1=>'January', 2=>'February', 3=>'March', 4=>'April',
                                    5=>'May', 6=>'June', 7=>'July', 8=>'August',
                                    9=>'September', 10=>'October', 11=>'November', 12=>'December'
                                ];
                            @endphp
                            @foreach($namaBulan as $num => $nama)
                                <option value="{{ $num }}" {{ (string)$filterBulan === (string)$num ? 'selected' : '' }}>
                                    {{ $nama }}
                                </option>
                            @endforeach
                        </select>

                        {{-- Dropdown Tahun 2020-2030 --}}
                        <select id="filterTahun" class="form-select form-select-sm" style="width: auto; min-width: 120px;">
                            <option value="">Semua Tahun</option>
                            @for($thn = 2030; $thn >= 2020; $thn--)
                                <option value="{{ $thn }}" {{ (string)$filterTahun === (string)$thn ? 'selected' : '' }}>
                                    {{ $thn }}
                                </option>
                            @endfor
                        </select>

                    </div>
                </div>

                <div class="card-body px-0 pb-2">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3" style="width: 40px">#</th>
                                    <th>Bulan Laporan</th>
                                    <th>Isi Catatan</th>
                                    <th class="text-center" style="width: 100px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $i => $row)
                                    <tr>
                                        <td class="ps-3">{{ $data->firstItem() + $i }}</td>
                                        <td>
                                            <span class="badge bg-gradient-info">
                                                {{ \Carbon\Carbon::createFromFormat('Y-m', $row->bulan_laporan)->translatedFormat('F Y') }}
                                            </span>
                                        </td>
                                        <td class="text-sm text-start" style="max-width: 400px;">
                                            {{ Str::limit($row->isi, 100) }}
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('form_pengawas.edit', $row->id) }}"
                                               class="btn btn-xs btn-warning me-1" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('form_pengawas.destroy', $row->id) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Hapus catatan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            Belum ada catatan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($data->hasPages())
                        <div class="d-flex justify-content-end px-3 pt-2">
                            {{ $data->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function applyFilter() {
        const bulan = document.getElementById('filterBulan').value;
        const tahun = document.getElementById('filterTahun').value;
        const url   = new URL(window.location.href);
        url.searchParams.set('bulan', bulan);
        url.searchParams.set('tahun', tahun);
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    document.getElementById('filterBulan').addEventListener('change', applyFilter);
    document.getElementById('filterTahun').addEventListener('change', applyFilter);
});
</script>
@endsection
