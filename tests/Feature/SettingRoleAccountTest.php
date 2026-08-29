<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Classroom;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SettingRoleAccountTest extends TestCase
{
    public function test_setting_role_page_includes_admin_guru_and_supervisor_accounts_from_database(): void
    {
        $adminSuffix = uniqid('admin_setting_role_');

        $admin = Admin::create([
            'name' => 'Admin Utama',
            'username' => $adminSuffix,
            'password' => 'password123',
        ]);

        $this->actingAs($admin, 'admin');

        $guruSuffix = uniqid('260401_setting_role_');
        $supervisorSuffix = uniqid('sup01_setting_role_');

        User::create([
            'name' => 'Irfan Abdul Aziz',
            'username' => $guruSuffix,
            'email' => $guruSuffix . '@santri.com',
            'password' => 'password123',
            'role' => 'wali_kelas',
        ]);

        User::create([
            'name' => 'Supervisor A',
            'username' => $supervisorSuffix,
            'email' => $supervisorSuffix . '@santri.com',
            'password' => 'password123',
            'role' => 'supervisor',
        ]);

        $response = $this->get('/admin/setting-role');

        $response->assertOk();
        $this->assertStringContainsString('"role":"Admin Utama"', $response->getContent());
        $this->assertStringContainsString('"role":"Guru"', $response->getContent());
        $this->assertStringContainsString('"role":"Supervisor"', $response->getContent());
    }

    public function test_admin_can_create_guru_with_explicit_username_and_password(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Guru',
            'username' => 'admin_guru_create_' . uniqid(),
            'password' => 'password123',
        ]);

        $this->actingAs($admin, 'admin');

        $classroom = Classroom::firstOrCreate([
            'name' => 'Kelas Uji ' . uniqid(),
        ]);

        $username = 'irfanabdul_' . uniqid();
        $password = 'guruPassword123';

        $response = $this->post('/admin/guru', [
            'name' => 'Irfan Abdul Aziz',
            'nip' => '260401_' . uniqid(),
            'nomor_hp' => '08123456789',
            'classroom_id' => $classroom->id,
            'spesialisasi' => 'Fiqih',
            'status' => 'aktif',
            'username' => $username,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertRedirect('/admin/guru');

        $guru = Guru::where('name', 'Irfan Abdul Aziz')->orderByDesc('id')->first();
        $this->assertNotNull($guru);

        $user = User::where('guru_id', $guru->id)->first();
        $this->assertNotNull($user);
        $this->assertSame($username, $user->username);
        $this->assertSame('wali_kelas', $user->role);
        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertEquals($guru->id, $user->guru_id);
    }
}
