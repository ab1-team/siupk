@php
    use App\Utils\Tanggal;
@endphp

<form action="" id="FormPemberitahuan">
    <div class="table-responsive">
        <table class="table table-striped midle" width="100%">
            <thead>
                <tr>
                    <td align="center" width="5%">
                        <div class="form-check text-center">
                            <input class="form-check-input" type="checkbox" value="true" id="checked" name="checked">
                        </div>
                    </td>
                    <td align="center">Nama Kelompok</td>
                    <td align="center">Tgl Cair</td>
                    <td align="center">Alokasi</td>
                    <td align="center">Tagihan Pokok</td>
                    <td align="center">Tagihan Jasa</td>
                </tr>
            </thead>
            <tbody>
                @foreach ($pinjaman as $pinj)
                    @if ($pinj->target)
                        @php
                            $nomor = $pinj->kelompok->telpon;
                            $desa = $pinj->kelompok->d->sebutan_desa->sebutan_desa ?? 'Desa';
                            $desa .= ' ' . $pinj->kelompok->d->nama_desa;

                            $value = $template ?? '';
                            $value = strtr($value, [
                                '{Nama Kelompok}' => $pinj->kelompok->nama_kelompok,
                                '{Nama Nasabah}' => $pinj->kelompok->nama_kelompok,
                                '{Nama Desa}' => $desa,
                                '{Angsuran Pokok}' => number_format($pinj->target->wajib_pokok),
                                '{Angsuran Jasa}' => number_format($pinj->target->wajib_jasa),
                                '{Tanggal Angsuran}' => $bulan_angsuran ?? '',
                                '{Tanggal Jatuh Tempo}' => $tgl_tagihan ?? '',
                                '{Tanggal Bayar}' => $tgl_bayar ?? '',
                                '{User Login}' => $user_nama ?? '',
                                '{Telpon}' => $user_hp ?? '',
                            ]);

                            $tagihan_pokok = $pinj->target->wajib_pokok;
                            $tagihan_jasa = $pinj->target->wajib_jasa;
                            if ($pinj->saldo) {
                                $tagihan_pokok = $pinj->saldo->tunggakan_pokok;
                                $tagihan_jasa = $pinj->saldo->tunggakan_jasa;
                            }

                            $cleanNomor = preg_replace('/[^0-9]/', '', $nomor);
                            $isValid = strlen($cleanNomor) >= 11 && (str_starts_with($cleanNomor, '08') || str_starts_with($cleanNomor, '628'));
                        @endphp
                        <tr>
                            <td>
                                <div class="form-check text-center">
                                    <input class="form-check-input wa-row" type="checkbox"
                                        data-row='@json([
                                            "id_pinkel" => $pinj->id,
                                            "nama_kelompok" => $pinj->kelompok->nama_kelompok,
                                            "desa" => $desa,
                                            "number" => $nomor,
                                        ])'
                                        id="{{ $pinj->id }}" name="pinjaman[]"
                                        {!! $isValid ? '' : 'disabled' !!}>
                                </div>
                            </td>
                            <td>{{ $pinj->kelompok->nama_kelompok }} - {{ $pinj->id }}</td>
                            <td align="center">{{ Tanggal::tglIndo($pinj->tgl_cair) }}</td>
                            <td align="right">{{ number_format($pinj->alokasi, 2) }}</td>
                            <td align="right">{{ number_format($tagihan_pokok, 2) }}</td>
                            <td align="right">{{ number_format($tagihan_jasa, 2) }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</form>

<script>
    $(document).on('click', '#checked', function() {
        if ($(this)[0].checked == true) {
            $('.wa-row:not(:disabled)').prop('checked', true)
        } else {
            $('.wa-row').prop('checked', false)
        }
    })
</script>
