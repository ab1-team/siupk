<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Whatsapp;
use App\Services\WaService;
use Illuminate\Http\Request;

class WaGatewayController extends Controller
{
    public function config(Request $request)
    {
        $kec = $request->attributes->get('tenant_kecamatan');
        $waService = app(WaService::class);
        $wa = Whatsapp::where('lokasi', $kec->id)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'gateway_url' => $waService->baseUrl(),
                'connected' => $wa && $wa->isConnected(),
                'device_id' => $wa?->device_id,
                'device_key' => $wa?->device_key,
                'phone_number' => $wa?->phone_number,
            ],
        ]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'to' => 'required|string',
            'message' => 'required|string',
        ]);

        $kec = $request->attributes->get('tenant_kecamatan');
        $wa = Whatsapp::where('lokasi', $kec->id)->first();

        if (!$wa || !$wa->isConnected()) {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp belum terhubung untuk tenant ini.',
            ], 400);
        }

        $waService = app(WaService::class);
        $number = $waService->normalize($request->input('to'));

        if (!$number) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor tujuan tidak valid.',
            ], 400);
        }

        $result = $waService->sendText($wa, $number, $request->input('message'));

        return response()->json($result, $result['success'] ? 200 : 502);
    }
}
