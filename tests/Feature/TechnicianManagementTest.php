<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;

class TechnicianManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_add_technician_and_receives_success_notification()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->post(route('admin.technicians.store'), [
            'name' => 'Fajar Pratama',
            'username' => 'fajar_tech',
            'phone' => '081299998888',
            'email' => 'fajar@ptdikta.co.id',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.technicians.index'));
        $response->assertSessionHas('success', 'Teknisi berhasil di tambahkan');

        $this->assertDatabaseHas('users', [
            'name' => 'Fajar Pratama',
            'username' => 'fajar_tech',
            'email' => 'fajar@ptdikta.co.id',
            'role' => 'teknisi',
            'status' => 'active',
        ]);

        // Kunjungi halaman index untuk memastikan notifikasi tampil di layar
        $indexResponse = $this->actingAs($admin)->get(route('admin.technicians.index'));
        $indexResponse->assertSee('Teknisi berhasil di tambahkan');
    }

    public function test_admin_can_add_technician_without_optional_email()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->post(route('admin.technicians.store'), [
            'name' => 'Doni Saputra',
            'username' => 'doni_tech',
            'phone' => '081277776666',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('admin.technicians.index'));
        $response->assertSessionHas('success', 'Teknisi berhasil di tambahkan');

        $this->assertDatabaseHas('users', [
            'name' => 'Doni Saputra',
            'username' => 'doni_tech',
            'email' => null,
            'role' => 'teknisi',
        ]);
    }

    public function test_technician_creation_fails_if_username_already_taken()
    {
        $admin = User::where('role', 'admin')->first();

        // Coba mendaftarkan teknisi dengan username 'budi' yang sudah ada
        $response = $this->actingAs($admin)->post(route('admin.technicians.store'), [
            'name' => 'Budi Duplikat',
            'username' => 'budi',
            'phone' => '081200001111',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertSessionHasErrors('username');
    }

    public function test_technician_creation_fails_if_password_confirmation_mismatches()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->post(route('admin.technicians.store'), [
            'name' => 'Teknisi Baru',
            'username' => 'teknisibaru',
            'phone' => '081200001111',
            'password' => 'secret123',
            'password_confirmation' => 'berbeda123',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
