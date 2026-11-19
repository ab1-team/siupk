@extends('layouts.base')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <style>
        .select2-container--bootstrap-5 .select2-selection {
            min-height: 40px;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            @if (session('status'))
                <div class="alert alert-success text-white">{{ session('status') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger text-white">{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger text-white">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="mb-0">Logo Kabupaten</h5>
                    <p class="text-sm text-muted mb-0">
                        Pilih kabupaten, lalu unggah logo. Logo dipakai pada kop/cover dokumen perguliran
                        (proposal, pencairan, rekomendasi kredit, dan lainnya) untuk kabupaten tersebut.
                    </p>
                </div>
                <div class="card-body">
                    <form id="formLogo" action="{{ route('logoKab.update') }}" method="post"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label" for="kabupaten_id">Kabupaten</label>
                            <select name="kabupaten_id" id="kabupaten_id"
                                class="form-select @error('kabupaten_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Kabupaten --</option>
                                @foreach ($kabupaten as $kab)
                                    <option value="{{ $kab->id }}"
                                        {{ (string) $selectedId === (string) $kab->id ? 'selected' : '' }}>
                                        {{ $kab->nama_kab }} ({{ $kab->kd_kab }})
                                    </option>
                                @endforeach
                            </select>
                            @error('kabupaten_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-5">
                                <div class="border border-radius-lg p-3 text-center">
                                    <img id="previewLogo" src="" alt="Logo Kabupaten" class="img-fluid"
                                        style="max-height: 180px; object-fit: contain; display: none;">

                                    <div id="emptyLogo" class="text-muted py-5">
                                        <i class="material-icons opacity-4" style="font-size: 42px;">image</i>
                                        <div class="text-sm">Belum ada logo</div>
                                    </div>

                                    <div class="text-sm mt-3">
                                        Nama file: <code id="nama_file">-</code>
                                    </div>
                                    <div class="text-xs text-muted">
                                        ID Kabupaten <span id="id_kab">-</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-7">
                                <div class="mb-3">
                                    <label class="form-label" for="file">File Gambar</label>
                                    <input type="file" name="file" id="file"
                                        class="form-control @error('file') is-invalid @enderror"
                                        accept="image/*" required>
                                    @error('file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Format JPG, PNG, GIF, WEBP, atau BMP (maks 4 MB). File otomatis
                                        dikonversi menjadi PNG dengan nama <code>&lt;id_kabupaten&gt;.png</code>
                                        dan diskalakan maksimal 1200 px.
                                    </div>
                                </div>

                                <button type="submit" id="btnSimpan" class="btn btn-primary mb-0">
                                    Unggah Logo
                                </button>
                            </div>
                        </div>
                    </form>

                    <form id="deleteForm" action="{{ route('logoKab.destroy') }}" method="post"
                        class="mt-2 d-none"
                        onsubmit="return confirm('Yakin hapus logo ' + document.getElementById('nama_file').textContent + '?')">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="kabupaten_id" id="deleteKabupatenId">
                        <button type="submit" class="btn btn-outline-danger btn-sm mb-0">
                            Hapus Logo
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Cara Pakai</h6>
                </div>
                <div class="card-body">
                    <ol class="mb-0 ps-3 text-sm">
                        <li class="mb-2">Pilih kabupaten pada dropdown.</li>
                        <li class="mb-2">Pilih file gambar logo kabupaten.</li>
                        <li class="mb-2">Klik <strong>Unggah Logo</strong> / <strong>Ganti Logo</strong>.</li>
                        <li>Logo tersimpan sebagai <code>&lt;id_kabupaten&gt;.png</code> dan langsung dipakai pada
                            dokumen proposal &amp; pencairan perguliran.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        (function() {
            var logoFiles = @json((object) $petaLogo);
            var storageUrl = @json(asset('storage/logo_kab'));

            var $select = $('#kabupaten_id');
            var $form = $('#formLogo');
            var $img = $('#previewLogo');
            var $empty = $('#emptyLogo');
            var $deleteForm = $('#deleteForm');
            var $deleteId = $('#deleteKabupatenId');
            var $btnSimpan = $('#btnSimpan');

            function render(id) {
                id = id ? String(id) : '';

                var nama = id ? id + '.png' : '-';
                var versi = id ? logoFiles[id] : undefined;
                var adaLogo = id !== '' && versi !== undefined;

                $('#nama_file').text(nama);
                $('#id_kab').text(id || '-');
                $deleteId.val(id);

                if (adaLogo) {
                    $img.attr('src', storageUrl + '/' + nama + '?v=' + versi).show();
                    $empty.hide();
                    $deleteForm.removeClass('d-none');
                    $btnSimpan.text('Ganti Logo');
                } else {
                    $img.attr('src', '').hide();
                    $empty.show();
                    $deleteForm.addClass('d-none');
                    $btnSimpan.text('Unggah Logo');
                }
            }

            $select.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: '-- Pilih Kabupaten --',
                allowClear: true
            });

            $select.on('change', function() {
                render($(this).val());
            });

            $form.on('submit', function(e) {
                if (!$select.val()) {
                    e.preventDefault();
                    alert('Pilih kabupaten terlebih dahulu.');
                }
            });

            render($select.val());
        })();
    </script>
@endsection
