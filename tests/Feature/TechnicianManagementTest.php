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

    public function test_newly_created_technician_can_login_with_uppercase_username()
    {
        $admin = User::where('role', 'admin')->first();

        // 1. Admin creates new technician
        $this->actingAs($admin)->post(route('admin.technicians.store'), [
            'name' => 'Bayu Wicaksono',
            'username' => 'bayu_wicak',
            'phone' => '081311223344',
            'email' => 'bayu@ptdikta.co.id',
            'password' => 'bayu12345',
            'password_confirmation' => 'bayu12345',
        ]);

        // Logout admin
        auth()->logout();

        // 2. Technician logs in with uppercase input (e.g. from smartphone auto-capitalization: Bayu_Wicak)
        $loginResponse = $this->post(route('login.post'), [
            'username' => 'Bayu_Wicak',
            'password' => 'bayu12345',
        ]);

        $loginResponse->assertRedirect(route('technician.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('bayu_wicak', auth()->user()->username);

        // 3. Access technician dashboard successfully
        $dashResponse = $this->get(route('technician.dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Bayu Wicaksono');
    }

    public function test_technician_can_login_using_phone_number()
    {
        $admin = User::where('role', 'admin')->first();

        $this->actingAs($admin)->post(route('admin.technicians.store'), [
            'name' => 'Surya Saputra',
            'username' => 'dtsurya',
            'phone' => '081298765432',
            'password' => 'surya12345',
            'password_confirmation' => 'surya12345',
        ]);

        auth()->logout();

        // Login using phone number instead of username
        $loginResponse = $this->post(route('login.post'), [
            'username' => '081298765432',
            'password' => 'surya12345',
        ]);

        $loginResponse->assertRedirect(route('technician.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('dtsurya', auth()->user()->username);

        // Also test logging in without 'dt' prefix (input: 'surya')
        auth()->logout();
        $loginNoDt = $this->post(route('login.post'), [
            'username' => 'surya',
            'password' => 'surya12345',
        ]);
        $loginNoDt->assertRedirect(route('technician.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('dtsurya', auth()->user()->username);
    }

    public function test_technician_can_login_using_email_with_mixed_case()
    {
        $admin = User::where('role', 'admin')->first();

        $this->actingAs($admin)->post(route('admin.technicians.store'), [
            'name' => 'Chandra Wijaya',
            'username' => 'chandra_w',
            'phone' => '081244556677',
            'email' => 'chandra@ptdikta.co.id',
            'password' => 'chandra123',
            'password_confirmation' => 'chandra123',
        ]);

        auth()->logout();

        // Login using email with mixed case
        $loginResponse = $this->post(route('login.post'), [
            'username' => 'Chandra@PTDikta.Co.Id',
            'password' => 'chandra123',
        ]);

        $loginResponse->assertRedirect(route('technician.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('chandra_w', auth()->user()->username);
    }

    public function test_inactive_technician_cannot_login()
    {
        $tech = User::where('username', 'budi')->first();
        $tech->update(['status' => 'inactive']);

        $response = $this->post(route('login.post'), [
            'username' => 'budi',
            'password' => 'dikta123',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_technician_can_login_with_or_without_dt_prefix_and_access_dashboard()
    {
        $tech = User::create([
            'name' => 'Fahmi Hidayat',
            'username' => 'dtfahmi',
            'phone' => '081617599861',
            'email' => 'fahmi@ptdikta.co.id',
            'password' => \Illuminate\Support\Facades\Hash::make('dikta123'),
            'role' => 'teknisi',
            'status' => 'active',
        ]);

        // 1. Login with prefix uppercase 'DTfahmi' and password 'dikta123'
        $resWithDt = $this->post(route('login.post'), [
            'username' => 'DTfahmi',
            'password' => 'dikta123',
        ]);
        $resWithDt->assertRedirect(route('technician.dashboard'));
        $this->assertAuthenticated();

        $dashRes = $this->get(route('technician.dashboard'));
        $dashRes->assertStatus(200);
        $dashRes->assertSee('Fahmi Hidayat');

        auth()->logout();

        // 2. Login without prefix 'dt' (just 'fahmi')
        $resNoDt = $this->post(route('login.post'), [
            'username' => 'fahmi',
            'password' => 'dikta123',
        ]);
        $resNoDt->assertRedirect(route('technician.dashboard'));
        $this->assertAuthenticated();

        auth()->logout();

        // 3. Login with phone number
        $resPhone = $this->post(route('login.post'), [
            'username' => '081617599861',
            'password' => 'dikta123',
        ]);
        $resPhone->assertRedirect(route('technician.dashboard'));
        $this->assertAuthenticated();

        auth()->logout();

        // 4. Test user 'dtaji' login with 'DTaji' and password 'dikta123'
        $aji = User::create([
            'name' => 'Aji',
            'username' => 'dtaji',
            'phone' => '081299998888',
            'password' => \Illuminate\Support\Facades\Hash::make('dikta123'),
            'role' => 'teknisi',
            'status' => 'active',
        ]);
        $resAji = $this->post(route('login.post'), [
            'username' => 'DTaji',
            'password' => 'dikta123',
        ]);
        $resAji->assertRedirect(route('technician.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('dtaji', auth()->user()->username);
    }
}
