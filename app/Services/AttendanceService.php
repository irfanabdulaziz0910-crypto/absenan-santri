<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Santri;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function normalizeSession(?string $session): ?string
    {
        if ($session === null) {
            return null;
        }

        return match (strtolower(trim($session))) {
            'subuh' => 'Subuh',
            'dzuhur' => 'Dzuhur',
            'asar', 'ashar' => 'Asar',
            'isya' => 'Isya',
            default => $session,
        };
    }

    public function isHolidayForSession(?string $description, string $session): bool
    {
        $description = strtolower(trim($description ?? ''));
        if ($description === '' || str_contains($description, 'semua')) {
            return true;
        }

        $normalizedSession = strtolower($this->normalizeSession($session));
        $mentionsSession = preg_match('/(subuh|dzuhur|asar|ashar|maghrib|isya)/i', $description);

        if (! $mentionsSession) {
            return true;
        }

        if ($normalizedSession === 'asar') {
            return str_contains($description, 'asar') || str_contains($description, 'ashar');
        }

        return str_contains($description, $normalizedSession);
    }

    public function getCurrentSession(?Carbon $now = null): ?string
    {
        $now = $now ?: Carbon::now();
        $time = $now->format('H:i');

        $sessions = [
            ['Subuh', '04:00', '08:00'],
            ['Dzuhur', '11:30', '14:59'],
            ['Asar', '15:00', '17:59'],
            ['Isya', '18:00', '23:59'],
        ];

        foreach ($sessions as [$name, $start, $end]) {
            if ($time >= $start && $time <= $end) {
                return $name;
            }
        }

        return null;
    }

    public function isAttendanceOpen(?Carbon $now = null): bool
    {
        return $this->getCurrentSession($now) !== null;
    }

    public function storeAttendance(Santri $santri, string $status, ?string $notes = null, ?Carbon $scanTime = null, ?string $session = null): Attendance
    {
        $scanTime = $scanTime ?: Carbon::now();
        $session = $this->normalizeSession($session ?: $this->getCurrentSession($scanTime));

        $validator = Validator::make([
            'status' => $status,
            'notes' => $notes,
            'session' => $session,
        ], [
            'status' => ['required', 'in:Hadir,Sakit,Izin,Alfa,Belum Absen'],
            'notes' => ['nullable', 'string'],
            'session' => ['required', 'in:Subuh,Dzuhur,Asar,Isya'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if (in_array($status, ['Sakit', 'Izin'], true) && blank($notes)) {
            throw ValidationException::withMessages([
                'notes' => 'Keterangan wajib diisi untuk status Sakit atau Izin.',
            ]);
        }

        if (Attendance::where('santri_id', $santri->getKey())
            ->where('session', $session)
            ->whereDate('date', $scanTime->toDateString())
            ->exists()) {
            throw ValidationException::withMessages([
                'attendance' => 'Santri sudah melakukan absensi untuk sesi ini.',
            ]);
        }

        return Attendance::updateOrCreate(
            [
                'santri_id' => $santri->getKey(),
                'session'   => $session,
                'date'      => $scanTime->toDateString(),
            ],
            [
                'status'    => $status,
                'scan_time' => $scanTime,
                'notes'     => $notes,
            ]
        );
    }

    public function storeRfidAttendance(Santri $santri, ?string $session = null, ?Carbon $scanTime = null): array
    {
        $scanTime = $scanTime ?: Carbon::now();
        $session = $this->normalizeSession($session ?: $this->getCurrentSession($scanTime));

        $existing = Attendance::where('santri_id', $santri->getKey())
            ->where('session', $session)
            ->whereDate('date', $scanTime->toDateString())
            ->first();

        if ($existing) {
            return [$existing, false];
        }

        try {
            return [Attendance::create([
                'santri_id' => $santri->getKey(),
                'session' => $session,
                'status' => 'Hadir',
                'scan_time' => $scanTime,
                'notes' => 'Tap RFID',
                'date' => $scanTime->toDateString(),
            ]), true];
        } catch (\Illuminate\Database\QueryException $exception) {
            $existing = Attendance::where('santri_id', $santri->getKey())
                ->where('session', $session)
                ->whereDate('date', $scanTime->toDateString())
                ->first();

            if ($existing) {
                return [$existing, false];
            }

            throw $exception;
        }
    }
}
