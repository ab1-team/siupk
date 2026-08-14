<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Simpanan;
use App\Models\Kecamatan;
use App\Services\RealSimpananGenerator;
use Session;
use URL;

class GenerateBungaController extends Controller
{
    public function index(Request $request, RealSimpananGenerator $generator)
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
                ->map(fn($i) => (int) $i)
                ->toArray();
        }

        if (empty($ids)) {
            return back()->with('error', 'ID tidak valid');
        }

        $total = Simpanan::whereIn('id', $ids)->count();

        $simpananBatch = Simpanan::whereIn('id', $ids)
            ->orderBy('id', 'ASC')
            ->skip($start)
            ->take($perPage)
            ->get();

        foreach ($simpananBatch as $simpanan) {
            $generator->generateForCif((int) $simpanan->id);
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
}
?>