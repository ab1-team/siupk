<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Whatsapp;

class WACleanupController extends Controller
{
    public function reset()
    {
        $rows = Whatsapp::all();
        $before = [];
        foreach ($rows as $r) {
            $before[] = [
                'lokasi' => $r->lokasi,
                'device_id' => $r->device_id,
                'device_key' => substr((string) $r->device_key, 0, 12) . '...',
            ];
        }
        $deleted = Whatsapp::query()->delete();
        return response()->json([
            'deleted' => $deleted,
            'before' => $before,
        ]);
    }
}
