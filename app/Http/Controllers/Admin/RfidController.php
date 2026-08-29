<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Santri;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class RfidController extends Controller
{
    public function index(Request $request, AttendanceService $attendanceService)
    {
        $today = now()->toDateString();
        // Null if current time is outside active session windows
        $currentSession = $request->input('session') ?: $attendanceService->getCurrentSession();
        $currentSession = $attendanceService->normalizeSession($currentSession);

        // Dynamic count 100% from database
        $totalSantri = Santri::count();

        // Count santri who tapped Hadir in current session today
        $hadirSesiIni = $currentSession
            ? Attendance::whereDate('date', $today)
                ->where('session', $currentSession)
                ->where(fn ($q) => $q->where('status', 'hadir')->orWhere('status', 'Hadir'))
                ->count()
            : 0;

        $belumTap = max(0, $totalSantri - $hadirSesiIni);

        $santris = Santri::with(['classroom', 'attendances' => function ($q) use ($today, $currentSession) {
            if ($currentSession) {
                $q->whereDate('date', $today)->where('session', $currentSession);
            } else {
                $q->whereDate('date', $today);
            }
        }])->orderBy('name')->get()->map(function ($s) {
            $attToday = $s->attendances->first();
            return [
                'id'           => $s->id,
                'name'         => $s->name,
                'nis'          => $s->nis ?? '000000',
                'rfid_barcode' => $s->rfid_barcode,
                'kelas'        => $s->classroom->name ?? 'Umum',
                'status_today' => $attToday ? ucfirst(strtolower($attToday->status)) : 'Belum Tap',
                'scan_time'    => $attToday && $attToday->scan_time ? $attToday->scan_time->format('H:i') : '-',
                'avatar'       => 'https://ui-avatars.com/api/?name=' . urlencode($s->name) . '&background=e2f3e9&color=1a4731&bold=true',
            ];
        });

        return view('admin.rfid.index', compact('santris', 'totalSantri', 'hadirSesiIni', 'belumTap', 'currentSession'));
    }
}
