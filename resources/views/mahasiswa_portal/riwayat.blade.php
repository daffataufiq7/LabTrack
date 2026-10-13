@extends('layouts.app')

@section('title', 'Riwayat & Status Peminjaman Mahasiswa')
@section('page_title', 'Status & Riwayat Peminjaman')
@section('page_subtitle', 'Pantau pengajuan menunggu persetujuan (ACC), pinjaman aktif, serta riwayat pengembalian alat')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <!-- Tab Filter Navigasi -->
        <div class="card shadow-sm border-0 mb-4 rounded-3">
            <div class="card-body p-2 bg-light rounded-3">
                <ul class="nav nav-pills nav-fill gap-1">
                    <li class="nav-item">
                        <a href="{{ route('mahasiswa.portal.riwayat', ['tab' => 'semua']) }}" 
                           class="nav-link py-2 {{ $tab === 'semua' ? 'active shadow-sm' : 'text-secondary' }}">
                            Semua Transaksi
                            <span class="badge {{ $tab === 'semua' ? 'bg-light text-dark' : 'bg-secondary bg-opacity-25 text-dark' }} ms-1">
                                {{ $countSemua }}
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('mahasiswa.portal.riwayat', ['tab' => 'menunggu_acc']) }}" 
                           class="nav-link py-2 {{ $tab === 'menunggu_acc' ? 'active shadow-sm' : 'text-secondary' }}">
                            <i class="bi bi-hourglass-split me-1"></i> Menunggu ACC
                            <span class="badge {{ $tab === 'menunggu_acc' ? 'bg-light text-dark' : 'bg-warning-subtle text-warning-emphasis border border-warning' }} ms-1">
                                {{ $countMenungguAcc }}
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('mahasiswa.portal.riwayat', ['tab' => 'dipinjam']) }}" 
                           class="nav-link py-2 {{ $tab === 'dipinjam' ? 'active shadow-sm' : 'text-secondary' }}">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Aktif Dipinjam
                            <span class="badge {{ $tab === 'dipinjam' ? 'bg-light text-dark' : 'bg-primary-subtle text-primary border border-primary' }} ms-1">
                                {{ $countDipinjam }}
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('mahasiswa.portal.riwayat', ['tab' => 'dikembalikan']) }}" 
                           class="nav-link py-2 {{ $tab === 'dikembalikan' ? 'active shadow-sm' : 'text-secondary' }}">
                            <i class="bi bi-check-circle me-1"></i> Selesai
                            <span class="badge {{ $tab === 'dikembalikan' ? 'bg-light text-dark' : 'bg-success-subtle text-success border border-success' }} ms-1">
                                {{ $countDikembalikan }}
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('mahasiswa.portal.riwayat', ['tab' => 'ditolak']) }}" 
                           class="nav-link py-2 {{ $tab === 'ditolak' ? 'active shadow-sm' : 'text-secondary' }}">
                            <i class="bi bi-x-circle me-1"></i> Ditolak
                            <span class="badge {{ $tab === 'ditolak' ? 'bg-light text-dark' : 'bg-danger-subtle text-danger border border-danger' }} ms-1">
                                {{ $countDitolak }}
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Tombol Aksi Cepat -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h6 class="fw-bold mb-0 text-dark">
                    @if($tab === 'menunggu_acc')
                        Preview Pengajuan Menunggu ACC Petugas Laboratorium
                    @elseif($tab === 'dipinjam')
                        Daftar Alat Sedang Aktif Dipinjam (Sudah Disetujui)
                    @elseif($tab === 'dikembalikan')
                        Riwayat Peminjaman yang Telah Selesai
                    @elseif($tab === 'ditolak')
                        Daftar Permohonan yang Ditolak Petugas Lab
                    @else
                        Semua Catatan Peminjaman Anda
                    @endif
                </h6>
                <small class="text-muted">Total {{ $daftarPeminjaman->total() }} catatan ditemukan</small>
            </div>
            <a href="{{ route('mahasiswa.portal.ajukan') }}" class="btn btn-sm btn-primary shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Ajukan Peminjaman Baru
            </a>
        </div>

        <!-- Daftar Kartu Transaksi -->
        @forelse($daftarPeminjaman as $trx)
            <div class="card shadow-sm border-0 mb-3 rounded-3 overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="font-monospace fw-bold text-primary fs-6">{{ $trx->kode_transaksi }}</span>
                        @if($trx->status === 'menunggu_acc')
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2 py-1">
                                <i class="bi bi-hourglass-split me-1"></i> Menunggu ACC Petugas Lab
                            </span>
                        @elseif($trx->status === 'dipinjam')
                            @if($trx->isTerlambat())
                                <span class="badge bg-danger text-white px-2 py-1">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Terlambat ({{ $trx->hitungHariTerlambat() }} Hari)
                                </span>
                            @else
                                <span class="badge bg-primary text-white px-2 py-1">
                                    <i class="bi bi-check-circle-fill me-1"></i> Sedang Dipinjam
                                </span>
                            @endif
                        @elseif($trx->status === 'dikembalikan')
                            <span class="badge bg-success text-white px-2 py-1">
                                <i class="bi bi-check2-all me-1"></i> Sudah Dikembalikan
                            </span>
                        @elseif($trx->status === 'ditolak')
                            <span class="badge bg-danger text-white px-2 py-1">
                                <i class="bi bi-x-circle-fill me-1"></i> Permohonan Ditolak
                            </span>
                        @else
                            <span class="badge bg-secondary text-white px-2 py-1">
                                {{ ucfirst($trx->status) }}
                            </span>
                        @endif
                    </div>
                    <small class="text-muted">Diajukan pada: {{ $trx->created_at->format('d/m/Y H:i') }} WIB</small>
                </div>

                <div class="card-body p-4">
                    <!-- Box Status Khusus Menunggu ACC -->
                    @if($trx->status === 'menunggu_acc')
                        <div class="alert alert-warning d-flex align-items-center justify-content-between p-3 rounded-3 border-warning border-opacity-50 mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-info-circle-fill text-warning fs-5"></i>
                                <span class="small text-dark">
                                    <strong>Status Permohonan:</strong> Berkas peminjaman ini telah terkirim dan berada di antrean verifikasi laboran. 
                                    Stok alat di gudang laboratorium akan otomatis dialokasikan dan dipotong saat petugas menekan <strong>[Setujui / ACC]</strong>.
                                </span>
                            </div>
                            <form action="{{ route('mahasiswa.portal.ajukan.batal', $trx->id) }}" method="POST" class="ms-3" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan pinjaman ini?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger text-nowrap">
                                    <i class="bi bi-x-circle me-1"></i> Batalkan Pengajuan
                                </button>
                            </form>
                        </div>
                    @endif

                    <!-- Box Status Ditolak -->
                    @if($trx->status === 'ditolak')
                        <div class="alert alert-danger p-3 rounded-3 mb-3">
                            <strong><i class="bi bi-exclamation-octagon-fill me-1"></i> Alasan Penolakan dari Petugas Lab:</strong>
                            <p class="mb-0 mt-1 small">{{ $trx->catatan_admin ?: 'Tidak memenuhi syarat operasional praktikum.' }}</p>
                        </div>
                    @endif

                    <!-- Periode Sewa & Tanggal -->
                    <div class="row g-3 mb-3 bg-light p-3 rounded-3">
                        <div class="col-6 col-md-3">
                            <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Sewa Dari Tanggal</small>
                            <div class="fw-bold text-dark mt-1">{{ $trx->tanggal_pinjam->translatedFormat('d M Y') }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Sampai Tanggal (Batas)</small>
                            <div class="fw-bold mt-1 {{ $trx->isTerlambat() ? 'text-danger' : 'text-dark' }}">
                                {{ $trx->tanggal_jatuh_tempo->translatedFormat('d M Y') }}
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Durasi Sewa</small>
                            <div class="fw-bold text-dark mt-1">
                                {{ $trx->tanggal_pinjam->diffInDays($trx->tanggal_jatuh_tempo) }} Hari
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">
                                {{ $trx->status === 'dikembalikan' ? 'Tanggal Kembali' : ($trx->status === 'dipinjam' ? 'Status Batas Waktu' : 'Status Persetujuan') }}
                            </small>
                            <div class="fw-bold mt-1">
                                @if($trx->status === 'dikembalikan')
                                    <span class="text-success">{{ $trx->tanggal_kembali ? $trx->tanggal_kembali->translatedFormat('d M Y') : '-' }}</span>
                                @elseif($trx->status === 'dipinjam')
                                    @if($trx->isTerlambat())
                                        <span class="text-danger">Telat {{ $trx->hitungHariTerlambat() }} hari</span>
                                    @else
                                        <span class="text-primary">{{ \Carbon\Carbon::today()->diffInDays($trx->tanggal_jatuh_tempo, false) }} hari tersisa</span>
                                    @endif
                                @elseif($trx->status === 'menunggu_acc')
                                    <span class="text-warning">Antrean Admin</span>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Rincian Alat yang Dipinjam -->
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Alat Laboratorium</th>
                                    <th>Kategori</th>
                                    <th class="text-center" style="width: 120px;">Jumlah</th>
                                    @if($trx->status === 'dikembalikan')
                                        <th>Kondisi Pengembalian</th>
                                        <th class="text-end" style="width: 140px;">Biaya Kerusakan</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trx->detailPeminjaman as $item)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $item->alat->nama_alat }}</span>
                                            <small class="text-muted font-monospace d-block" style="font-size: 0.75rem;">{{ $item->alat->kode_alat }}</small>
                                        </td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">{{ $item->alat->kategori }}</span></td>
                                        <td class="text-center fw-bold text-primary">{{ $item->jumlah }} Unit</td>
                                        @if($trx->status === 'dikembalikan')
                                            <td>
                                                <span class="badge {{ str_contains($item->kondisi_kembali, 'Rusak') ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
                                                    {{ $item->kondisi_kembali ?: 'Baik' }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                Rp {{ number_format($item->biaya_perbaikan, 0, ',', '.') }}
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        @if($trx->status === 'dikembalikan' && ($trx->total_denda + $trx->total_biaya_perbaikan) > 0)
                            <span class="small text-danger fw-semibold">
                                Total Denda & Kerusakan Selesai: Rp {{ number_format($trx->total_denda + $trx->total_biaya_perbaikan, 0, ',', '.') }}
                            </span>
                        @elseif($trx->status === 'dipinjam' && $trx->isTerlambat())
                            <span class="small text-danger fw-semibold">
                                Estimasi Denda Berjalan: Rp {{ number_format($trx->hitungDenda(), 0, ',', '.') }} (Rp2.000/hari)
                            </span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if(in_array($trx->status, ['dipinjam', 'dikembalikan']))
                            <a href="{{ route('peminjaman.nota', $trx->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-printer me-1"></i> Cetak Struk / Nota
                            </a>
                        @endif
                        <a href="{{ route('mahasiswa.portal.detail', $trx->id) }}" class="btn btn-sm btn-outline-secondary">
                            Lihat Rincian <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="card shadow-sm border-0 rounded-3 text-center p-5">
                <div class="text-muted mb-3">
                    <i class="bi bi-inbox fs-1"></i>
                </div>
                <h6 class="fw-bold text-secondary">Tidak Ada Data Peminjaman</h6>
                <p class="small text-muted mb-3">Belum ada transaksi peminjaman pada kategori ini.</p>
                <div>
                    <a href="{{ route('mahasiswa.portal.ajukan') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Ajukan Peminjaman Sekarang
                    </a>
                </div>
            </div>
        @endforelse

        <!-- Pagination -->
        <div class="mt-4 d-flex justify-content-end">
            {{ $daftarPeminjaman->links() }}
        </div>
    </div>
</div>
@endsection
