<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileAccountTest extends TestCase
{
    public function test_admin_can_open_profile_page_and_change_username_and_password(): void
    {
        $suffix = uniqid('admin_profile_');

        $admin = Admin::create([
            'name' => 'Admin Profil',
            'username' => $suffix,
            'password' => 'sandi123',
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get('/profile');
        $response->assertOk();
        $response->assertSee('Profil Saya');

        $newUsername = $suffix . '_edited';

        $response = $this->post('/profile/username', [
            'current_password' => 'sandi123',
            'username' => $newUsername,
        ]);

        $response->assertRedirect('/profile');
        $this->assertEquals($newUsername, $admin->fresh()->username);

        $response = $this->post('/profile/password', [
            'current_password' => 'sandi123',
            'password' => 'newPassword123',
            'password_confirmation' => 'newPassword123',
        ]);

        $response->assertRedirect('/profile');
        $this->assertTrue(Hash::check('newPassword123', $admin->fresh()->password));
    }

    public function test_web_user_can_view_profile_page(): void
    {
        $suffix = uniqid('guru_profile_');

        $user = User::create([
            'name' => 'Guru Profil',
            'username' => $suffix,
            'email' => $suffix . '@santri.com',
            'password' => Hash::make('guru123'),
            'role' => 'wali_kelas',
        ]);

        $this->actingAs($user, 'web');

        $response = $this->get('/profile');
        $response->assertOk();
        $response->assertSee('Profil Saya');
    }

    public function test_default_admin_can_login_with_admin_credentials(): void
    {
        $admin = Admin::firstOrCreate(
            ['username' => 'admin'],
            ['name' => 'Admin Utama', 'password' => 'admin']
        );

        $admin->password = 'admin';
        $admin->save();

        $response = $this->post('/admin/login', [
            'username' => 'admin',
            'password' => 'admin',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_login_uses_username_field_and_rejects_nip_fallback(): void
    {
        $suffix = uniqid('guru_login_');

        $user = User::create([
            'name' => 'Irfan Abdul Aziz',
            'username' => $suffix,
            'email' => $suffix . '@santri.com',
            'password' => Hash::make('secret123'),
            'role' => 'wali_kelas',
        ]);

        $this->post('/admin/login', [
            'username' => '260401',
            'password' => 'secret123',
        ])->assertSessionHasErrors('username');

        $this->post('/admin/login', [
            'username' => $suffix,
            'password' => 'secret123',
        ])->assertRedirect(route('wali-kelas.dashboard'));
    }

    public function test_username_with_space_and_apostrophe_does_not_trigger_nip_lookup(): void
    {
        $username = "Ma'had Ali";

        User::create([
            'name' => 'Irfan Abdul Aziz',
            'username' => $username,
            'email' => 'mahad-ali-' . uniqid() . '@santri.com',
            'password' => Hash::make('260401'),
            'role' => 'wali_kelas',
        ]);

        $response = $this->post('/admin/login', [
            'username' => $username,
            'password' => '260401',
        ]);

        $response->assertRedirect(route('wali-kelas.dashboard'));
    }

    public function test_legacy_nip_username_still_logs_in_for_wali_kelas(): void
    {
        $legacyUsername = '260401_' . uniqid();

        User::where('username', 'like', '260401%')->delete();

        User::create([
            'name' => 'Legacy NIP Guru',
            'username' => $legacyUsername,
            'email' => $legacyUsername . '@santri.com',
            'password' => Hash::make('legacy123'),
            'role' => 'wali_kelas',
        ]);

        $response = $this->post('/admin/login', [
            'username' => $legacyUsername,
            'password' => 'legacy123',
        ]);

        $response->assertRedirect(route('wali-kelas.dashboard'));
        $this->assertTrue(Auth::guard('web')->check());
    }
}
