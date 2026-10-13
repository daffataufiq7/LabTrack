@extends('layouts.app')

@section('title', 'Peminjaman Baru')
@section('page_title', 'Form Peminjaman Alat Laboratorium')
@section('page_subtitle', 'Pencatatan sirkulasi peminjaman multi-item dengan verifikasi ketersediaan stok')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <form action="{{ route('peminjaman.store') }}" method="POST" id="formPeminjaman">
            @csrf

            <!-- Card Informasi Peminjaman & Peminjam -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-person-badge-fill text-primary me-2"></i>1. Identitas Peminjam & Periode Peminjaman
                    </h6>
                    <a href="{{ route('peminjaman.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <!-- Pilih Mahasiswa -->
                        <div class="col-12 col-md-6">
                            <label for="mahasiswa_id" class="form-label">Mahasiswa Peminjam <span class="text-danger">*</span></label>
                            <select name="mahasiswa_id" id="mahasiswa_id" class="form-select @error('mahasiswa_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Mahasiswa --</option>
                                @foreach($mahasiswa as $mhs)
                                    <option value="{{ $mhs->id }}" {{ old('mahasiswa_id') == $mhs->id ? 'selected' : '' }}>
                                        {{ $mhs->nim }} — {{ $mhs->nama }} ({{ $mhs->program_studi }})
                                    </option>
                                @endforeach
                            </select>
                            @error('mahasiswa_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Pilih mahasiswa yang terdaftar dalam master data.</div>
                        </div>

                        <!-- Info Petugas / Kode Transaksi -->
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted">Nomor Kode Transaksi</label>
                            <input type="text" class="form-control bg-light font-monospace" value="[Otomatis Digenerate Sistem]" readonly>
                            <div class="form-text">Format: TRX-YYYYMMDD-XXXX (unik dan terindeks).</div>
                        </div>

                        <!-- Tanggal Pinjam -->
                        <div class="col-12 col-md-6">
                            <label for="tanggal_pinjam" class="form-label">Tanggal Pinjam <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_pinjam" id="tanggal_pinjam" class="form-control @error('tanggal_pinjam') is-invalid @enderror" value="{{ old('tanggal_pinjam', date('Y-m-d')) }}" required>
                            @error('tanggal_pinjam')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tanggal Jatuh Tempo -->
                        <div class="col-12 col-md-6">
                            <label for="tanggal_jatuh_tempo" class="form-label">Tanggal Jatuh Tempo (Batas Pengembalian) <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_jatuh_tempo" id="tanggal_jatuh_tempo" class="form-control @error('tanggal_jatuh_tempo') is-invalid @enderror" value="{{ old('tanggal_jatuh_tempo', date('Y-m-d', strtotime('+7 days'))) }}" required>
                            @error('tanggal_jatuh_tempo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Default 7 hari masa peminjaman. Tidak boleh sebelum tanggal pinjam.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Multi-item Alat yang Dipinjam -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-box-seam-fill text-primary me-2"></i>2. Daftar Alat Laboratorium yang Dipinjam
                        </h6>
                        <small class="text-muted">Satu transaksi dapat memuat beberapa item alat sekaligus</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnTambahBaris">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Item Alat
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0" id="tabelItems">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 320px;">Pilih Alat Lab (Ketersediaan Stok)</th>
                                    <th style="width: 160px;" class="text-center">Jumlah Pinjam</th>
                                    <th style="width: 140px;" class="text-center">Stok Sisa</th>
                                    <th style="width: 70px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="containerBarisItem">
                                <!-- Baris Pertama Default -->
                                <tr class="baris-item" data-index="0">
                                    <td>
                                        <select name="items[0][alat_id]" class="form-select select-alat" required>
                                            <option value="" data-stok="0">-- Pilih Alat Lab --</option>
                                            @foreach($alat as $item)
                                                <option value="{{ $item->id }}" data-stok="{{ $item->stok_tersedia }}">
                                                    {{ $item->kode_alat }} — {{ $item->nama_alat }} (Tersedia: {{ $item->stok_tersedia }} unit)
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][jumlah]" class="form-control text-center input-jumlah" min="1" value="1" required>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border badge-stok-sisa px-2 py-1">-</span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-baris" title="Hapus Baris" disabled>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light p-3 d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        <i class="bi bi-info-circle me-1"></i>Stok tersedia akan otomatis dikunci dan dikurangi secara atomik dalam database transaction.
                    </div>
                    <div>
                        <strong class="text-dark">Total Item: </strong>
                        <span id="labelTotalItem" class="badge bg-primary fs-6">1 jenis</span>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi Simpan -->
            <div class="card shadow-sm border-0 bg-transparent">
                <div class="d-flex align-items-center justify-content-end gap-2">
                    <a href="{{ route('peminjaman.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-4 py-2 shadow-sm" id="btnSubmit">
                        <i class="bi bi-check-circle-fill me-1"></i> Simpan Transaksi Peminjaman
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('containerBarisItem');
    const btnTambah = document.getElementById('btnTambahBaris');
    const labelTotal = document.getElementById('labelTotalItem');
    const tglPinjam = document.getElementById('tanggal_pinjam');
    const tglTempo = document.getElementById('tanggal_jatuh_tempo');

    let rowIndex = 1;

    // Sinkronisasi batas tanggal jatuh tempo >= tanggal pinjam
    tglPinjam.addEventListener('change', function () {
        if (tglTempo.value < this.value) {
            tglTempo.value = this.value;
        }
        tglTempo.min = this.value;
    });

    function updateStockDisplay(row) {
        const select = row.querySelector('.select-alat');
        const inputJumlah = row.querySelector('.input-jumlah');
        const badgeSisa = row.querySelector('.badge-stok-sisa');

        const selectedOption = select.options[select.selectedIndex];
        const stokTersedia = parseInt(selectedOption?.dataset?.stok || 0);
        const jumlahMinta = parseInt(inputJumlah.value || 0);

        if (!select.value) {
            badgeSisa.className = 'badge bg-light text-dark border badge-stok-sisa px-2 py-1';
            badgeSisa.textContent = '-';
            inputJumlah.max = 999;
            return;
        }

        inputJumlah.max = stokTersedia;

        const sisa = stokTersedia - jumlahMinta;
        if (sisa < 0) {
            badgeSisa.className = 'badge bg-danger badge-stok-sisa px-2 py-1';
            badgeSisa.textContent = 'Kurang ' + Math.abs(sisa);
            inputJumlah.classList.add('is-invalid');
        } else {
            badgeSisa.className = 'badge bg-success-subtle text-success border border-success border-opacity-25 badge-stok-sisa px-2 py-1';
            badgeSisa.textContent = 'Sisa ' + sisa;
            inputJumlah.classList.remove('is-invalid');
        }
    }

    function updateTotalCount() {
        const rows = container.querySelectorAll('.baris-item');
        labelTotal.textContent = rows.length + ' jenis';

        // Aktifkan tombol hapus hanya jika baris lebih dari 1
        rows.forEach(r => {
            const btnHapus = r.querySelector('.btn-hapus-baris');
            if (btnHapus) btnHapus.disabled = (rows.length === 1);
        });
    }

    function bindRowEvents(row) {
        const select = row.querySelector('.select-alat');
        const inputJumlah = row.querySelector('.input-jumlah');
        const btnHapus = row.querySelector('.btn-hapus-baris');

        select.addEventListener('change', function () {
            // Cek duplikasi alat yang sama
            const currentVal = this.value;
            if (currentVal) {
                let duplicateCount = 0;
                container.querySelectorAll('.select-alat').forEach(s => {
                    if (s.value === currentVal) duplicateCount++;
                });

                if (duplicateCount > 1) {
                    alert('Alat ini sudah dipilih di baris lain. Harap pilih alat lain atau sesuaikan jumlahnya.');
                    this.value = '';
                }
            }
            updateStockDisplay(row);
        });

        inputJumlah.addEventListener('input', function () {
            updateStockDisplay(row);
        });

        btnHapus.addEventListener('click', function () {
            if (container.querySelectorAll('.baris-item').length > 1) {
                row.remove();
                updateTotalCount();
            }
        });
    }

    // Bind baris default awal
    container.querySelectorAll('.baris-item').forEach(bindRowEvents);

    // Tambah Baris Baru
    btnTambah.addEventListener('click', function () {
        const firstRow = container.querySelector('.baris-item');
        const selectOptions = firstRow.querySelector('.select-alat').innerHTML;

        const newRow = document.createElement('tr');
        newRow.className = 'baris-item';
        newRow.dataset.index = rowIndex;
        newRow.innerHTML = `
            <td>
                <select name="items[${rowIndex}][alat_id]" class="form-select select-alat" required>
                    ${selectOptions}
                </select>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][jumlah]" class="form-control text-center input-jumlah" min="1" value="1" required>
            </td>
            <td class="text-center">
                <span class="badge bg-light text-dark border badge-stok-sisa px-2 py-1">-</span>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-baris" title="Hapus Baris">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        // Reset value pilihan select
        newRow.querySelector('.select-alat').value = '';
        container.appendChild(newRow);
        bindRowEvents(newRow);
        updateTotalCount();
        rowIndex++;
    });

    // Validasi submit form
    document.getElementById('formPeminjaman').addEventListener('submit', function (e) {
        let hasError = false;
        container.querySelectorAll('.baris-item').forEach(r => {
            const select = r.querySelector('.select-alat');
            const inputJumlah = r.querySelector('.input-jumlah');
            const selectedOption = select.options[select.selectedIndex];
            const stokTersedia = parseInt(selectedOption?.dataset?.stok || 0);
            const jumlahMinta = parseInt(inputJumlah.value || 0);

            if (!select.value) {
                alert('Silakan pilih alat pada setiap baris item.');
                hasError = true;
                return;
            }

            if (jumlahMinta > stokTersedia) {
                alert('Jumlah pinjam melebihi stok yang tersedia!');
                hasError = true;
                return;
            }
        });

        if (hasError) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
