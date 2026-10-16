@extends('layouts.app')

@section('title', 'Detail Transaksi ' . $peminjaman->kode_transaksi)
@section('page_title', 'Detail Transaksi Peminjaman')
@section('page_subtitle', 'Informasi lengkap sirkulasi alat, status jatuh tempo, rincian item, dan denda')

@section('content')
<div class="row g-4">
    <!-- Kolom Kiri: Rincian Peminjaman & Item -->
    <div class="col-12 col-lg-8">
        <!-- Peringatan Keterlambatan Aktif -->
        @if($peminjaman->status === 'dipinjam' && $peminjaman->isTerlambat())
            @php
                $hariTerlambat = $peminjaman->hitungHariTerlambat();
                $hariKenaDenda = $peminjaman->hitungHariKenaDenda();
                $estimasiDenda = $peminjaman->hitungDenda();
            @endphp
            <div class="alert alert-danger d-flex align-items-center justify-content-between p-3 rounded-4 shadow-sm mb-4 border-danger border-opacity-50" role="alert">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box bg-danger bg-opacity-20 text-danger rounded-circle">
                        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                    </div>
                    <div>
                        <h6 class="alert-heading mb-1 fw-bold text-danger">Transaksi Ini Sedang Melewati Jatuh Tempo!</h6>
                        <p class="mb-0 small text-danger-emphasis">
                            Terlambat <strong>{{ $hariTerlambat }} hari</strong> (Masa tenggang 3 hari). 
                            Hari dikenai denda: <strong>{{ $hariKenaDenda }} hari</strong>.
                            Estimasi denda saat ini: <strong>Rp {{ number_format($estimasiDenda, 0, ',', '.') }}</strong>.
                        </p>
                    </div>
                </div>
                <a href="{{ route('pengembalian.form', $peminjaman->id) }}" class="btn btn-sm btn-danger shadow-sm text-nowrap">
                    Proses Pengembalian <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        @endif

        <!-- Peringatan Menunggu ACC -->
        @if($peminjaman->status === 'menunggu_acc')
            <div class="alert alert-warning d-flex align-items-center justify-content-between p-3 rounded-4 shadow-sm mb-4 border-warning border-opacity-50" role="alert">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box bg-warning bg-opacity-20 text-warning-emphasis rounded-circle p-2">
                        <i class="bi bi-clock-history fs-3"></i>
                    </div>
                    <div>
                        <h6 class="alert-heading mb-1 fw-bold text-dark">Permohonan Ini Sedang Menunggu Verifikasi (ACC) Anda</h6>
                        <p class="mb-0 small text-muted">
                            Mahasiswa telah mengajukan peminjaman. Silakan setujui (ACC) untuk memotong stok fisik dan alokasi nomor unit, atau tolak permohonan.
                        </p>
                    </div>
                </div>
                <div class="d-flex gap-2 text-nowrap">
                    <button type="button" class="btn btn-sm btn-success shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#modalAccDetail">
                        <i class="bi bi-check-lg me-1"></i> ACC
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#modalRejectDetail">
                        <i class="bi bi-x-lg me-1"></i> Tolak
                    </button>
                </div>
            </div>
        @endif

        @if($peminjaman->status === 'ditolak')
            <div class="alert alert-danger p-3 rounded-4 shadow-sm mb-4 border-danger border-opacity-50" role="alert">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-x-circle-fill text-danger fs-5"></i>
                    <h6 class="alert-heading mb-0 fw-bold text-danger">Permohonan Peminjaman Ditolak</h6>
                </div>
                <p class="mb-0 small text-danger-emphasis">
                    Alasan Penolakan: <em>"{{ $peminjaman->catatan_admin ?: 'Tidak memenuhi syarat peminjaman.' }}"</em>
                </p>
            </div>
        @endif
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-6 fw-bold font-monospace text-primary">{{ $peminjaman->kode_transaksi }}</span>
                    @if($peminjaman->status === 'menunggu_acc')
                        <span class="badge bg-warning text-dark badge-status"><i class="bi bi-clock-history me-1"></i> Menunggu ACC</span>
                    @elseif($peminjaman->status === 'dipinjam')
                        @if($peminjaman->isTerlambat())
                            <span class="badge bg-danger badge-status">Terlambat (Aktif)</span>
                        @else
                            <span class="badge bg-info text-white badge-status">Sedang Dipinjam</span>
                        @endif
                    @elseif($peminjaman->status === 'dikembalikan')
                        <span class="badge bg-success badge-status">Sudah Dikembalikan</span>
                    @elseif($peminjaman->status === 'ditolak')
                        <span class="badge bg-danger badge-status">Ditolak</span>
                    @else
                        <span class="badge bg-secondary badge-status">Dibatalkan</span>
                    @endif
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('peminjaman.nota', $peminjaman->id) }}" target="_blank" class="btn btn-sm btn-outline-primary shadow-sm">
                        <i class="bi bi-printer me-1"></i> Cetak Struk / Nota
                    </a>
                    <a href="{{ route('peminjaman.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Tanggal Pinjam</small>
                        <div class="fw-bold text-dark mt-1">{{ $peminjaman->tanggal_pinjam->translatedFormat('d F Y') }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Tanggal Jatuh Tempo</small>
                        <div class="fw-bold mt-1 {{ $peminjaman->isTerlambat() ? 'text-danger' : 'text-dark' }}">
                            {{ $peminjaman->tanggal_jatuh_tempo->translatedFormat('d F Y') }}
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Tanggal Kembali</small>
                        <div class="fw-bold text-dark mt-1">
                            {{ $peminjaman->tanggal_kembali ? $peminjaman->tanggal_kembali->translatedFormat('d F Y') : '-' }}
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Dicatat Pada</small>
                        <div class="text-muted mt-1" style="font-size: 0.85rem;">{{ $peminjaman->created_at->format('d/m/Y H:i') }} WIB</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Tabel Rincian Alat -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-tools text-primary me-2"></i>Daftar Alat Laboratorium Dipinjam
                    </h6>
                    <small class="text-muted">Total {{ $peminjaman->detailPeminjaman->sum('jumlah') }} unit alat tercatat</small>
                </div>
                <span class="badge bg-light text-dark border">
                    {{ $peminjaman->detailPeminjaman->count() }} Jenis Alat
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Kode & Nama Alat</th>
                                <th>Kategori</th>
                                <th class="text-center" style="width: 100px;">Jumlah</th>
                                <th>Kondisi Pengembalian</th>
                                <th class="text-end" style="width: 140px;">Biaya Kerusakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($peminjaman->detailPeminjaman as $idx => $detail)
                                <tr>
                                    <td class="text-muted">{{ $idx + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $detail->alat->nama_alat }}</div>
                                        <div class="small text-muted font-monospace">{{ $detail->alat->kode_alat }}</div>
                                        @if($detail->kode_unit_dipinjam)
                                            <div class="mt-1 d-flex flex-wrap gap-1 align-items-center">
                                                <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.7rem;">
                                                    <i class="bi bi-upc-scan text-primary me-1"></i>Unit Fisik:
                                                </span>
                                                @foreach($detail->unit_list as $unitCode)
                                                    <span class="badge bg-primary-subtle text-primary border border-primary font-monospace" style="font-size: 0.72rem;">
                                                        {{ $unitCode }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                            {{ $detail->alat->kategori }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary px-3 py-1 rounded-pill">
                                            {{ $detail->jumlah }} Unit
                                        </span>
                                    </td>
                                    <td>
                                        @if($detail->kondisi_kembali)
                                            <span class="badge {{ str_contains($detail->kondisi_kembali, 'Rusak') ? 'bg-danger-subtle text-danger border border-danger' : 'bg-success-subtle text-success border border-success' }} px-2 py-1">
                                                {{ $detail->kondisi_kembali }}
                                            </span>
                                        @else
                                            <span class="badge bg-light text-muted border">Belum Dikembalikan</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($detail->biaya_perbaikan > 0)
                                            <strong class="text-danger">Rp {{ number_format($detail->biaya_perbaikan, 0, ',', '.') }}</strong>
                                        @else
                                            <span class="text-muted small">Rp 0</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Info Peminjam & Rincian Finansial -->
    <div class="col-12 col-lg-4">
        <!-- Card Profil Peminjam -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-person-circle text-primary me-2"></i>Identitas Peminjam
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="text-center mb-3">
                    <div class="avatar-lg rounded-circle bg-primary bg-opacity-10 text-primary mx-auto d-flex align-items-center justify-content-center mb-2" style="width: 56px; height: 56px;">
                        <i class="bi bi-person fs-3"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-0">{{ $peminjaman->mahasiswa->nama }}</h6>
                    <small class="text-muted font-monospace">NIM: {{ $peminjaman->mahasiswa->nim }}</small>
                </div>
                <hr class="text-muted opacity-25">
                <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Program Studi:</span>
                        <span class="fw-semibold text-dark">{{ $peminjaman->mahasiswa->program_studi }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Nomor WhatsApp:</span>
                        <span class="fw-semibold text-dark">{{ $peminjaman->mahasiswa->no_hp ?: '-' }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Card Rekapitulasi Tagihan / Denda & Perbaikan -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-receipt text-primary me-2"></i>Rincian Biaya & Denda
                </h6>
            </div>
            <div class="card-body p-4">
                <ul class="list-unstyled mb-3 d-flex flex-column gap-2">
                    <li class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Denda Keterlambatan:</span><br>
                            <small class="text-muted" style="font-size: 0.72rem;">(Lewat masa tenggang 3 hari)</small>
                        </div>
                        <span class="fw-bold {{ $peminjaman->total_denda > 0 ? 'text-danger' : 'text-dark' }}">
                            Rp {{ number_format($peminjaman->total_denda, 0, ',', '.') }}
                        </span>
                    </li>
                    <li class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <div>
                            <span class="text-muted small">Biaya Perbaikan Alat:</span><br>
                            <small class="text-muted" style="font-size: 0.72rem;">(Rp10.000 / unit rusak)</small>
                        </div>
                        <span class="fw-bold {{ $peminjaman->total_biaya_perbaikan > 0 ? 'text-danger' : 'text-dark' }}">
                            Rp {{ number_format($peminjaman->total_biaya_perbaikan, 0, ',', '.') }}
                        </span>
                    </li>
                </ul>
                <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center border mb-3">
                    <strong class="text-dark">Total Biaya:</strong>
                    <h5 class="fw-bold mb-0 {{ $peminjaman->totalTagihan() > 0 ? 'text-danger' : 'text-success' }}">
                        Rp {{ number_format($peminjaman->totalTagihan(), 0, ',', '.') }}
                    </h5>
                </div>

                @if($peminjaman->status === 'dikembalikan')
                    <div class="p-3 rounded-3 border {{ $peminjaman->isLunas() ? 'bg-success-subtle border-success' : 'bg-danger-subtle border-danger' }}">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted fw-semibold">Status Pembayaran:</span>
                            @if($peminjaman->isLunas())
                                <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> LUNAS</span>
                            @else
                                <span class="badge bg-danger"><i class="bi bi-exclamation-octagon-fill me-1"></i> BELUM LUNAS</span>
                            @endif
                        </div>
                        @if($peminjaman->isLunas())
                            <small class="text-success-emphasis d-block" style="font-size: 0.75rem;">
                                Metode: <strong>{{ $peminjaman->metode_pembayaran ?: 'Tunai di Lab' }}</strong>
                                @if($peminjaman->waktu_pembayaran)
                                    &bull; {{ $peminjaman->waktu_pembayaran->format('d/m/Y H:i') }} WIB
                                @endif
                            </small>
                        @else
                            <small class="text-danger-emphasis d-block mb-2" style="font-size: 0.75rem;">
                                Mahasiswa belum melunasi denda/kerusakan sebesar <strong>Rp {{ number_format($peminjaman->totalTagihan(), 0, ',', '.') }}</strong>. Mahasiswa diblokir dari peminjaman baru sampai tagihan ini lunas.
                            </small>
                            <button type="button" class="btn btn-sm btn-success w-100 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalPelunasan">
                                <i class="bi bi-cash-coin me-1"></i> Konfirmasi Pelunasan Denda
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            @if($peminjaman->status === 'dikembalikan' && $peminjaman->isBelumLunas())
                <!-- Modal Pelunasan Denda -->
                <div class="modal fade text-start" id="modalPelunasan" tabindex="-1" aria-labelledby="modalPelunasanLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <form action="{{ route('pengembalian.bayar', $peminjaman->id) }}" method="POST">
                                @csrf
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title fs-6 fw-bold" id="modalPelunasanLabel">
                                        <i class="bi bi-cash-stack me-2"></i>Konfirmasi Pelunasan Denda / Kerusakan
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body py-4">
                                    <p class="mb-2">Konfirmasi penerimaan pembayaran tagihan untuk transaksi ini:</p>
                                    <div class="p-3 bg-light rounded-3 border mb-3">
                                        <strong>{{ $peminjaman->kode_transaksi }}</strong> &bull; {{ $peminjaman->mahasiswa->nama }}<br>
                                        <span class="text-muted small">Total yang harus dibayar:</span>
                                        <h5 class="fw-bold text-danger mb-0 mt-1">Rp {{ number_format($peminjaman->totalTagihan(), 0, ',', '.') }}</h5>
                                    </div>
                                    <div class="mb-3">
                                        <label for="metode_pembayaran_modal" class="form-label small fw-semibold">Pilih Kanal Pembayaran <span class="text-danger">*</span></label>
                                        <select name="metode_pembayaran" id="metode_pembayaran_modal" class="form-select" required>
                                            <option value="Tunai (Cash Meja Lab)">Tunai (Cash di Meja Lab)</option>
                                            <option value="QRIS Kas Laboratorium">QRIS Kas Laboratorium Terpadu</option>
                                            <option value="Transfer Bank Kas Lab">Transfer Bank (Rekening Kas Lab)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-check-lg me-1"></i> Simpan Sebagai LUNAS
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            @if($peminjaman->status === 'menunggu_acc')
                <div class="card-footer bg-white border-top p-3 d-flex flex-column gap-2">
                    <button type="button" class="btn btn-success w-100 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAccDetail">
                        <i class="bi bi-check-circle-fill me-1"></i> Setujui Peminjaman (ACC)
                    </button>
                    <button type="button" class="btn btn-outline-danger w-100 btn-sm" data-bs-toggle="modal" data-bs-target="#modalRejectDetail">
                        <i class="bi bi-x-circle me-1"></i> Tolak Permohonan
                    </button>
                </div>

                <!-- Modal ACC Detail -->
                <div class="modal fade text-start" id="modalAccDetail" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form action="{{ route('peminjaman.approve', $peminjaman->id) }}" method="POST">
                                @csrf
                                <div class="modal-header bg-success text-white">
                                    <h6 class="modal-title fw-bold">Konfirmasi ACC Peminjaman</h6>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <p class="mb-2">Anda akan menyetujui peminjaman untuk:</p>
                                    <div class="p-3 bg-light rounded-3 mb-3 border">
                                        <strong>{{ $peminjaman->mahasiswa->nama }}</strong> ({{ $peminjaman->mahasiswa->nim }})<br>
                                        <small class="text-muted">Kode Transaksi: {{ $peminjaman->kode_transaksi }}</small>
                                    </div>
                                    <div class="alert alert-info py-2 small mb-3">
                                        <i class="bi bi-info-circle me-1"></i> Stok alat yang diminta akan otomatis dipotong secara atomik dari inventaris dan nomor unit fisik akan dialokasikan ke mahasiswa.
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label small fw-semibold">Catatan Petugas (Opsional):</label>
                                        <input type="text" name="catatan_admin" class="form-control form-control-sm" placeholder="Contoh: Disetujui, silakan ambil unit di Meja Lab 2">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-sm btn-success px-3">
                                        <i class="bi bi-check2-circle me-1"></i> Setujui Sekarang (ACC)
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Reject Detail -->
                <div class="modal fade text-start" id="modalRejectDetail" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form action="{{ route('peminjaman.reject', $peminjaman->id) }}" method="POST">
                                @csrf
                                <div class="modal-header bg-danger text-white">
                                    <h6 class="modal-title fw-bold">Tolak Permohonan Peminjaman</h6>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <p class="mb-2">Apakah Anda yakin ingin menolak permohonan dari <strong>{{ $peminjaman->mahasiswa->nama }}</strong>?</p>
                                    <div class="mb-0">
                                        <label class="form-label small fw-semibold text-danger">Alasan Penolakan <span class="text-danger">*</span></label>
                                        <textarea name="catatan_admin" class="form-control" rows="3" placeholder="Contoh: Alat sedang dalam jadwal perawatan / praktikum..." required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-sm btn-danger px-3">
                                        <i class="bi bi-x-circle me-1"></i> Tolak Permohonan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            @if($peminjaman->status === 'dipinjam')
                <div class="card-footer bg-white border-top p-3 d-flex flex-column gap-2">
                    <a href="{{ route('pengembalian.form', $peminjaman->id) }}" class="btn btn-primary w-100 shadow-sm">
                        <i class="bi bi-box-arrow-in-down-left me-1"></i> Proses Pengembalian Alat
                    </a>
                    <button type="button" class="btn btn-outline-danger w-100 btn-sm" data-bs-toggle="modal" data-bs-target="#batalModal">
                        <i class="bi bi-x-circle me-1"></i> Batalkan Transaksi Ini
                    </button>
                </div>

                <!-- Modal Konfirmasi Batal -->
                <div class="modal fade text-start" id="batalModal" tabindex="-1" aria-labelledby="batalModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title fs-6 fw-bold" id="batalModalLabel">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Batalkan Transaksi
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body py-4">
                                <p class="mb-2">Apakah Anda yakin ingin membatalkan transaksi peminjaman ini?</p>
                                <div class="p-3 bg-light rounded-3 border">
                                    <strong>{{ $peminjaman->kode_transaksi }}</strong> &bull; {{ $peminjaman->mahasiswa->nama }}<br>
                                    <small class="text-muted">Total {{ $peminjaman->detailPeminjaman->sum('jumlah') }} unit alat yang dialokasikan akan segera dipulihkan ke stok tersedia.</small>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                <form action="{{ route('peminjaman.cancel', $peminjaman->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger">Ya, Batalkan Transaksi</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
