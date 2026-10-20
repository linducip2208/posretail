<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ShopApi;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    use ShopApi;

    public function clockIn(Request $request): JsonResponse
    {
        $data = $request->validate([
            'outlet_id' => 'required|integer|exists:outlets,id',
            'notes' => 'nullable|string|max:500',
        ]);

        if (! in_array((int) $data['outlet_id'], $this->outletIds($request), true)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke outlet ini.'], 403);
        }

        $existing = Attendance::where('user_id', $request->user()->id)
            ->whereDate('date', today())->first();
        if ($existing && $existing->clock_in) {
            return response()->json(['data' => $existing, 'message' => 'Sudah clock-in hari ini.']);
        }

        $row = Attendance::updateOrCreate(
            ['user_id' => $request->user()->id, 'date' => today()->toDateString()],
            [
                'outlet_id' => $data['outlet_id'],
                'clock_in' => now()->format('H:i:s'),
                'status' => now()->format('H:i') > '08:00' ? 'late' : 'present',
                'notes' => $data['notes'] ?? null,
            ]
        );

        return response()->json(['data' => $row], 201);
    }

    public function clockOut(Request $request): JsonResponse
    {
        $row = Attendance::where('user_id', $request->user()->id)
            ->whereDate('date', today())->first();
        if (! $row || ! $row->clock_in) {
            return response()->json(['message' => 'Belum clock-in hari ini.'], 422);
        }
        if ($row->clock_out) {
            return response()->json(['data' => $row, 'message' => 'Sudah clock-out hari ini.']);
        }

        $row->update(['clock_out' => now()->format('H:i:s')]);

        return response()->json(['data' => $row->fresh()]);
    }

    public function today(Request $request): JsonResponse
    {
        $row = Attendance::where('user_id', $request->user()->id)
            ->whereDate('date', today())->first();

        return response()->json(['data' => $row]);
    }
}
