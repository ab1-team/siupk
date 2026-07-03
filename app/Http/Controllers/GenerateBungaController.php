<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Simpanan;
use App\Models\Transaksi;
use App\Models\RealSimpanan;
use App\Models\Kecamatan;
use Session;
use DB;
use URL;
use Carbon\Carbon;

class GenerateBungaController extends Controller
{
    public function index(Request $request)
    {
        $kec = Kecamatan::where('web_kec', explode('//', URL::to('/'))[1])
            ->orWhere('web_alternatif', explode('//', URL::to('/'))[1])
            ->first();

        if ($kec) {
            Session::put('lokasi', $kec->id);
        }

        $id = $request->get('id');
        $start = intval($request->get('start', 0));
        $perPage = intval($request->get('limit', 25));

        if ($id === null || $id === '') {
            return view('generate_simpanan.index');
        }

        if ($id === 'all') {
            $ids = Simpanan::pluck('id')->toArray();
        } else {
            $ids = collect(explode(',', $id))
                ->map(fn($i) => trim($i))
                ->filter(fn($i) => is_numeric($i))
                ->toArray();
        }

        if (empty($ids)) {
            return back()->with('error', 'ID tidak valid');
        }

        $total = Simpanan::whereIn('id', $ids)->count();

        // Ambil batch simpanan sesuai start dan perPage
        $simpananBatch = Simpanan::whereIn('id', $ids)
            ->orderBy('id', 'ASC')
            ->skip($start)
            ->take($perPage)
            ->get();

        foreach ($simpananBatch as $simpanan) {
            RealSimpanan::where('cif', $simpanan->id)->delete();

            $trx = $simpanan->trx;
            $sum = 0;
            $str = 1;

            foreach ($trx as $t) {
                $kode = $this->kodeTransaksi($t->rekening_debit, $t->rekening_kredit, $str);
                if ($kode == 1) {
                    $str = 2;
                }

                if (in_array($kode, [1, 2, 5])) {
                    $real_d = 0;
                    $real_k = $t->jumlah;
                    $sum += $t->jumlah;
                } elseif (in_array($kode, [3, 4, 6, 7])) {
                    $real_d = $t->jumlah;
                    $real_k = 0;
                    $sum -= $t->jumlah;
                } else {
                    $real_d = 0;
                    $real_k = 0;
                }

                RealSimpanan::create([
                    'cif' => $simpanan->id,
                    'idt' => $t->idt,
                    'kode' => $kode,
                    'tgl_transaksi' => $t->tgl_transaksi,
                    'real_d' => $real_d,
                    'real_k' => $real_k,
                    'sum' => $sum,
                    'lu' => Carbon::now(),
                    'id_user' => $t->id_user,
                ]);
            }
        }

        $nextStart = $start + $perPage;
        return view('generate_simpanan.result', [
            'total' => $total,
            'processed' => count($simpananBatch),
            'start' => $nextStart,
            'limit' => $perPage,
            'id' => $id,
            'isDone' => $nextStart > $total
        ]);
    }

    private function kodeTransaksi($rdeb, $rkre, $str)
    {
        if (substr($rdeb, 0, 6) == '1.1.01' && in_array(substr($rkre, 0, 6), ['2.1.05', '2.2.05']) && $str == 1) {
            return 1; // setor awal
        } elseif (substr($rdeb, 0, 6) == '1.1.01' && in_array(substr($rkre, 0, 6), ['2.1.05', '2.2.05'])) {
            return 2; // setor
        } elseif (in_array(substr($rdeb, 0, 6), ['2.1.05', '2.2.05']) && substr($rkre, 0, 6) == '1.1.01') {
            return 3; // tarik
        } elseif (substr($rdeb, 0, 6) == '5.2.01' && in_array(substr($rkre, 0, 6), ['2.1.05', '2.2.05'])) {
            return 5; // bunga
        } elseif (in_array(substr($rdeb, 0, 6), ['2.1.05', '2.2.05']) && substr($rkre, 0, 6) == '2.1.03') {
            return 6; // pajak
        } elseif (in_array(substr($rdeb, 0, 6), ['2.1.05', '2.2.05']) && substr($rkre, 0, 6) == '4.1.03') {
            return 7; // admin
        } else {
            return 0;
        }
    }
}
?>
