<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Santri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SantriCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_update_santri_data(): void
    {
        $admin = Admin::create([
            'name' => 'Admin CRUD',
            'username' => 'admin-crud',
            'password' => 'password123',
        ]);
        $classroom = Classroom::create(['name' => 'Kelas CRUD']);
        $updatedClassroom = Classroom::create(['name' => 'Kelas CRUD Baru']);

        $this->actingAs($admin, 'admin');

        $createResponse = $this->post(route('admin.santri.store'), [
            'name' => 'Santri CRUD',
            'nis' => 'CRUD001',
            'rfid_barcode' => 'RFID-CRUD-001',
            'classroom_id' => $classroom->id,
            'jenis_kelamin' => 'L',
        ]);

        $createResponse->assertRedirect(route('admin.santri.index'));
        $santri = Santri::where('rfid_barcode', 'RFID-CRUD-001')->firstOrFail();
        $this->assertDatabaseHas('santris', [
            'id' => $santri->id,
            'name' => 'Santri CRUD',
            'jenis_kelamin' => 'L',
        ]);

        $updateResponse = $this->put(route('admin.santri.update', $santri), [
            'name' => 'Santri CRUD Updated',
            'nis' => 'CRUD002',
            'rfid_barcode' => 'RFID-CRUD-002',
            'classroom_id' => $updatedClassroom->id,
            'jenis_kelamin' => 'P',
        ]);

        $updateResponse->assertRedirect(route('admin.santri.index'));
        $this->assertDatabaseHas('santris', [
            'id' => $santri->id,
            'name' => 'Santri CRUD Updated',
            'rfid_barcode' => 'RFID-CRUD-002',
            'classroom_id' => $updatedClassroom->id,
            'jenis_kelamin' => 'P',
        ]);
    }

    public function test_admin_cannot_reuse_another_santris_rfid(): void
    {
        $admin = Admin::create([
            'name' => 'Admin RFID',
            'username' => 'admin-rfid',
            'password' => 'password123',
        ]);
        $classroom = Classroom::create(['name' => 'Kelas RFID']);
        Santri::create([
            'name' => 'Existing RFID',
            'nis' => 'RFID001',
            'rfid_barcode' => 'RFID-SAME',
            'classroom_id' => $classroom->id,
            'jenis_kelamin' => 'L',
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->post(route('admin.santri.store'), [
            'name' => 'Duplicate RFID',
            'nis' => 'RFID002',
            'rfid_barcode' => 'RFID-SAME',
            'classroom_id' => $classroom->id,
            'jenis_kelamin' => 'P',
        ]);

        $response->assertSessionHasErrors('rfid_barcode');
    }

    public function test_deleting_santri_soft_deletes_only_the_santri_and_keeps_attendance(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Delete',
            'username' => 'admin-delete',
            'password' => 'password123',
        ]);
        $classroom = Classroom::create(['name' => 'Kelas Delete']);
        $santri = Santri::create([
            'name' => 'Santri Delete',
            'nis' => 'DELETE001',
            'rfid_barcode' => 'RFID-DELETE-001',
            'classroom_id' => $classroom->id,
            'jenis_kelamin' => 'L',
        ]);
        $attendance = Attendance::create([
            'santri_id' => $santri->id,
            'session' => 'Asar',
            'status' => 'Hadir',
            'date' => '2026-08-21',
            'notes' => 'Tap RFID',
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->delete(route('admin.santri.destroy', $santri));

        $response->assertRedirect(route('admin.santri.index'));
        $this->assertSoftDeleted('santris', ['id' => $santri->id]);
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'santri_id' => $santri->id,
        ]);
    }
}
