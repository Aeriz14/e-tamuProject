<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

/**
 * Koki Kepala (DashboardController)
 * Menyiapkan semua data yang dibutuhkan untuk tampilan dashboard admin.
 * PERBAIKAN: Menggunakan fungsi tanggal yang kompatibel dengan SQLite dan MySQL.
 */
class DashboardController extends Controller
{
    private function getBaseQuery(Request $request)
    {
        $user = $request->user();
        $query = Appointment::query();

        if ($user->role === 'admin' && $user->division_id) {
            $query->where('division_id', $user->division_id);
        }

        return $query;
    }

    public function getDashboardSummary(Request $request)
    {
        $user = $request->user();
        $baseQuery = $this->getBaseQuery($request);

        $appointments = (clone $baseQuery)->with('division')->latest()->get();
        $totalGuests = (clone $baseQuery)->count();

        $statusStatsQuery = (clone $baseQuery)
            ->select('status_pertemuan', DB::raw('count(*) as total'))
            ->groupBy('status_pertemuan')
            ->pluck('total', 'status_pertemuan');

        $statusStats = [
            'pending' => $statusStatsQuery->get('pending', 0),
            'selesai' => $statusStatsQuery->get('selesai', 0),
            'ditolak' => $statusStatsQuery->get('ditolak', 0),
        ];

        $visitsByDivisionQuery = Appointment::join('divisions', 'appointments.division_id', '=', 'divisions.id')
            ->select('divisions.name', DB::raw('count(appointments.id) as total'))
            ->groupBy('divisions.name')
            ->orderBy('divisions.name', 'asc');

        if ($user->role === 'admin') {
            $visitsByDivisionQuery->where('appointments.division_id', $user->division_id);
        }
        $visitsByDivision = $visitsByDivisionQuery->get();

        $totalAdmins = ($user->role === 'super_admin') ? User::count() : 0;

        // --- PERBAIKAN KRITIS UNTUK SQLITE ---
        // Mendeteksi driver database yang sedang aktif
        $dbDriver = Config::get('database.default');
        $monthExpression = $dbDriver === 'sqlite'
            ? "strftime('%m', appointment_date)" // Fungsi untuk SQLite
            : "MONTH(appointment_date)"; // Fungsi untuk MySQL

        $monthlyDataRaw = (clone $baseQuery)->select(
                DB::raw("$monthExpression as month"),
                DB::raw('COUNT(*) as count')
            )
            ->whereYear('appointment_date', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthKey = str_pad($i, 2, '0', STR_PAD_LEFT);
            $monthlyData[] = $monthlyDataRaw[$monthKey] ?? ($monthlyDataRaw[$i] ?? 0);
        }

        $pendingCount = $statusStats['pending'];

        return response()->json([
            'appointments' => $appointments,
            'total_guests' => $totalGuests,
            'total_admins' => $totalAdmins,
            'status_stats' => $statusStats,
            'visits_by_division' => $visitsByDivision,
            'monthly_data' => $monthlyData,
            'pending_count' => $pendingCount,
        ]);
    }
}
