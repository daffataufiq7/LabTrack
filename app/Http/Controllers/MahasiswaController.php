<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $prodiFilter = $request->input('program_studi');

        $query = Mahasiswa::withCount('peminjaman');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nim', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%")
                  ->orWhere('program_studi', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        if ($prodiFilter) {
            $query->where('program_studi', $prodiFilter);
        }

        $mahasiswa = $query->orderBy('nama', 'asc')->paginate(10)->withQueryString();

        $daftarProdi = Mahasiswa::select('program_studi')->distinct()->pluck('program_studi');

        return view('mahasiswa.index', compact('mahasiswa', 'search', 'prodiFilter', 'daftarProdi'));
    }

    public function create()
    {
        return view('mahasiswa.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nim' => ['required', 'string', 'max:30', 'unique:mahasiswa,nim'],
            'nama' => ['required', 'string', 'max:100'],
            'program_studi' => ['required', 'string', 'max:100'],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ], [
            'nim.required' => 'NIM wajib diisi.',
            'nim.unique' => 'NIM ini sudah terdaftar dalam sistem.',
            'nama.required' => 'Nama mahasiswa wajib diisi.',
            'program_studi.required' => 'Program studi wajib diisi.',
        ]);

        Mahasiswa::create($validated);

        return redirect()->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil ditambahkan.');
    }

    public function edit(Mahasiswa $mahasiswa)
    {
        return view('mahasiswa.edit', compact('mahasiswa'));
    }

    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $validated = $request->validate([
            'nim' => ['required', 'string', 'max:30', Rule::unique('mahasiswa', 'nim')->ignore($mahasiswa->id)],
            'nama' => ['required', 'string', 'max:100'],
            'program_studi' => ['required', 'string', 'max:100'],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ], [
            'nim.required' => 'NIM wajib diisi.',
            'nim.unique' => 'NIM ini sudah digunakan oleh mahasiswa lain.',
            'nama.required' => 'Nama mahasiswa wajib diisi.',
            'program_studi.required' => 'Program studi wajib diisi.',
        ]);

        $mahasiswa->update($validated);

        return redirect()->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    public function destroy(Mahasiswa $mahasiswa)
    {
        // Pencegahan penghapusan data master yang sudah memiliki riwayat transaksi
        if ($mahasiswa->hasPeminjaman()) {
            return redirect()->route('mahasiswa.index')
                ->with('error', "Mahasiswa '{$mahasiswa->nama}' tidak dapat dihapus karena memiliki riwayat transaksi peminjaman alat laboratorium.");
        }

        $mahasiswa->delete();

        return redirect()->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil dihapus.');
    }
}
