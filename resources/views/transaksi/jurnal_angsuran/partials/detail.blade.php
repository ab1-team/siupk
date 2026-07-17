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
            <td align="right"><b>{{ number_format($pinkel->alokasi) }}</b></td>
            <td align="right"><b>{{ number_format($pinkel->alokasi) }}</b></td>
            <td align="right"><b>{{ number_format(($pinkel->alokasi * $pinkel->pros_jasa) / 100) }}</b></td>
            <td align="right"><b>0</b></td>
            <td align="right">&nbsp;</td>
        </tr>
        @foreach ($pinkel->real as $real)
            @php
                $keterangan = '';
                $denda = 0;
                $idt = 0;
            @endphp
            @foreach ($real->trx as $trx)
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
                <td align="center">{{ Tanggal::tglIndo($real->tgl_transaksi) }}</td>
                <td>{!! $keterangan !!}</td>
                <td align="center">{{ $real->id }}</td>
                <td align="right">0</td>
                <td align="right">{{ number_format($real->realisasi_pokok) }}</td>
                <td align="right">{{ number_format($real->realisasi_jasa) }}</td>
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
                                    href="/transaksi/dokumen/struk/{{ $real->id }}">
                                    Kuitansi
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item border-radius-md" target="_blank"
                                    href="/transaksi/dokumen/struk_matrix/{{ $real->id }}">
                                    Kuitansi Dot Matrix
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item border-radius-md" target="_blank"
                                    href="/transaksi/dokumen/struk_thermal/{{ $real->id }}">
                                    Kuitansi Thermal
                                </a>
                            </li>
                        </ul>
                        <button type="button" class="btn btn-tumblr btn-icon-only btn-tooltip"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="btn-inner--icon"><i class="fas fa-file-invoice"></i></span>
                        </button>
                        <ul class="dropdown-menu px-2 py-3" aria-labelledby="dropdownMenuButton">
                            <li>
                                <a class="dropdown-item border-radius-md" target="_blank"
                                    href="/perguliran/dokumen/kartu_angsuran/{{ $real->loan_id }}/{{ $real->id }}">
                                    Kelompok
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item border-radius-md" target="_blank"
                                    href="/perguliran/dokumen/cetak_kartu_angsuran_anggota/{{ $real->loan_id }}/{{ $real->id }}">
                                    Anggota
                                </a>
                            </li>
                        </ul>
                        <button type="button" data-action="/transaksi/dokumen/bkm_angsuran/{{ $idt }}"
                            class="btn btn-github btn-icon-only btn-tooltip btn-link" data-bs-toggle="tooltip"
                            data-bs-placement="top" title="BKM" data-container="body" data-animation="true">
                            <span class="btn-inner--icon"><i class="fas fa-file-circle-exclamation"></i></span>
                        </button>
                        @if (auth()->user()->level == 1 && auth()->user()->jabatan == 1)
                            <button type="button"
                                class="btn btn-danger btn-icon-only btn-tooltip btn-delete-angsuran"
                                data-idt="{{ $idt }}"
                                data-idtp="{{ $real->id }}"
                                data-id-pinj="{{ $pinkel->id }}"
                                data-id-pinj-i="0"
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
                $t_pokok += $real->realisasi_pokok;
                $t_jasa += $real->realisasi_jasa;
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
            <td align="right"><b>{{ number_format($pinkel->alokasi) }}</b></td>
            <td align="right"><b>{{ number_format($pinkel->alokasi - $t_pokok) }}</b></td>
            <td align="right"><b>{{ number_format(($pinkel->alokasi * $pinkel->pros_jasa) / 100 - $t_jasa) }}</b></td>
            <td align="right"><b>{{ number_format($t_denda) }}</b></td>
            <td>&nbsp;</td>
        </tr>
    </tbody>
</table>
