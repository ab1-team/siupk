<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CatatanBp;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class FormPengawasController extends Controller
{
    public function index(Request $request)
    {
        $filterBulan = $request->get('bulan', '');
        $filterTahun = $request->get('tahun', now()->year);
        $lokasi      = Session::get('lokasi') ?? Auth::user()->lokasi ?? 0;

        $query = CatatanBp::where('lokasi', $lokasi);

        // bulan_laporan formatnya "YYYY-MM" (varchar), pakai LIKE
        if (!empty($filterTahun)) {
            $query->where('bulan_laporan', 'like', $filterTahun . '-%');
        }
        if (!empty($filterBulan)) {
            $bulanPad = str_pad($filterBulan, 2, '0', STR_PAD_LEFT); // "5" -> "05"
            $query->where('bulan_laporan', 'like', '%-' . $bulanPad);
        }

        $data = $query->orderBy('tanggal', 'desc')->paginate(5)->withQueryString();

        $title = 'Form Pengawas';

        return view('pengawas.form_pengawas', compact('data', 'title', 'filterBulan', 'filterTahun'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bulan_laporan' => 'required',
            'isi'           => 'required',
        ]);

        $lokasi = Session::get('lokasi') ?? Auth::user()->lokasi ?? 0;

        CatatanBp::create([
            'tanggal'       => now(),
            'lokasi'        => $lokasi,
            'bulan_laporan' => $request->bulan_laporan,
            'isi'           => $request->isi,
            'id_sender'     => Auth::id(),
        ]);

        return redirect()->route('form_pengawas.index')
                         ->with('success', 'Catatan berhasil disimpan.');
    }

    public function edit($id)
    {
        $lokasi  = Session::get('lokasi') ?? Auth::user()->lokasi ?? 0;
        $catatan = CatatanBp::where('id', $id)
                            ->where('lokasi', $lokasi)
                            ->firstOrFail();

        $filterBulan = '';
        $filterTahun = now()->year;

        $data = CatatanBp::where('lokasi', $lokasi)
                    ->orderBy('tanggal', 'desc')
                    ->paginate(5);

        $title = 'Edit Catatan Pengawas';

        return view('pengawas.form_pengawas', compact('data', 'catatan', 'title', 'filterBulan', 'filterTahun'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'bulan_laporan' => 'required',
            'isi'           => 'required',
        ]);

        $lokasi  = Session::get('lokasi') ?? Auth::user()->lokasi ?? 0;
        $catatan = CatatanBp::where('id', $id)
                            ->where('lokasi', $lokasi)
                            ->firstOrFail();

        $catatan->update([
            'bulan_laporan' => $request->bulan_laporan,
            'isi'           => $request->isi,
        ]);

        return redirect()->route('form_pengawas.index')
                         ->with('success', 'Catatan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $lokasi  = Session::get('lokasi') ?? Auth::user()->lokasi ?? 0;
        $catatan = CatatanBp::where('id', $id)
                            ->where('lokasi', $lokasi)
                            ->firstOrFail();

        $catatan->delete();

        return redirect()->route('form_pengawas.index')
                         ->with('success', 'Catatan berhasil dihapus.');
    }
}
