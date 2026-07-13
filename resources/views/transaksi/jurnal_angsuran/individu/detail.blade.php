@php
    use App\Utils\Tanggal;
    $t_pokok = 0;
    $t_jasa = 0;
    $t_denda = 0;
@endphp

<table border="1" class="table table-striped midle">
    <thead class="bg-dark text-white">
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Keterangan</th>
            <th>Kode Kuitansi</th>
            <th>Pencairan</th>
            <th>Pokok</th>
            <th>Jasa</th>
            <th>Denda</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="4" align="center" style="text-transform: uppercase;">
                <b>Target Pembayaran</b>
            </td>
            <td align="right"><b>{{ number_format($nia->alokasi) }}</b></td>
            <td align="right"><b>{{ number_format($nia->alokasi) }}</b></td>
            <td align="right"><b>{{ number_format(($nia->alokasi * $nia->pros_jasa) / 100) }}</b></td>
            <td align="right"><b>0</b></td>
            <td align="right">&nbsp;</td>
        </tr>
        @foreach ($nia->real_i as $real_i)
            @php
                $keterangan = '';
                $denda = 0;
                $idt = 0;
            @endphp
            @foreach ($real_i->trx as $trx)
                @php
                    $keterangan .= $trx->keterangan_transaksi . '<br>';
                    if (
                        $trx->rekening_kredit == '4.1.01.04' ||
                        $trx->rekening_kredit == '4.1.01.05' ||
                        $trx->rekening_kredit == '4.1.01.06'
                    ) {
                        $denda += $trx->jumlah;
                    }

                    $idt = $trx->idt;
                @endphp
            @endforeach
            <tr>
                <td align="center">{{ $loop->iteration }}</td>
                <td align="center">{{ Tanggal::tglIndo($real_i->tgl_transaksi) }}</td>
                <td>{!! $keterangan !!}</td>
                <td align="center">{{ $real_i->id }}</td>
                <td align="right">0</td>
                <td align="right">{{ number_format($real_i->realisasi_pokok) }}</td>
                <td align="right">{{ number_format($real_i->realisasi_jasa) }}</td>
                <td align="right">{{ number_format($denda) }}</td>
                <td align="right">
                    <div class="btn-group">
                        <button type="button" class="btn btn-instagram btn-icon-only btn-tooltip"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="btn-inner--icon"><i class="fas fa-file"></i></span>
                        </button>
                        <ul class="dropdown-menu px-2 py-3" aria-labelledby="dropdownMenuButton">
                            <li>
                                <a class="dropdown-item border-radius-md" target="_blank"
                                    href="/transaksi/dokumen/struk_individu/{{ $real_i->id }}">
                                    Kuitansi
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item border-radius-md" target="_blank"
                                    href="/transaksi/dokumen/struk_matrix_individu/{{ $real_i->id }}">
                                    Kuitansi Dot Matrix
                                </a>
                            </li>
                        </ul>
                        <button type="button"
                            data-action="/perguliran_i/dokumen/kartu_angsuran/{{ $real_i->loan_id }}/{{ $real_i->id }}"
                            class="btn btn-github btn-icon-only btn-tooltip btn-link" data-bs-toggle="tooltip"
                            data-bs-placement="top" title="kartu angsuran" data-container="body" data-animation="true">
                            <span class="btn-inner--icon"><i class="fas fa-file-invoice"></i></span>
                        </button>

                        <button type="button" data-action="/transaksi/dokumen/bkm_angsuran/{{ $idt }}"
                            class="btn btn-github btn-icon-only btn-tooltip btn-link" data-bs-toggle="tooltip"
                            data-bs-placement="top" title="BKM" data-container="body" data-animation="true">
                            <span class="btn-inner--icon"><i class="fas fa-file-circle-exclamation"></i></span>
                        </button>
                        @if (auth()->user()->level == 1 && auth()->user()->jabatan == 1)
                            <button type="button"
                                class="btn btn-danger btn-icon-only btn-tooltip btn-delete-angsuran"
                                data-idt="{{ $idt }}"
                                data-idtp="{{ $real_i->id }}"
                                data-id-pinj="0"
                                data-id-pinj-i="{{ $nia->id }}"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top" title="Hapus Transaksi" data-container="body"
                                data-animation="true">
                                <span class="btn-inner--icon"><i class="fas fa-trash"></i></span>
                            </button>
                        @endif
                    </div>
                </td>
            </tr>

            @php
                $t_pokok += $real_i->realisasi_pokok;
                $t_jasa += $real_i->realisasi_jasa;
                $t_denda += $denda;
            @endphp
        @endforeach

        <tr>
            <td colspan="4" align="right" style="text-transform: uppercase;">
                <b>Total Transaksi</b>
            </td>
            <td align="right"><b>0</b></td>
            <td align="right"><b>{{ number_format($t_pokok) }}</b></td>
            <td align="right"><b>{{ number_format($t_jasa) }}</b></td>
            <td align="right"><b>{{ number_format($t_denda) }}</b></td>
            <td>&nbsp;</td>
        </tr>

        <tr>
            <td colspan="4" align="right" style="text-transform: uppercase;">
                <b>Saldo</b>
            </td>
            <td align="right"><b>{{ number_format($nia->alokasi) }}</b></td>
            <td align="right"><b>{{ number_format($nia->alokasi - $t_pokok) }}</b></td>
            <td align="right"><b>{{ number_format(($nia->alokasi * $nia->pros_jasa) / 100 - $t_jasa) }}</b></td>
            <td align="right"><b>{{ number_format($t_denda) }}</b></td>
            <td>&nbsp;</td>
        </tr>
    </tbody>
</table>
