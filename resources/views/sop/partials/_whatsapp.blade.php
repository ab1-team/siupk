@php
    $pesan_wa = json_decode($kec->whatsapp, true);
@endphp

<form action="/pengaturan/pesan_whatsapp/{{ $kec->id }}" method="post" id="FormScanWhatsapp">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-md-6">
            <div class="input-group input-group-static mb-3">
                <label for="tagihan">Pesan Tagihan</label>
                <textarea class="form-control" name="tagihan" id="tagihan" cols="20" rows="10">{!! $pesan_wa['tagihan'] !!}</textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="input-group input-group-static mb-3">
                <label for="angsuran">Pesan Angsuran</label>
                <textarea class="form-control" name="angsuran" id="angsuran" cols="20" rows="10">{!! $pesan_wa['angsuran'] !!}</textarea>
            </div>
        </div>
    </div>
</form>

<div class="d-flex justify-content-end align-items-center">
    <div class="dropdown me-2">
        <button class="btn btn-sm btn-info mb-0 dropdown-toggle" type="button" id="DropdownScanWA"
            data-bs-toggle="dropdown" aria-expanded="false">
            <i class="material-icons text-sm me-1">qr_code_scanner</i>
            Scan WA Gateway
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="DropdownScanWA">
            <li id="ScanWALi" class="d-none">
                <button type="button" id="ScanWA" class="dropdown-item">
                    <i class="material-icons text-sm me-1">qr_code_2</i>
                    Scan QR Baru
                </button>
            </li>
            <li id="HapusWaLi" class="d-none">
                <button type="button" id="HapusWa" class="dropdown-item text-danger">
                    <i class="material-icons text-sm me-1">link_off</i>
                    Hapus Koneksi WA
                </button>
            </li>
        </ul>
    </div>

    <button type="button" id="SimpanWhatsapp" data-target="#FormScanWhatsapp"
        class="btn btn-sm btn-github mb-0 btn-simpan">
        Simpan Perubahan
    </button>
</div>
