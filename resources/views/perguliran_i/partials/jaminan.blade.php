<div class="jaminan-partial-wrapper">
    <div class="row">
        <div class="col-md-12">
            <div class="input-group input-group-static my-3">
                <label for="jenis_jaminan">Jenis Jaminan</label>
                <select class="form-control" name="jenis_jaminan" id="jenis_jaminan" style="border: 1px solid #d4d4d4; border-radius: 6px;">
                    <option value="1" {{ isset($jenis_jaminan) && $jenis_jaminan == '1' ? 'selected' : '' }}>Surat Tanah</option>
                    <option value="2" {{ isset($jenis_jaminan) && $jenis_jaminan == '2' ? 'selected' : '' }}>BPKB</option>
                    <option value="3" {{ isset($jenis_jaminan) && $jenis_jaminan == '3' ? 'selected' : '' }}>SK. Pegawai</option>
                    <option value="4" {{ isset($jenis_jaminan) && $jenis_jaminan == '4' ? 'selected' : '' }}>Lain Lain</option>
                </select>
                <small class="text-danger" id="msg_jenis_jaminan"></small>
            </div>
        </div>
    </div>

    <div id="formJaminanWrapper" class="mt-2">
        @include('perguliran_i.partials.jaminan_fields', ['id' => $id, 'jaminan' => $jaminan ?? []])
    </div>
</div>

<script>
    (function() {
        var wrapper = document.querySelector('.jaminan-partial-wrapper').parentNode;

        function initMaskMoneyJaminan() {
            if (typeof $ !== 'undefined' && typeof $.fn.maskMoney === 'function') {
                wrapper.querySelectorAll('#nilai_jual_tanah, #nilai_jual_kendaraan, #nilai_jaminan').forEach(function(el) {
                    if (typeof $(el).data('maskMoney') === 'undefined') {
                        $(el).maskMoney();
                    }
                });
            }
        }

        initMaskMoneyJaminan();

        var select = wrapper.querySelector('#jenis_jaminan');
        if (select && !select.dataset.bound) {
            select.dataset.bound = '1';
            select.addEventListener('change', function() {
                var id = this.value;
                if (!id) return;
                var idPinjI = '';
                var idInput = wrapper.parentNode.querySelector('input[name="_id_jaminan"]')
                    || wrapper.querySelector('input[name="_id_jaminan"]');
                if (idInput) idPinjI = idInput.value;
                var url = '/register_proposal_i/jaminan/' + id + (idPinjI ? '?id_pinj_i=' + idPinjI : '') + '&_t=' + Date.now();
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Cache-Control': 'no-cache' } })
                    .then(function(r) { return r.json(); })
                    .then(function(result) {
                        if (result && result.success) {
                            var wrap = wrapper.querySelector('#formJaminanWrapper');
                            if (wrap) {
                                wrap.innerHTML = result.view;
                                initMaskMoneyJaminan();
                            }
                        }
                    })
                    .catch(function(err) { console.error('Jaminan fetch error:', err); });
            });
        }
    })();
</script>
