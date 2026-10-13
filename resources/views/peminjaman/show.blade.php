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

        <!-- Card Header Transaksi -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-6 fw-bold font-monospace text-primary">{{ $peminjaman->kode_transaksi }}</span>
                    @if($peminjaman->status === 'dipinjam')
                        @if($peminjaman->isTerlambat())
                            <span class="badge bg-danger badge-status">Terlambat (Aktif)</span>
                        @else
                            <span class="badge bg-warning text-dark badge-status">Sedang Dipinjam</span>
                        @endif
                    @elseif($peminjaman->status === 'dikembalikan')
                        <span class="badge bg-success badge-status">Sudah Dikembalikan</span>
                    @else
                        <span class="badge bg-secondary badge-status">Dibatalkan</span>
                    @endif
                </div>
                <a href="{{ route('peminjaman.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
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
                                        <small class="text-muted font-monospace">{{ $detail->alat->kode_alat }}</small>
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
                <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center border">
                    <strong class="text-dark">Total Biaya:</strong>
                    <h5 class="fw-bold mb-0 {{ ($peminjaman->total_denda + $peminjaman->total_biaya_perbaikan) > 0 ? 'text-danger' : 'text-success' }}">
                        Rp {{ number_format($peminjaman->total_denda + $peminjaman->total_biaya_perbaikan, 0, ',', '.') }}
                    </h5>
                </div>
            </div>
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
