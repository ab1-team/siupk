@php
    $pesan_wa = $kec->whatsapp ?: [];
    $waDevice = $kec->wa_session;
    $deviceId = $waDevice->device_id ?? null;
    $deviceKey = $waDevice->device_key ?? null;
    $waStatus = $waDevice->status ?? null;
    $isConnected = $waDevice && $waDevice->isConnected();
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

    <div class="alert alert-light text-xs mb-3">
        <b>Placeholder tersedia:</b>
        <code>{Nama Kelompok}</code> &middot;
        <code>{Nama Nasabah}</code> &middot;
        <code>{Nama Desa}</code> &middot;
        <code>{Angsuran Pokok}</code> &middot;
        <code>{Angsuran Jasa}</code> &middot;
        <code>{Tanggal Angsuran}</code> &middot;
        <code>{Tanggal Jatuh Tempo}</code> &middot;
        <code>{Tanggal Bayar}</code> &middot;
        <code>{User Login}</code> &middot;
        <code>{Telpon}</code>
    </div>
</form>

<div class="d-flex justify-content-between align-items-center">
    <div>
        @if ($isConnected)
            <span class="badge bg-success">WhatsApp Terhubung</span>
            @if ($waDevice->phone_number)
                <small class="text-muted ms-2">{{ $waDevice->phone_number }}</small>
            @endif
        @else
            <span class="badge bg-secondary">WhatsApp Belum Terhubung</span>
        @endif
    </div>

    <div class="d-flex">
        <div class="me-2">
            <button type="button" id="ScanWA" class="btn btn-sm btn-info mb-0 {{ $isConnected ? 'd-none' : '' }}">
                <i class="material-icons text-sm me-1">qr_code_scanner</i>
                Scan WA Gateway
            </button>
            <button type="button" id="HapusWa" class="btn btn-sm btn-danger mb-0 {{ !$isConnected ? 'd-none' : '' }}">
                <i class="material-icons text-sm me-1">link_off</i>
                Putuskan Koneksi
            </button>
        </div>

        <button type="button" id="SimpanWhatsapp" data-target="#FormScanWhatsapp"
            class="btn btn-sm btn-github mb-0 btn-simpan">
            Simpan Perubahan
        </button>
    </div>
</div>

<input type="hidden" id="waDeviceId" value="{{ $deviceId }}">
<input type="hidden" id="waDeviceKey" value="{{ $deviceKey }}">
<input type="hidden" id="waApiUrl" value="{{ $api }}">
