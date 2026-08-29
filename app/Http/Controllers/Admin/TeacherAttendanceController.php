<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Guru;
use App\Models\TeacherAttendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TeacherAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = TeacherAttendance::with(['guru', 'classroom', 'approvedBy'])->latest('date')->latest('attendance_time');

        $query->when($request->filled('guru_id'), fn ($q) => $q->where('guru_id', $request->guru_id));
        $query->when($request->filled('classroom_id'), fn ($q) => $q->where('classroom_id', $request->classroom_id));
        $query->when($request->filled('kitab'), fn ($q) => $q->where('kitab', 'like', '%' . $request->kitab . '%'));
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->status));

        $period = $request->input('period');
        $tgl = $request->input('date');

        if ($tgl) {
            $baseDate = Carbon::parse($tgl);
            if ($period === 'harian') {
                $query->whereDate('date', $baseDate->toDateString());
            } elseif ($period === 'mingguan') {
                $start = $baseDate->copy()->startOfWeek(Carbon::SATURDAY);
                $end = $start->copy()->addDays(6);
                $query->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
            } elseif ($period === 'bulanan') {
                $query->whereYear('date', $baseDate->year)->whereMonth('date', $baseDate->month);
            } elseif ($period === 'semester') {
                $start = $baseDate->copy()->subMonths(5)->startOfMonth();
                $query->whereBetween('date', [$start->toDateString(), $baseDate->endOfMonth()->toDateString()]);
            } else {
                $query->whereDate('date', $baseDate->toDateString());
            }
        } elseif ($period) {
            $now = Carbon::now();
            if ($period === 'harian') {
                $query->whereDate('date', $now->toDateString());
            } elseif ($period === 'mingguan') {
                $start = $now->copy()->startOfWeek(Carbon::SATURDAY);
                $end = $start->copy()->addDays(6);
                $query->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
            } elseif ($period === 'bulanan') {
                $query->whereYear('date', $now->year)->whereMonth('date', $now->month);
            } elseif ($period === 'semester') {
                $start = $now->copy()->subMonths(5)->startOfMonth();
                $query->whereBetween('date', [$start->toDateString(), $now->endOfMonth()->toDateString()]);
            }
        }

        return view('admin.teacher-attendance.index', [
            'attendances' => $query->paginate(25)->withQueryString(),
            'gurus' => Guru::orderBy('name')->get(),
            'classrooms' => Classroom::orderBy('name')->get(),
            'filters' => [
                'guru_id' => $request->guru_id,
                'classroom_id' => $request->classroom_id,
                'date' => $request->date,
                'kitab' => $request->kitab,
                'status' => $request->status,
                'period' => $request->period,
            ],
        ]);
    }

    public function approve($id)
    {
        $attendance = TeacherAttendance::findOrFail($id);

        $adminUser = auth()->guard('admin')->user();
        $userInUsersTable = \App\Models\User::where('email', $adminUser?->email)
            ->orWhere('username', $adminUser?->username)
            ->first();

        $attendance->update([
            'approval_status' => 'approved',
            'approved_by'     => $userInUsersTable?->id,
            'approved_at'     => now(),
            'rejection_reason'=> null,
        ]);

        return back()->with('success', 'Permintaan mengajar telah disetujui!');
    }

    public function reject(Request $request, $id)
    {
        $attendance = TeacherAttendance::findOrFail($id);

        $adminUser = auth()->guard('admin')->user();
        $userInUsersTable = \App\Models\User::where('email', $adminUser?->email)
            ->orWhere('username', $adminUser?->username)
            ->first();

        $attendance->update([
            'approval_status' => 'rejected',
            'approved_by'     => $userInUsersTable?->id,
            'approved_at'     => now(),
            'rejection_reason'=> $request->input('rejection_reason', 'Permintaan ditolak oleh Admin'),
        ]);

        return back()->with('success', 'Permintaan mengajar telah ditolak.');
    }
}
