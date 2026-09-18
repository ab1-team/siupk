@if ($file == 3)
    <div class="my-2">
        <label class="form-label" for="sub_laporan">Nama Sub Laporan</label>
        <select class="form-control" name="sub_laporan" id="sub_laporan">
            @foreach ($rekening as $rek)
                <option value="BB_{{ $rek->kode_akun }}">{{ $rek->kode_akun }}. {{ $rek->nama_akun }}</option>
            @endforeach
        </select>
        <small class="text-danger" id="msg_sub_laporan"></small>
    </div>

@elseif ($file == 20)
    <div class="my-2">
        <label class="form-label" for="sub_laporan">Nama Sub Laporan</label>
        <select class="sublaporanselect2 form-control" name="sub_laporan" id="sub_laporan">
        @foreach ($ojk as $index => $lp)
            <option value="{{ $lp->file }}">{{ $index + 1 }}. {{ $lp->nama_laporan }}</option>
        @endforeach

        </select>
        <small class="text-danger" id="msg_sub_laporan"></small>
    </div>
@elseif ($file == 'calk')
    <div class="my-3">
        <div id="editor">
            <ol>
                {!! $keterangan !!}
            </ol>
        </div>
    </div>

    <textarea name="sub_laporan" id="sub_laporan" class="d-none"></textarea>

    <script>
        quill = new Quill('#editor', {
            theme: 'snow'
        });
    </script>
@elseif ($file == 'calk_v2')
    <input type="hidden" name="sub_laporan" id="sub_laporan" value="calk_v2">
@elseif ($file == 5)
    <div class="my-2">
        <label class="form-label" for="sub_laporan">Nama Sub Laporan</label>
        <select class="form-control" name="sub_laporan" id="sub_laporan">
            @foreach ($jenis_laporan as $jl)
                <option value="{{ $jl->file }}">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}.
                    {{ $jl->nama_laporan }}
                </option>
            @endforeach
        </select>
        <small class="text-danger" id="msg_sub_laporan"></small>
    </div>
@elseif ($file == 14)
    <div class="my-2">
        <label class="form-label" for="sub_laporan">Nama Sub Laporan</label>
        <select class="form-control" name="sub_laporan" id="sub_laporan">
            @foreach ($data as $dt)
                <option value="EB_{{ $dt['id'] }}">
                    {{ $dt['title'] }}
                </option>
            @endforeach
        </select>
        <small class="text-danger" id="msg_sub_laporan"></small>
    </div>
@elseif ($file == 'tutup_buku')
    <div class="my-2">
        <label class="form-label" for="sub_laporan">Nama Sub Laporan</label>
        <select class="form-control" name="sub_laporan" id="sub_laporan">
            @foreach ($data as $dt)
                <option value="{{ $dt['file'] }}">
                    {{ $dt['title'] }}
                </option>
            @endforeach
        </select>
        <small class="text-danger" id="msg_sub_laporan"></small>
    </div>
@else
    <div class="my-2">
        <label class="form-label" for="sub_laporan">Nama Sub Laporan</label>
        <select class="form-control" name="sub_laporan" id="sub_laporan">
            <option value="">---</option>
        </select>
        <small class="text-danger" id="msg_sub_laporan"></small>
    </div>
@endif


<script>
    new Choices($('select#sub_laporan')[0])
</script>
