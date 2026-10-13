@extends('layouts.app')

@section('title', 'Rincian Peminjaman - ' . $peminjaman->kode_transaksi)
@section('page_title', 'Rincian Pengajuan & Peminjaman')
@section('page_subtitle', 'Pantau status verifikasi, jatuh tempo, dan rincian denda secara transparan')

@section('content')
<div class="row g-4">
    <!-- Kolom Kiri: Status & Rincian Alat -->
    <div class="col-12 col-lg-8">
        <!-- Status Stepper Card -->
        <div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden">
            <div class="card-body p-4 bg-light bg-opacity-50 border-bottom">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                    <div>
                        <span class="badge bg-secondary bg-opacity-25 text-dark font-monospace px-3 py-1 mb-1">
                            {{ $peminjaman->kode_transaksi }}
                        </span>
                        <h5 class="fw-bold text-dark mb-0">Status Peminjaman Anda</h5>
                    </div>
                    <div>
                        @if($peminjaman->status === 'menunggu_acc')
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-3 py-2 rounded-pill fs-6">
                                <i class="bi bi-hourglass-split me-1"></i> Menunggu Persetujuan Admin
                            </span>
                        @elseif($peminjaman->status === 'dipinjam')
                            @if($peminjaman->isTerlambat())
                                <span class="badge bg-danger text-white px-3 py-2 rounded-pill fs-6 animate__animated animate__pulse animate__infinite">
                                    <i class="bi bi-exclamation-octagon-fill me-1"></i> Terlambat Mengembalikan
                                </span>
                            @else
                                <span class="badge bg-primary text-white px-3 py-2 rounded-pill fs-6">
                                    <i class="bi bi-shield-check me-1"></i> Sedang Aktif Dipinjam
                                </span>
                            @endif
                        @elseif($peminjaman->status === 'dikembalikan')
                            <span class="badge bg-success text-white px-3 py-2 rounded-pill fs-6">
                                <i class="bi bi-check-circle-fill me-1"></i> Transaksi Selesai (Dikembalikan)
                            </span>
                        @elseif($peminjaman->status === 'ditolak')
                            <span class="badge bg-danger text-white px-3 py-2 rounded-pill fs-6">
                                <i class="bi bi-x-circle-fill me-1"></i> Permohonan Ditolak
                            </span>
                        @else
                            <span class="badge bg-secondary text-white px-3 py-2 rounded-pill fs-6">
                                {{ ucfirst($peminjaman->status) }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Visual Timeline Steps -->
                <div class="row text-center mt-4 g-2">
                    <div class="col-4">
                        <div class="rounded-pill p-2 fw-semibold small {{ in_array($peminjaman->status, ['menunggu_acc', 'dipinjam', 'dikembalikan']) ? 'bg-primary text-white shadow-sm' : 'bg-light text-muted border' }}">
                            <i class="bi bi-1-circle-fill me-1"></i> 1. Pengajuan
                        </div>
                        <small class="text-muted d-block mt-1">{{ $peminjaman->created_at->format('d/m/Y') }}</small>
                    </div>
                    <div class="col-4">
                        <div class="rounded-pill p-2 fw-semibold small {{ in_array($peminjaman->status, ['dipinjam', 'dikembalikan']) ? 'bg-primary text-white shadow-sm' : ($peminjaman->status === 'ditolak' ? 'bg-danger text-white' : 'bg-secondary bg-opacity-25 text-muted') }}">
                            <i class="bi bi-2-circle-fill me-1"></i> 2. Verifikasi ACC
                        </div>
                        <small class="text-muted d-block mt-1">
                            {{ $peminjaman->approved_at ? \Carbon\Carbon::parse($peminjaman->approved_at)->format('d/m/Y H:i') : ($peminjaman->status === 'ditolak' ? 'Ditolak' : 'Antrean Admin') }}
                        </small>
                    </div>
                    <div class="col-4">
                        <div class="rounded-pill p-2 fw-semibold small {{ $peminjaman->status === 'dikembalikan' ? 'bg-success text-white shadow-sm' : 'bg-secondary bg-opacity-25 text-muted' }}">
                            <i class="bi bi-3-circle-fill me-1"></i> 3. Pengembalian Lab
                        </div>
                        <small class="text-muted d-block mt-1">
                            {{ $peminjaman->tanggal_kembali ? $peminjaman->tanggal_kembali->format('d/m/Y') : 'Belum Selesai' }}
                        </small>
                    </div>
                </div>
            </div>

            <!-- Pesan / Catatan Admin jika ditolak -->
            @if($peminjaman->status === 'ditolak' && $peminjaman->catatan_admin)
                <div class="p-3 bg-danger-subtle text-danger border-bottom">
                    <strong><i class="bi bi-info-circle-fill me-1"></i> Alasan Penolakan Petugas Lab:</strong>
                    <p class="mb-0 mt-1 small">{{ $peminjaman->catatan_admin }}</p>
                </div>
            @endif

            <!-- Warning Keterlambatan Aktif -->
            @if($peminjaman->status === 'dipinjam' && $peminjaman->isTerlambat())
                @php
                    $hariTerlambat = $peminjaman->hitungHariTerlambat();
                    $hariKenaDenda = $peminjaman->hitungHariKenaDenda();
                    $estimasiDenda = $peminjaman->hitungDenda();
                @endphp
                <div class="p-3 bg-danger text-white border-bottom d-flex align-items-center gap-3">
                    <i class="bi bi-exclamation-diamond-fill fs-2"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Masa Peminjaman Melewati Batas Waktu!</h6>
                        <small>
                            Anda terlambat <strong>{{ $hariTerlambat }} hari</strong> (Masa tenggang bebas denda 3 hari). 
                            Hari kena denda berjalan: <strong>{{ $hariKenaDenda }} hari</strong>. Estimasi denda: <strong>Rp{{ number_format($estimasiDenda, 0, ',', '.') }}</strong> (Rp2.000/hari).
                            Segera serahkan alat ke ruang laboratorium teknik.
                        </small>
                    </div>
                </div>
            @endif

            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Tanggal Pinjam</small>
                        <div class="fw-bold text-dark mt-1">{{ $peminjaman->tanggal_pinjam->translatedFormat('d F Y') }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Batas Jatuh Tempo</small>
                        <div class="fw-bold mt-1 {{ $peminjaman->isTerlambat() ? 'text-danger' : 'text-dark' }}">
                            {{ $peminjaman->tanggal_jatuh_tempo->translatedFormat('d F Y') }}
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Tanggal Pengembalian</small>
                        <div class="fw-bold text-dark mt-1">
                            {{ $peminjaman->tanggal_kembali ? $peminjaman->tanggal_kembali->translatedFormat('d F Y') : '-' }}
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Waktu Input</small>
                        <div class="text-muted mt-1 small">{{ $peminjaman->created_at->format('d/m/Y H:i') }} WIB</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Tabel Rincian Alat -->
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-box-seam text-primary me-2"></i>Daftar Alat yang Dipinjam
                    </h6>
                    <small class="text-muted">Total {{ $peminjaman->detailPeminjaman->sum('jumlah') }} unit alat</small>
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
                                <th>Nama & Kode Alat</th>
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
                                        @elseif($peminjaman->status === 'menunggu_acc')
                                            <div class="text-muted small mt-1" style="font-size: 0.72rem;">
                                                <i class="bi bi-clock me-1"></i>Kode unit fisik terkalibrasi diterbitkan saat di-ACC petugas
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
                                            <span class="badge bg-light text-muted border">Belum Diperiksa</span>
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

    <!-- Kolom Kanan: Rincian Keuangan & Tombol Aksi -->
    <div class="col-12 col-lg-4">
        <!-- Card Finansial / Denda -->
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-receipt text-primary me-2"></i>Rincian Biaya & Denda
                </h6>
            </div>
            <div class="card-body p-4">
                <ul class="list-unstyled mb-3 d-flex flex-column gap-3">
                    <li class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small d-block">Denda Keterlambatan:</span>
                            <small class="text-muted" style="font-size: 0.72rem;">(Rp2.000/hari setelah hari ke-3)</small>
                        </div>
                        <span class="fw-bold {{ $peminjaman->total_denda > 0 ? 'text-danger' : 'text-dark' }}">
                            Rp {{ number_format($peminjaman->total_denda, 0, ',', '.') }}
                        </span>
                    </li>
                    <li class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <div>
                            <span class="text-muted small d-block">Biaya Kerusakan Alat:</span>
                            <small class="text-muted" style="font-size: 0.72rem;">(Rp10.000 per unit rusak)</small>
                        </div>
                        <span class="fw-bold {{ $peminjaman->total_biaya_perbaikan > 0 ? 'text-danger' : 'text-dark' }}">
                            Rp {{ number_format($peminjaman->total_biaya_perbaikan, 0, ',', '.') }}
                        </span>
                    </li>
                </ul>

                <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center border">
                    <strong class="text-dark">Total Tagihan:</strong>
                    <h5 class="fw-bold mb-0 {{ ($peminjaman->total_denda + $peminjaman->total_biaya_perbaikan) > 0 ? 'text-danger' : 'text-success' }}">
                        Rp {{ number_format($peminjaman->total_denda + $peminjaman->total_biaya_perbaikan, 0, ',', '.') }}
                    </h5>
                </div>

                @if(($peminjaman->total_denda + $peminjaman->total_biaya_perbaikan) > 0)
                    <div class="alert alert-warning mb-0 mt-3 p-2 small border-0 bg-warning-subtle text-warning-emphasis">
                        <i class="bi bi-info-circle-fill me-1"></i> Harap menyelesaikan pembayaran denda/kerusakan ini langsung ke kasir laboratorium saat serah terima alat.
                    </div>
                @endif
            </div>

            <div class="card-footer bg-white border-top p-3 d-flex flex-column gap-2">
                @if(in_array($peminjaman->status, ['dipinjam', 'dikembalikan']))
                    <a href="{{ route('peminjaman.nota', $peminjaman->id) }}" target="_blank" class="btn btn-outline-primary w-100 shadow-sm">
                        <i class="bi bi-printer me-1"></i> Cetak Bukti / Nota Transaksi
                    </a>
                @endif
                <a href="{{ route('mahasiswa.portal.dashboard') }}" class="btn btn-light w-100 border">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard Mahasiswa
                </a>
            </div>
        </div>

        <!-- Panduan Aturan Laboratorium -->
        <div class="card shadow-sm border-0 rounded-4 bg-primary text-white p-3">
            <h6 class="fw-bold mb-2"><i class="bi bi-shield-check me-2"></i>Aturan Peminjaman Lab</h6>
            <ul class="small mb-0 ps-3 d-flex flex-column gap-1 opacity-75">
                <li>Batas waktu peminjaman default adalah 7 hari.</li>
                <li>Masa tenggang keterlambatan adalah 3 hari (belum kena denda).</li>
                <li>Hari ke-4 keterlambatan dan seterusnya dikenai denda Rp2.000/hari.</li>
                <li>Setiap unit alat yang kembali dalam kondisi rusak dikenai biaya perbaikan Rp10.000/unit.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
