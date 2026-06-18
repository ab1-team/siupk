<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Usaha;
use App\Models\Desa;
use App\Models\Keluarga;
use App\Models\Kecamatan;
use App\Models\SebutanAgent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Session;
use Yajra\DataTables\Facades\DataTables;


class AgentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $desa = Desa::where('kd_kec', $kec['kd_kec'])->with('sebutan_desa')->get();
        if (request()->ajax()) {
            $data = Agent::where('lokasi', $kec->id)->with('d')->get();
            return DataTables::of($data)
                ->make(true);
        }

        $desa_dipilih = 0;
        if (request()->get('desa')) {
            $desa_dipilih = request()->get('desa');
        }
        $title = 'Data Agent';
        $sebutan = SebutanAgent::all();
        return view('agent.index')->with(compact('sebutan', 'title', 'kec', 'desa', 'desa_dipilih'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $desa = Desa::where('kd_kec', $kec['kd_kec'])->with('sebutan_desa')->get();


        $desa_dipilih = 0;
        $agent_dipilih = 0;

        return view('agent.create')->with(compact('desa', 'desa_dipilih'));
    }

    public function generateKode()
    {
        $lokasi = Session::get('lokasi');
        $kd_desa = request()->get('kode');

        $jumlah_agent_by_kd_desa = Agent::where('desa', $kd_desa)->orderBy('kd_agent', 'DESC');
        if ($jumlah_agent_by_kd_desa->count() > 0) {
            $data_agent = $jumlah_agent_by_kd_desa->first();
            $kode_agent = explode('-', $data_agent->kd_agent);

            if (count($kode_agent) >= 2) {
                $kd_agent = $kode_agent[0] . '-' . str_pad(($kode_agent[1] + 1), 3, "0", STR_PAD_LEFT);
            } else {
                $jumlah_agent = str_pad(Agent::where('desa', $kd_desa)->count() + 1, 3, "0", STR_PAD_LEFT);
                $kd_agent = $kd_desa . '-' . $jumlah_agent;
            }

            // $kd_agent = $data_agent->kd_agent + 1;

        } else {
            $jumlah_agent = str_pad(Agent::where('desa', $kd_desa)->count() + 1, 3, "0", STR_PAD_LEFT);
            $kd_agent = $kd_desa . '-' . $jumlah_agent;
            // $kd_agent = $kd_desa . $jumlah_agent;

        }

        if (request()->get('kd_agent')) {
            $kd_kel = request()->get('kd_agent');
            $agent = Agent::where('kd_agent', $kd_kel);
            if ($agent->count() > 0) {
                $data_kel = $agent->first();

                if ($kd_desa == $data_kel->desa) {
                    $kd_agent = $data_kel->kd_agent;
                }
            }
        }

        return response()->json([
            'kd_agent' => $kd_agent
        ], Response::HTTP_ACCEPTED);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->only([
            'desa',
            'kd_agent',
            'agent',
            'alamat',
            'nohp'
        ]);
        $rules = [
            'desa'      => 'required',
            'kd_agent' => 'required|unique:agent,kd_agent',
            'agent'     => 'required',
            'alamat'    => 'required',
            'nohp'      => 'required'
        ];

        $validate = Validator::make($data, $rules);

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        $insert = [
            'lokasi'    => Session::get('lokasi'),
            'kd_agent'  => $request->kd_agent,
            'agent'     => $request->agent,
            'alamat'    => $request->alamat,
            'desa'      => $request->desa,
            'nohp'      => $request->nohp
        ];

        $agent = Agent::create($insert);
        return response()->json([
            'msg' => 'Register Agent dengan Kode Agent ' . $insert['kd_agent'] . ' berhasil disimpan'
        ], Response::HTTP_ACCEPTED);
    }
    /**
     * Display the specified resource.
     */
    public function show(Agent $agent)
    {
        //
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Agent $agent)
    {
        $sebutan = SebutanAgent::all();
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $desa = Desa::where('kd_kec', $kec['kd_kec'])->with('sebutan_desa')->get();

        $desa_dipilih = 0;

        return view('agent.edit')->with(compact('kec', 'agent', 'sebutan', 'desa', 'desa_dipilih'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Agent $agent)
    {
        $data = $request->only([
            "desa",
            "kd_agent",
            "agent",
            "alamat",
            "nohp"

        ]);

        $validate = Validator::make($data, [
            "desa"      => 'required',
            "kd_agent" => 'required|unique:agent,kd_agent',
            "agent"     => 'required',
            "alamat"    => 'required',
            "nohp"      => 'required'

        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        agent::where('id', $agent->id)->update($data);
        return response()->json([
            'msg' => 'Agent ' . $agent->nama . ' berhasil diperbarui'
        ], Response::HTTP_OK);
    }

    /**z
     * Remove the specified resource from storage.
     */
    public function destroy(Agent $agent)
    {
        //
    }
}
