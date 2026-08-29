<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SantriController extends Controller
{
    public function index(Request $request)
    {
        $kelasList = Classroom::orderBy('name')->get();
        $santrisDb = Santri::with('classroom')->orderBy('name')->get();

        $santris = $santrisDb->map(function ($s) {
            $gender = in_array((string) ($s->jenis_kelamin ?? ''), ['L', 'P'], true)
                ? (string) $s->jenis_kelamin
                : 'L';

            return [
                'id'            => $s->id,
                'name'          => $s->name,
                'nis'           => $s->nis ?? ('SAN' . str_pad($s->id, 3, '0', STR_PAD_LEFT)),
                'rfid_barcode'  => $s->rfid_barcode,
                'classroom_id'  => $s->classroom_id,
                'kelas'         => $s->classroom ? $s->classroom->name : 'Tanpa Kelas',
                'status'        => 'Aktif',
                'jenis_kelamin' => $gender,
            ];
        });

        return response()->view('admin.santri.index', compact(
            'santris',
            'kelasList'
        ))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'nis'           => 'required|string|max:50',
            'jenis_kelamin' => 'required|in:L,P',
            'classroom_id'  => 'required|exists:classrooms,id',
            'kelas'         => 'nullable|string',
            'rfid_barcode'  => 'required|string|max:100|unique:santris,rfid_barcode',
        ]);

        $classroomId = $request->classroom_id;
        if (!$classroomId && $request->kelas) {
            $cls = Classroom::firstOrCreate(['name' => $request->kelas]);
            $classroomId = $cls->id;
        }

        Santri::create([
            'name'         => $request->name,
            'rfid_barcode' => $request->rfid_barcode,
            'classroom_id' => $classroomId,
            'jenis_kelamin' => $request->jenis_kelamin,
        ]);

        return redirect()->route('admin.santri.index')
            ->with('success', 'Santri berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $santri = Santri::findOrFail($id);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'nis'           => 'nullable|string|max:50',
            'jenis_kelamin' => 'required|in:L,P',
            'classroom_id'  => 'required|exists:classrooms,id',
            'rfid_barcode'  => ['required', 'string', 'max:100', Rule::unique('santris', 'rfid_barcode')->ignore($santri->id)],
        ]);

        $santri->update($validated);

        return redirect()->route('admin.santri.index')
            ->with('success', 'Data santri berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $santri = Santri::findOrFail($id);

        // Soft delete keeps the santri row and attendance history intact.
        $santri->delete();

        return redirect()->route('admin.santri.index')
            ->with('success', 'Data santri berhasil dihapus tanpa menghapus riwayat absensi.');
    }
}
