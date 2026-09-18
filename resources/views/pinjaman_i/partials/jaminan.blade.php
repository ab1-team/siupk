@if ($id == '1')
    <div class="col-md-6">
        <div class="input-group input-group-static my-3">
            <label for="nomor_sertifikat">Nomor Sertifikat</label>
            <input autocomplete="off" type="text" name="data_jaminan[nomor_sertifikat]" id="nomor_sertifikat"
                class="form-control form-control-border">
            <small class="text-danger" id="msg_nomor_sertifikat"></small>
        </div>
    </div>
    <div class="col-md-6">
        <div class="input-group input-group-static my-3">
            <label for="nama_pemilik">Nama Pemilik</label>
            <input autocomplete="off" type="text" name="data_jaminan[nama_pemilik]" id="nama_pemilik"
                class="form-control form-control-border">
            <small class="text-danger" id="msg_nama_pemilik"></small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="input-group input-group-static my-3">
            <label for="alamat">Alamat</label>
            <input autocomplete="off" type="text" name="data_jaminan[alamat]" id="alamat" class="form-control form-control-border">
            <small class="text-danger" id="msg_alamat"></small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="input-group input-group-static my-3">
            <label for="luas">Luas (m²)</label>
            <input autocomplete="off" type="text" name="data_jaminan[luas]" id="luas" class="form-control form-control-border">
            <small class="text-danger" id="msg_luas"></small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="input-group input-group-static my-3">
            <label for="nilai_jual_tanah">Nilai Jual Tanah</label>
            <input autocomplete="off" type="text" name="data_jaminan[nilai_jual_tanah]" id="nilai_jual_tanah"
                class="form-control form-control-border">
            <small class="text-danger" id="msg_nilai_jual_tanah"></small>
        </div>
    </div>

    <script>
        (function() {
            if (typeof $ !== 'undefined' && typeof $.fn.maskMoney === 'function') {
                var el = document.getElementById('nilai_jual_tanah');
                if (el && typeof $(el).data('maskMoney') === 'undefined') $(el).maskMoney();
            }
        })();
    </script>
@elseif ($id == '2')
    <div class="col-md-6">
        <div class="input-group input-group-static my-3">
            <label for="nomor">Nomor</label>
            <input autocomplete="off" type="text" name="data_jaminan[nomor]" id="nomor" class="form-control form-control-border">
            <small class="text-danger" id="msg_nomor"></small>
        </div>
    </div>
    <div class="col-md-6">
        <div class="input-group input-group-static my-3">
            <label for="nama_pemilik">Nama Pemilik</label>
            <input autocomplete="off" type="text" name="data_jaminan[nama_pemilik]" id="nama_pemilik"
                class="form-control form-control-border">
            <small class="text-danger" id="msg_nama_pemilik"></small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="input-group input-group-static my-3">
            <label for="jenis_kendaraan">Jenis Kendaraan</label>
            <input autocomplete="off" type="text" name="data_jaminan[jenis_kendaraan]" id="jenis_kendaraan"
                class="form-control form-control-border">
            <small class="text-danger" id="msg_jenis_kendaraan"></small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="input-group input-group-static my-3">
            <label for="nopol">Nopol</label>
            <input autocomplete="off" type="text" name="data_jaminan[nopol]" id="nopol" class="form-control form-control-border">
            <small class="text-danger" id="msg_nopol"></small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="input-group input-group-static my-3">
            <label for="nilai_jual_kendaraan">Nilai Jual Kendaraan</label>
            <input autocomplete="off" type="text" name="data_jaminan[nilai_jual_kendaraan]" id="nilai_jual_kendaraan"
                class="form-control form-control-border">
            <small class="text-danger" id="msg_nilai_jual_kendaraan"></small>
        </div>
    </div>

    <script>
        (function() {
            if (typeof $ !== 'undefined' && typeof $.fn.maskMoney === 'function') {
                var el = document.getElementById('nilai_jual_kendaraan');
                if (el && typeof $(el).data('maskMoney') === 'undefined') $(el).maskMoney();
            }
        })();
    </script>
@elseif ($id == '3')
    <div class="col-md-4">
        <div class="input-group input-group-static my-3">
            <label for="nomor">Nomor</label>
            <input autocomplete="off" type="text" name="data_jaminan[nomor]" id="nomor"
                class="form-control form-control-border">
            <small class="text-danger" id="msg_nomor"></small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="input-group input-group-static my-3">
            <label for="nama_pegawai">Nama Pegawai</label>
            <input autocomplete="off" type="text" name="data_jaminan[nama_pegawai]" id="nama_pegawai"
                class="form-control form-control-border">
            <small class="text-danger" id="msg_nama_pegawai"></small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="input-group input-group-static my-3">
            <label for="nama_kuitansi_penerbit">Nama Instansi Penerbit</label>
            <input autocomplete="off" type="text" name="data_jaminan[nama_kuitansi_penerbit]"
                id="nama_kuitansi_penerbit" class="form-control form-control-border">
            <small class="text-danger" id="msg_nama_kuitansi_penerbit"></small>
        </div>
    </div>
@else
    <div class="col-md-4">
        <div class="input-group input-group-static my-3">
            <label for="nama_jaminan">Nama Jaminan</label>
            <input autocomplete="off" type="text" name="data_jaminan[nama_jaminan]" id="nama_jaminan"
                class="form-control form-control-border">
            <small class="text-danger" id="msg_nama_jaminan"></small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="input-group input-group-static my-3">
            <label for="keterangan">Keterangan</label>
            <input autocomplete="off" type="text" name="data_jaminan[keterangan]" id="keterangan"
                class="form-control form-control-border">
            <small class="text-danger" id="msg_keterangan"></small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="input-group input-group-static my-3">
            <label for="nilai_jaminan">Nilai Jaminan</label>
            <input autocomplete="off" type="text" name="data_jaminan[nilai_jaminan]" id="nilai_jaminan"
                class="form-control form-control-border">
            <small class="text-danger" id="msg_nilai_jaminan"></small>
        </div>
    </div>

    <script>
        (function() {
            if (typeof $ !== 'undefined' && typeof $.fn.maskMoney === 'function') {
                var el = document.getElementById('nilai_jaminan');
                if (el && typeof $(el).data('maskMoney') === 'undefined') $(el).maskMoney();
            }
        })();
    </script>
@endif
