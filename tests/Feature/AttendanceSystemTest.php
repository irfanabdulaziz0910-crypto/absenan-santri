<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Santri;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AttendanceSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_allows_admin_login_with_username_and_password(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Utama',
            'username' => 'admin',
            'password' => 'password123',
        ]);

        $response = $this->post('/admin/login', [
            'username' => 'admin',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/scanner');
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_allows_login_when_admin_password_is_stored_as_plaintext(): void
    {
        DB::table('admins')->insert([
            'name' => 'Admin Lama',
            'username' => 'legacyadmin',
            'password' => 'password123',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post('/admin/login', [
            'username' => 'legacyadmin',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/scanner');
        $this->assertAuthenticatedAs(
            Admin::where('username', 'legacyadmin')->first(),
            'admin'
        );
    }

    public function test_requires_a_reason_for_sick_or_izin_attendance(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Utama',
            'username' => 'admin2',
            'password' => 'password123',
        ]);
        $santri = Santri::create([
            'barcode' => 'SAN001',
            'nama_santri' => 'Ahmad',
            'kelas' => 'A',
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->post('/attendance/manual', [
            'barcode' => $santri->barcode,
            'status' => 'Sakit',
            'sesi' => 'Subuh',
            'waktu_absen' => now()->toDateTimeString(),
            'keterangan' => '',
        ]);

        $response->assertSessionHasErrors('keterangan');
    }

    public function test_blocks_duplicate_attendance_for_the_same_day_and_session(): void
    {
        $service = app(AttendanceService::class);
        $santri = Santri::create([
            'barcode' => 'SAN002',
            'nama_santri' => 'Budi',
            'kelas' => 'B',
        ]);

        $timestamp = Carbon::parse('2026-08-06 15:30:00');

        $service->storeAttendance($santri, 'Hadir', null, $timestamp, 'Asar');

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->storeAttendance($santri, 'Hadir', null, $timestamp, 'Asar');
    }

    public function test_detects_the_expected_session_windows_for_the_requested_schedule(): void
    {
        $service = app(AttendanceService::class);

        $this->assertNull($service->getCurrentSession(Carbon::parse('2026-08-06 03:30:00')));
        $this->assertNull($service->getCurrentSession(Carbon::parse('2026-08-06 11:00:00')));
        $this->assertNull($service->getCurrentSession(Carbon::parse('2026-08-06 18:30:00')));

        $this->assertSame('Subuh', $service->getCurrentSession(Carbon::parse('2026-08-06 05:15:00')));
        $this->assertSame('Dzuhur', $service->getCurrentSession(Carbon::parse('2026-08-06 12:30:00')));
        $this->assertSame('Asar', $service->getCurrentSession(Carbon::parse('2026-08-06 16:00:00')));
        $this->assertSame('Isya', $service->getCurrentSession(Carbon::parse('2026-08-06 20:15:00')));
    }
}
