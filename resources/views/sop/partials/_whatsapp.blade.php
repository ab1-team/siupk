@php
    $pesan_wa = is_array($kec->whatsapp) ? $kec->whatsapp : (json_decode($kec->whatsapp, true) ?: []);
@endphp

<form action="/pengaturan/pesan_whatsapp/{{ $kec->id }}" method="post" id="FormScanWhatsapp">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="tagihan" class="form-label">Pesan Tagihan</label>
            <textarea class="form-control" name="tagihan" id="tagihan" rows="12">{!! $pesan_wa['tagihan'] ?? '' !!}</textarea>
        </div>
        <div class="col-md-6 mb-3">
            <label for="angsuran" class="form-label">Pesan Angsuran</label>
            <textarea class="form-control" name="angsuran" id="angsuran" rows="12">{!! $pesan_wa['angsuran'] ?? '' !!}</textarea>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
            <button type="button" id="ScanWA" class="btn btn-sm btn-info mb-0">
                <i class="material-icons text-sm me-1">qr_code_scanner</i>
                Scan WA Gateway
            </button>
            <button type="button" id="HapusWa" class="btn btn-sm btn-danger mb-0">
                <i class="material-icons text-sm me-1">link_off</i>
                Putuskan Koneksi
            </button>
        </div>

        <button type="button" id="SimpanWhatsapp" data-target="#FormScanWhatsapp"
            class="btn btn-sm btn-github mb-0 btn-simpan">
            Simpan Perubahan
        </button>
    </div>
</form>
