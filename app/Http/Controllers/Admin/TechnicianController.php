<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TechnicianController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'teknisi')
            ->with([
                'carriedDevices' => function ($q) {
                    $q->latest();
                },
            ])
            ->withCount([
                'carriedDevices',
            ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $technicians = $query->paginate(10)->withQueryString();

        return view('admin.technicians.index', compact('technicians'));
    }

    public function create()
    {
        return view('admin.technicians.create');
    }

    public function store(Request $request)
    {
        if ($request->has('username')) {
            $request->merge(['username' => strtolower(trim(str_replace(' ', '', (string)$request->username)))]);
        }
        if ($request->has('email') && empty(trim((string)$request->email))) {
            $request->merge(['email' => null]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'nullable|email|max:100|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required' => 'Nama lengkap teknisi wajib diisi.',
            'username.required' => 'Username login wajib diisi.',
            'username.unique' => 'Username ini sudah terdaftar/digunakan oleh akun lain. Silakan pilih username lain.',
            'email.email' => 'Format penulisan email tidak valid.',
            'email.unique' => 'Alamat email ini sudah terdaftar pada akun lain.',
            'phone.required' => 'Nomor HP / WhatsApp wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok dengan password yang dimasukkan.',
        ]);

        $validated['role'] = 'teknisi';
        $validated['status'] = 'active';
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.technicians.index')->with('success', 'Teknisi berhasil di tambahkan');
    }

    public function edit(User $technician)
    {
        if ($technician->role !== 'teknisi') {
            abort(404);
        }

        return view('admin.technicians.edit', compact('technician'));
    }

    public function update(Request $request, User $technician)
    {
        if ($technician->role !== 'teknisi') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($technician->id)],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('users')->ignore($technician->id)],
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
            'status' => 'required|in:active,inactive',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $technician->update($validated);

        return redirect()->route('admin.technicians.index')->with('success', 'Data teknisi berhasil diperbarui.');
    }

    public function toggleStatus(User $technician)
    {
        if ($technician->role !== 'teknisi') {
            abort(404);
        }

        $technician->status = ($technician->status === 'active') ? 'inactive' : 'active';
        $technician->save();

        $statusText = $technician->status === 'active' ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun teknisi {$technician->name} berhasil {$statusText}.");
    }

    public function destroy(User $technician)
    {
        if ($technician->role !== 'teknisi') {
            abort(404);
        }

        if ($technician->carriedDevices()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus teknisi karena masih membawa perangkat perusahaan!');
        }

        $technician->delete();

        return redirect()->route('admin.technicians.index')->with('success', 'Data teknisi berhasil dihapus.');
    }
}
