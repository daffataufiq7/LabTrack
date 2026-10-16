<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AlatController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $kategoriFilter = $request->input('kategori');
        $kondisiFilter = $request->input('kondisi');

        $query = Alat::with('units')->withCount('detailPeminjaman');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_alat', 'like', "%{$search}%")
                  ->orWhere('nama_alat', 'like', "%{$search}%")
                  ->orWhere('kategori', 'like', "%{$search}%");
            });
        }

        if ($kategoriFilter) {
            $query->where('kategori', $kategoriFilter);
        }

        if ($kondisiFilter) {
            $query->where('kondisi', $kondisiFilter);
        }

        $alat = $query->orderBy('nama_alat', 'asc')->paginate(10)->withQueryString();

        $daftarKategori = Alat::select('kategori')->distinct()->pluck('kategori');
        $daftarKondisi = ['Baik', 'Perlu Perawatan', 'Rusak Ringan', 'Rusak Berat'];

        return view('alat.index', compact('alat', 'search', 'kategoriFilter', 'kondisiFilter', 'daftarKategori', 'daftarKondisi'));
    }

    public function create()
    {
        $kategoriSuggestions = Alat::select('kategori')->distinct()->pluck('kategori');
        return view('alat.create', compact('kategoriSuggestions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_alat' => ['required', 'string', 'max:30', 'unique:alat,kode_alat'],
            'nama_alat' => ['required', 'string', 'max:150'],
            'kategori' => ['required', 'string', 'max:100'],
            'gambar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'stok_total' => ['required', 'integer', 'min:0'],
            'stok_tersedia' => ['required', 'integer', 'min:0', 'lte:stok_total'],
            'stok_rusak' => ['nullable', 'integer', 'min:0'],
            'kondisi' => ['required', 'string', 'max:50'],
        ], [
            'kode_alat.required' => 'Kode alat wajib diisi.',
            'kode_alat.unique' => 'Kode alat sudah digunakan.',
            'nama_alat.required' => 'Nama alat wajib diisi.',
            'kategori.required' => 'Kategori alat wajib diisi.',
            'gambar.image' => 'File harus berupa gambar (JPG, PNG, atau WEBP).',
            'gambar.max' => 'Ukuran gambar maksimal 3MB.',
            'stok_total.required' => 'Stok total wajib diisi.',
            'stok_total.min' => 'Stok total tidak boleh bernilai negatif.',
            'stok_tersedia.required' => 'Stok tersedia wajib diisi.',
            'stok_tersedia.lte' => 'Stok tersedia tidak boleh melebihi stok total.',
            'kondisi.required' => 'Kondisi fisik alat wajib diisi.',
        ]);

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            $file->move(public_path('images/alat'), $filename);
            $validated['gambar'] = 'images/alat/' . $filename;
        }

        $validated['stok_rusak'] = $validated['stok_rusak'] ?? 0;
        Alat::create($validated);

        return redirect()->route('alat.index')
            ->with('success', 'Data alat laboratorium berhasil ditambahkan.');
    }

    public function edit(Alat $alat)
    {
        $kategoriSuggestions = Alat::select('kategori')->distinct()->pluck('kategori');
        return view('alat.edit', compact('alat', 'kategoriSuggestions'));
    }

    public function update(Request $request, Alat $alat)
    {
        $validated = $request->validate([
            'kode_alat' => ['required', 'string', 'max:30', Rule::unique('alat', 'kode_alat')->ignore($alat->id)],
            'nama_alat' => ['required', 'string', 'max:150'],
            'kategori' => ['required', 'string', 'max:100'],
            'gambar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'stok_total' => ['required', 'integer', 'min:0'],
            'stok_tersedia' => ['required', 'integer', 'min:0', 'lte:stok_total'],
            'stok_rusak' => ['nullable', 'integer', 'min:0'],
            'kondisi' => ['required', 'string', 'max:50'],
        ], [
            'kode_alat.required' => 'Kode alat wajib diisi.',
            'kode_alat.unique' => 'Kode alat sudah digunakan alat lain.',
            'nama_alat.required' => 'Nama alat wajib diisi.',
            'kategori.required' => 'Kategori alat wajib diisi.',
            'gambar.image' => 'File harus berupa gambar (JPG, PNG, atau WEBP).',
            'gambar.max' => 'Ukuran gambar maksimal 3MB.',
            'stok_total.required' => 'Stok total wajib diisi.',
            'stok_total.min' => 'Stok total tidak boleh bernilai negatif.',
            'stok_tersedia.required' => 'Stok tersedia wajib diisi.',
            'stok_tersedia.lte' => 'Stok tersedia tidak boleh melebihi stok total.',
            'kondisi.required' => 'Kondisi alat wajib diisi.',
        ]);

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            $file->move(public_path('images/alat'), $filename);

            if ($alat->gambar && file_exists(public_path($alat->gambar)) && !str_contains($alat->gambar, 'default_')) {
                @unlink(public_path($alat->gambar));
            }

            $validated['gambar'] = 'images/alat/' . $filename;
        }

        $validated['stok_rusak'] = $validated['stok_rusak'] ?? 0;
        $alat->update($validated);

        return redirect()->route('alat.index')
            ->with('success', 'Data alat laboratorium berhasil diperbarui.');
    }

    public function destroy(Alat $alat)
    {
        // Pencegahan penghapusan jika alat sudah pernah tercatat dalam transaksi
        if ($alat->hasTransactions()) {
            return redirect()->route('alat.index')
                ->with('error', "Alat '{$alat->nama_alat}' tidak dapat dihapus karena sudah tercatat dalam riwayat transaksi laboratorium.");
        }

        $alat->delete();

        return redirect()->route('alat.index')
            ->with('success', 'Data alat berhasil dihapus.');
    }
}
