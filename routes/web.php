<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\JadwalController;
use App\Http\Controllers\Admin\SantriController;
use App\Http\Controllers\Admin\RfidController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\SettingRoleController;
use App\Http\Controllers\Admin\TeacherAttendanceController as AdminTeacherAttendanceController;
use App\Http\Controllers\Admin\TeacherScheduleController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\TeacherAttendanceController;
use Illuminate\Support\Facades\Route;

// Root redirect & alias
Route::get('/', function () {
    return redirect('/admin/login');
});
Route::get('/guru', function () {
    return redirect()->route('guru.dashboard');
});

// ─── AUTH ─────────────────────────────────────────────────────────────────────
Route::get('/admin/login',        [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login',       [AdminAuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout',      [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile');
Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'updateProfile'])->name('profile.update');
Route::post('/profile/username', [\App\Http\Controllers\ProfileController::class, 'updateUsername'])->name('profile.username');
Route::post('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');

// Debug login (existing)
Route::get('/admin/debug-login',  [AdminAuthController::class, 'debugLoginPage'])->name('admin.debug.login.page');
Route::post('/admin/debug-login', [AdminAuthController::class, 'debugLogin'])->name('admin.debug.login');

// ─── ADMIN PANEL ──────────────────────────────────────────────────────────────
Route::middleware(['auth.admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Santri
    Route::get('/santri',          [SantriController::class, 'index'])->name('santri.index');
    Route::post('/santri',         [SantriController::class, 'store'])->name('santri.store');
    Route::put('/santri/{id}',     [SantriController::class, 'update'])->name('santri.update');
    Route::delete('/santri/{id}',  [SantriController::class, 'destroy'])->name('santri.destroy');

    // Guru
    Route::get('/guru',            [GuruController::class, 'index'])->name('guru.index');
    Route::post('/guru',           [GuruController::class, 'store'])->name('guru.store');
    Route::put('/guru/{id}',       [GuruController::class, 'update'])->name('guru.update');
    Route::delete('/guru/{id}',    [GuruController::class, 'destroy'])->name('guru.destroy');

    // Jadwal
    Route::get('/jadwal',                [JadwalController::class, 'index'])->name('jadwal.index');
    Route::post('/jadwal',               [JadwalController::class, 'store'])->name('jadwal.store');
    Route::put('/jadwal/{id}',           [JadwalController::class, 'update'])->name('jadwal.update');
    Route::delete('/jadwal/{id}',        [JadwalController::class, 'destroy'])->name('jadwal.destroy');
    Route::patch('/jadwal/{id}/toggle',  [JadwalController::class, 'toggleStatus'])->name('jadwal.toggle');
    Route::post('/jadwal/libur',         [JadwalController::class, 'storeLibur'])->name('jadwal.libur.store');
    Route::delete('/jadwal/libur/{id}',  [JadwalController::class, 'destroyLibur'])->name('jadwal.libur.destroy');

    // RFID & Laporan
    Route::get('/rfid',        [RfidController::class, 'index'])->name('rfid');
    Route::get('/setting-role', [SettingRoleController::class, 'index'])->name('setting-role');
    Route::post('/setting-role', [SettingRoleController::class, 'store'])->name('setting-role.store');
    Route::put('/setting-role/{account}', [SettingRoleController::class, 'update'])->name('setting-role.update');
    Route::post('/setting-role/{account}/link-guru', [SettingRoleController::class, 'linkGuru'])->name('setting-role.link-guru');
    Route::delete('/setting-role/{account}', [SettingRoleController::class, 'destroy'])->name('setting-role.destroy');
    Route::get('/laporan',     [LaporanController::class, 'index'])->name('laporan');
    Route::get('/absensi-mengajar', [AdminTeacherAttendanceController::class, 'index'])->name('teacher-attendance.index');
    Route::patch('/absensi-mengajar/{id}/approve', [AdminTeacherAttendanceController::class, 'approve'])->name('teacher-attendance.approve');
    Route::patch('/absensi-mengajar/{id}/reject', [AdminTeacherAttendanceController::class, 'reject'])->name('teacher-attendance.reject');

    // Master Jadwal Mengajar Guru
    Route::get('/jadwal-mengajar',          [TeacherScheduleController::class, 'index'])->name('teacher-schedule.index');
    Route::post('/jadwal-mengajar',         [TeacherScheduleController::class, 'store'])->name('teacher-schedule.store');
    Route::put('/jadwal-mengajar/{id}',     [TeacherScheduleController::class, 'update'])->name('teacher-schedule.update');
    Route::delete('/jadwal-mengajar/{id}',  [TeacherScheduleController::class, 'destroy'])->name('teacher-schedule.destroy');
});

// ─── WALI KELAS PANEL ────────────────────────────────────────────────────────
Route::prefix('wali-kelas')->name('wali-kelas.')->group(function () {
    Route::get('/dashboard',       [\App\Http\Controllers\WaliKelasController::class, 'dashboard'])->name('dashboard');
    Route::get('/notifikasi',      [\App\Http\Controllers\WaliKelasController::class, 'notifikasi'])->name('notifikasi');
    Route::get('/laporan',         [\App\Http\Controllers\WaliKelasController::class, 'laporan'])->name('laporan');
    Route::get('/santri',          [\App\Http\Controllers\WaliKelasController::class, 'santri'])->name('santri');
    Route::get('/absensi-manual',  [\App\Http\Controllers\WaliKelasController::class, 'absensiManual'])->name('absensi-manual');
    Route::post('/absensi-manual', [\App\Http\Controllers\WaliKelasController::class, 'saveAbsensiManual'])->name('absensi-manual.post');
    Route::get('/absensi-mengajar', [TeacherAttendanceController::class, 'index'])->name('teacher-attendance.index');
    Route::post('/absensi-mengajar', [TeacherAttendanceController::class, 'store'])->name('teacher-attendance.store');
    Route::patch('/absensi-mengajar/{id}/approve', [\App\Http\Controllers\WaliKelasController::class, 'approveTeacherAttendance'])->name('teacher-attendance.approve');
    Route::patch('/absensi-mengajar/{id}/reject', [\App\Http\Controllers\WaliKelasController::class, 'rejectTeacherAttendance'])->name('teacher-attendance.reject');
});

// ─── GURU BIASA PANEL ────────────────────────────────────────────────────────
Route::prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard',        [\App\Http\Controllers\GuruPanelController::class, 'dashboard'])->name('dashboard');
    Route::get('/jadwal',           [\App\Http\Controllers\GuruPanelController::class, 'jadwal'])->name('jadwal');
    Route::get('/absensi-mengajar', [TeacherAttendanceController::class, 'index'])->name('absensi-mengajar');
    Route::post('/absensi-mengajar',[TeacherAttendanceController::class, 'store'])->name('absensi-mengajar.store');
    Route::get('/absensi-santri',   [\App\Http\Controllers\GuruPanelController::class, 'absensiSantri'])->name('absensi-santri');
    Route::post('/absensi-santri/simpan', [\App\Http\Controllers\GuruPanelController::class, 'saveAbsensiSantri'])->name('absensi-santri.save');
    Route::get('/riwayat-mengajar', [\App\Http\Controllers\GuruPanelController::class, 'riwayatMengajar'])->name('riwayat-mengajar');
    Route::get('/profil',           [\App\Http\Controllers\GuruPanelController::class, 'profil'])->name('profil');
});

// ─── API ENDPOINTS FOR NEW FRONTEND (kept intact) ───
Route::middleware(['auth.admin'])->group(function () {
    // API endpoints used by the new RFID dashboard
    Route::post('/scanner',        [AttendanceController::class, 'scan'])->name('attendance.scan');
    Route::post('/input-manual',   [AttendanceController::class, 'manual'])->name('attendance.manual.post');
    Route::post('/attendance/manual', [AttendanceController::class, 'manual'])->name('attendance.manual.post.legacy');

    // Old Pages (Disabled)
    // Route::get('/scanner',         [AttendanceController::class, 'scanPage'])->name('attendance.scan.page');
    // Route::get('/input-manual',    [AttendanceController::class, 'manualPage'])->name('attendance.manual.page');
    // Route::get('/attendance/manual',  [AttendanceController::class, 'manualPage'])->name('attendance.manual.page.legacy');
    // Route::get('/laporan-harian',     [AttendanceController::class, 'dailyReport'])->name('attendance.daily.report');
    // Route::get('/laporan-harian/export', [AttendanceController::class, 'exportExcel'])->name('attendance.daily.export');
    // Route::get('/jurnal-bulanan',     [AttendanceController::class, 'monthlyJournal'])->name('attendance.monthly.journal');
    // Route::get('/jurnal-bulanan/export', [AttendanceController::class, 'exportMonthlyJournal'])->name('attendance.monthly.export');
});
