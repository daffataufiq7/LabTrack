@extends('layouts.app')

@section('title', 'Ajukan Peminjaman Alat Laboratorium')
@section('page_title', 'Formulir Pengajuan Sewa / Peminjaman Alat')
@section('page_subtitle', 'Tentukan periode sewa (dari tanggal berapa sampai tanggal berapa) dan pilih instrumen praktikum')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <form action="{{ route('mahasiswa.portal.ajukan.store') }}" method="POST" id="formPengajuan">
            @csrf

            <!-- Identitas Mahasiswa Pemohon -->
            <div class="card shadow-sm border-0 mb-4 rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-person-badge text-primary fs-5"></i>
                        <h6 class="mb-0 fw-bold text-dark">Data Mahasiswa Pemohon</h6>
                    </div>
                    <a href="{{ route('mahasiswa.portal.riwayat') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-clock-history me-1"></i> Riwayat & Status
                    </a>
                </div>
                <div class="card-body p-4 bg-light bg-opacity-50">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small text-muted">Nama Lengkap</label>
                            <input type="text" class="form-control bg-white fw-bold" value="{{ $mahasiswa->nama }}" readonly>
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label small text-muted">NIM</label>
                            <input type="text" class="form-control bg-white font-monospace" value="{{ $mahasiswa->nim }}" readonly>
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label small text-muted">Program Studi</label>
                            <input type="text" class="form-control bg-white" value="{{ $mahasiswa->program_studi }}" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Periode Waktu Sewa / Peminjaman (Dari Tanggal Berapa Sampai Berapa) -->
            <div class="card shadow-sm border-0 mb-4 rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-calendar-event text-primary me-2"></i>Periode Sewa / Peminjaman Alat Laboratorium
                    </h6>
                    <span class="badge bg-primary-subtle text-primary border border-primary px-3 py-1" id="badgeDurasiSewa">
                        Durasi Sewa: 7 Hari
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="tanggal_pinjam" class="form-label small fw-semibold text-dark">
                                Sewa Dari Tanggal (Mulai Pinjam) <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="tanggal_pinjam" id="tanggal_pinjam" 
                                   class="form-control @error('tanggal_pinjam') is-invalid @enderror" 
                                   value="{{ old('tanggal_pinjam', date('Y-m-d')) }}" 
                                   min="{{ date('Y-m-d') }}" required>
                            @error('tanggal_pinjam')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text" style="font-size: 0.75rem;">
                                Tanggal mulai pinjam tidak boleh di masa lampau (sebelum hari ini: <strong>{{ date('d/m/Y') }}</strong>).
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="tanggal_jatuh_tempo" class="form-label small fw-semibold text-dark">
                                Sampai Tanggal (Batas Pengembalian) <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="tanggal_jatuh_tempo" id="tanggal_jatuh_tempo" 
                                   class="form-control @error('tanggal_jatuh_tempo') is-invalid @enderror" 
                                   value="{{ old('tanggal_jatuh_tempo', date('Y-m-d', strtotime('+7 days'))) }}" 
                                   min="{{ date('Y-m-d') }}" required>
                            @error('tanggal_jatuh_tempo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text" style="font-size: 0.75rem;">
                                Tanggal pengembalian harus sama atau setelah tanggal mulai pinjam (Masa toleransi keterlambatan denda adalah 3 hari).
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pemilihan Alat (Multi-Item Dinamis) -->
            <div class="card shadow-sm border-0 mb-4 rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-box-seam text-primary me-2"></i>Daftar Alat Laboratorium yang Diajukan
                        </h6>
                        <small class="text-muted">Maksimal total kuantitas peminjaman adalah 10 unit dalam satu transaksi</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary shadow-sm" id="btnTambahAlat">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Alat Lain
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0" id="tabelItem">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 340px;">Pilih Instrumen / Alat Lab</th>
                                    <th style="width: 130px;" class="text-center">Kuantitas</th>
                                    <th style="width: 220px;" class="text-center">Status & Stok Ready</th>
                                    <th style="width: 70px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="containerBaris">
                                <!-- Baris Pertama -->
                                <tr class="baris-item">
                                    <td>
                                        <select name="items[0][alat_id]" class="form-select select-alat" required>
                                            <option value="">-- Pilih Alat Laboratorium --</option>
                                            @foreach($alat as $item)
                                                <option value="{{ $item->id }}" 
                                                        data-stok="{{ $item->stok_tersedia }}" 
                                                        data-rusak="{{ $item->stok_rusak }}"
                                                        data-kode="{{ $item->kode_alat }}"
                                                        {{ $item->stok_tersedia == 0 ? 'disabled' : '' }}
                                                        {{ (old('items.0.alat_id', request('alat_id')) == $item->id) ? 'selected' : '' }}>
                                                    [{{ $item->kode_alat }}] {{ $item->nama_alat }} — {{ $item->stok_tersedia > 0 ? "Stok Ready: {$item->stok_tersedia} Unit (Siap Pakai)" : "STOK HABIS (0 Unit)" }}{{ $item->stok_rusak > 0 ? " [{$item->stok_rusak} Unit Karantina Rusak]" : "" }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][jumlah]" class="form-control text-center input-jumlah" min="1" max="10" value="1" required>
                                    </td>
                                    <td class="text-center">
                                        <div class="badge-stok-container">
                                            <span class="badge bg-secondary-subtle text-secondary">-</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-hapus" disabled>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Petunjuk Keamanan Unit Fisik & Alur Verifikasi Admin -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-12 col-md-6 border-end">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-shield-check text-success fs-4"></i>
                                <div class="small">
                                    <strong class="text-dark d-block mb-1">Proteksi Kode Unit & Kalibrasi Aman</strong>
                                    <span class="text-muted">Setiap unit fisik memiliki kode barcode terpisah (misal: <code>ALT-ELK-01-U01</code>). Unit yang rusak otomatis dikarantina agar Anda hanya mendapatkan unit yang sehat & layak pakai.</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-clock-history text-primary fs-4"></i>
                                <div class="small">
                                    <strong class="text-dark d-block mb-1">Alur Persetujuan (ACC) Petugas</strong>
                                    <span class="text-muted">Setelah form dikirim, status adalah <span class="badge bg-warning text-dark">Menunggu ACC</span>. Stok fisik dipotong otomatis saat disetujui. Pantau riwayatnya di menu Riwayat.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-5">
                <a href="{{ route('mahasiswa.portal.riwayat') }}" class="btn btn-outline-secondary px-4">Batal</a>
                <button type="submit" class="btn btn-primary px-4 shadow-sm fw-semibold">
                    <i class="bi bi-send-check me-1"></i> Kirim Permohonan Peminjaman
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tglPinjam = document.getElementById('tanggal_pinjam');
    const tglTempo = document.getElementById('tanggal_jatuh_tempo');
    const badgeDurasi = document.getElementById('badgeDurasiSewa');

    function hitungDurasi() {
        if (tglPinjam.value && tglTempo.value) {
            const date1 = new Date(tglPinjam.value);
            const date2 = new Date(tglTempo.value);
            const diffTime = date2 - date1;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays >= 0) {
                badgeDurasi.textContent = `Durasi Sewa: ${diffDays} Hari`;
                badgeDurasi.className = 'badge bg-primary-subtle text-primary border border-primary px-3 py-1';
            } else {
                badgeDurasi.textContent = 'Tanggal Tidak Valid!';
                badgeDurasi.className = 'badge bg-danger-subtle text-danger border border-danger px-3 py-1';
            }
        }
    }

    // Sinkronisasi batas tanggal minimal
    tglPinjam.addEventListener('change', function () {
        // Tanggal kembali tidak boleh sebelum tanggal pinjam
        tglTempo.min = this.value;
        if (tglTempo.value < this.value) {
            tglTempo.value = this.value;
        }
        hitungDurasi();
    });

    tglTempo.addEventListener('change', function () {
        if (this.value < tglPinjam.value) {
            alert('Tanggal selesai sewa tidak boleh sebelum tanggal mulai sewa!');
            this.value = tglPinjam.value;
        }
        hitungDurasi();
    });

    hitungDurasi();

    // Logika pemilihan item alat
    let rowIndex = 1;
    const container = document.getElementById('containerBaris');
    const btnTambah = document.getElementById('btnTambahAlat');

    function updateStokBadge(row) {
        const select = row.querySelector('.select-alat');
        const badgeContainer = row.querySelector('.badge-stok-container');
        const inputJumlah = row.querySelector('.input-jumlah');
        const selectedOption = select.options[select.selectedIndex];

        if (selectedOption && selectedOption.dataset.stok !== undefined && selectedOption.value !== '') {
            const stok = parseInt(selectedOption.dataset.stok);
            const rusak = parseInt(selectedOption.dataset.rusak || '0');

            if (stok > 0) {
                badgeContainer.innerHTML = `
                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1 fw-bold">
                        <i class="bi bi-check-circle-fill me-1"></i>${stok} Unit Ready
                    </span>
                    ${rusak > 0 
                        ? `<div class="text-muted" style="font-size: 0.72rem;"><i class="bi bi-shield-check text-primary me-1"></i>${rusak} unit rusak dikarantina</div>` 
                        : `<div class="text-success" style="font-size: 0.72rem;"><i class="bi bi-patch-check me-1"></i>100% Layak Pakai</div>`}
                `;
                inputJumlah.max = stok;
                inputJumlah.disabled = false;
                if (parseInt(inputJumlah.value) > stok) {
                    inputJumlah.value = stok;
                }
            } else {
                badgeContainer.innerHTML = `
                    <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1 fw-bold">
                        <i class="bi bi-x-circle-fill me-1"></i>Habis (0 Ready)
                    </span>
                    <div class="text-danger" style="font-size: 0.72rem;">Sedang dipinjam/karantina</div>
                `;
                inputJumlah.max = 0;
                inputJumlah.value = 0;
                inputJumlah.disabled = true;
            }
        } else {
            badgeContainer.innerHTML = '<span class="badge bg-secondary-subtle text-secondary">-</span>';
            inputJumlah.max = 10;
            inputJumlah.disabled = false;
        }
    }

    const firstRow = container.querySelector('.baris-item');
    if (firstRow) {
        updateStokBadge(firstRow);
        firstRow.querySelector('.select-alat').addEventListener('change', function () {
            updateStokBadge(firstRow);
        });
    }

    btnTambah.addEventListener('click', function () {
        const rows = container.querySelectorAll('.baris-item');
        if (rows.length >= 5) {
            alert('Maksimal 5 jenis alat per transaksi.');
            return;
        }

        const templateRow = firstRow.cloneNode(true);
        const select = templateRow.querySelector('.select-alat');
        const input = templateRow.querySelector('.input-jumlah');
        const btnHapus = templateRow.querySelector('.btn-hapus');

        select.name = `items[${rowIndex}][alat_id]`;
        select.selectedIndex = 0;
        input.name = `items[${rowIndex}][jumlah]`;
        input.value = 1;
        input.disabled = false;
        btnHapus.disabled = false;

        templateRow.querySelector('.badge-stok-container').innerHTML = '<span class="badge bg-secondary-subtle text-secondary">-</span>';

        select.addEventListener('change', function () {
            updateStokBadge(templateRow);
        });

        btnHapus.addEventListener('click', function () {
            templateRow.remove();
        });

        container.appendChild(templateRow);
        rowIndex++;
    });
});
</script>
@endpush
