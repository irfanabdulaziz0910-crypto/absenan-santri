<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use App\Models\Jadwal;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        $attendanceService = app(AttendanceService::class);
        $jadwals      = Jadwal::orderBy('urutan')->get();
        $hariLibursDb = HariLibur::orderBy('tanggal')->get();

        $hariLiburs = $hariLibursDb->map(function ($hl) use ($attendanceService) {
            $sesiList = [];
            if ($attendanceService->isHolidayForSession($hl->keterangan, 'Subuh')
                && $attendanceService->isHolidayForSession($hl->keterangan, 'Dzuhur')
                && $attendanceService->isHolidayForSession($hl->keterangan, 'Asar')
                && $attendanceService->isHolidayForSession($hl->keterangan, 'Isya')) {
                $sesiList = ['Semua Sesi'];
            } else {
                foreach (['Subuh', 'Dzuhur', 'Asar', 'Isya'] as $s) {
                    if ($attendanceService->isHolidayForSession($hl->keterangan, $s)) {
                        $sesiList[] = $s === 'Asar' ? 'Ashar' : $s;
                    }
                }
            }

            return [
                'id'         => $hl->id,
                'tanggal'    => $hl->tanggal ? $hl->tanggal->format('Y-m-d') : '',
                'sesi'       => $sesiList,
                'keterangan' => $hl->keterangan,
            ];
        });

        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        return view('admin.jadwal.index', compact('jadwals', 'hariLiburs', 'month', 'year'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'jam_mulai'     => 'required|date_format:H:i',
            'jam_selesai'   => 'required|date_format:H:i|after:jam_mulai',
            'status'        => 'required|in:aktif,nonaktif',
        ]);

        $lastUrutan = Jadwal::max('urutan') ?? 0;

        Jadwal::create([
            'nama_kegiatan' => $request->nama_kegiatan,
            'hari'          => $request->hari ?? [],
            'jam_mulai'     => $request->jam_mulai . ':00',
            'jam_selesai'   => $request->jam_selesai . ':00',
            'status'        => $request->status,
            'urutan'        => $lastUrutan + 1,
        ]);

        return redirect()->route('admin.jadwal.index')
            ->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $jadwal = Jadwal::findOrFail($id);

        $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'jam_mulai'     => 'required|date_format:H:i',
            'jam_selesai'   => 'required|date_format:H:i|after:jam_mulai',
            'status'        => 'required|in:aktif,nonaktif',
        ]);

        $jadwal->update([
            'nama_kegiatan' => $request->nama_kegiatan,
            'hari'          => $request->hari ?? [],
            'jam_mulai'     => $request->jam_mulai . ':00',
            'jam_selesai'   => $request->jam_selesai . ':00',
            'status'        => $request->status,
        ]);

        return redirect()->route('admin.jadwal.index')
            ->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Jadwal::findOrFail($id)->delete();

        return redirect()->route('admin.jadwal.index')
            ->with('success', 'Jadwal berhasil dihapus.');
    }

    public function toggleStatus($id)
    {
        $jadwal = Jadwal::findOrFail($id);
        $jadwal->status = $jadwal->status === 'aktif' ? 'nonaktif' : 'aktif';
        $jadwal->save();

        return redirect()->route('admin.jadwal.index')
            ->with('success', 'Status jadwal berhasil diubah.');
    }

    public function storeLibur(Request $request)
    {
        $request->validate([
            'tanggal'     => 'required|date',
            'keterangan'  => 'nullable|string|max:255',
            'sesi'        => 'nullable',
        ]);

        $keteranganInput = trim($request->keterangan ?? '');
        $sesiList = $request->input('sesi', []);

        if (is_string($sesiList)) {
            $sesiList = json_decode($sesiList, true) ?: [$sesiList];
        }

        $ketFinal = '';
        if (!empty($sesiList)) {
            if (in_array('Semua Sesi', $sesiList) || count($sesiList) >= 4) {
                $ketFinal = 'Libur Semua Sesi';
            } else {
                $ketFinal = 'Libur Sesi ' . implode(', ', $sesiList);
            }
            if ($keteranganInput) {
                $ketFinal .= ' - ' . $keteranganInput;
            }
        } else {
            $ketFinal = $keteranganInput ?: 'Hari Libur Pesantren';
        }

        $libur = HariLibur::updateOrCreate(
            ['tanggal' => $request->tanggal],
            ['keterangan' => $ketFinal]
        );

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Hari libur berhasil disimpan ke database.',
                'data'    => [
                    'id'         => $libur->id,
                    'tanggal'    => $libur->tanggal ? $libur->tanggal->format('Y-m-d') : $request->tanggal,
                    'keterangan' => $libur->keterangan
                ]
            ]);
        }

        return redirect()->route('admin.jadwal.index')
            ->with('success', 'Hari libur berhasil disimpan.');
    }

    public function destroyLibur($id)
    {
        if (is_numeric($id)) {
            HariLibur::where('id', $id)->delete();
        } else {
            HariLibur::whereDate('tanggal', $id)->delete();
        }

        if (request()->expectsJson() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Hari libur berhasil dihapus dari database.']);
        }

        return redirect()->route('admin.jadwal.index')
            ->with('success', 'Hari libur berhasil dihapus.');
    }
}
