@extends('layouts.app')

@section('title', 'Portal Mahasiswa — ' . $mahasiswa->nama)
@section('page_title', 'Portal Mandiri Peminjaman Mahasiswa')
@section('page_subtitle', 'Kelola pengajuan peminjaman, pantau batas jatuh tempo, dan cek status tanggungan denda/biaya alat')

@section('content')
<!-- Header Profil & Quick Actions -->
<div class="card shadow-sm border-0 mb-4 bg-gradient" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);">
    <div class="card-body p-4 text-white">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-white bg-opacity-20 rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="bi bi-mortarboard-fill fs-2"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1">{{ $mahasiswa->nama }}</h5>
                    <div class="small text-white-50">
                        NIM: <span class="font-monospace text-white fw-semibold">{{ $mahasiswa->nim }}</span> &bull; 
                        Program Studi: <span class="text-white fw-semibold">{{ $mahasiswa->program_studi }}</span>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('mahasiswa.portal.riwayat') }}" class="btn btn-sm btn-outline-light px-3 shadow-sm">
                    <i class="bi bi-clock-history me-1"></i> Riwayat & Status ACC
                </a>
                <a href="{{ route('mahasiswa.portal.katalog') }}" class="btn btn-sm btn-light text-primary fw-semibold px-3 shadow-sm">
                    <i class="bi bi-search me-1"></i> Cek Stok Alat
                </a>
                <a href="{{ route('mahasiswa.portal.ajukan') }}" class="btn btn-sm btn-warning text-dark fw-bold px-3 shadow-sm">
                    <i class="bi bi-plus-circle-fill me-1"></i> Ajukan Pinjam Baru
                </a>
            </div>
        </div>
    </div>
</div>

<!-- 4 Statistik Kartu Ringkasan -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3">
                    <i class="bi bi-box-seam-fill fs-4"></i>
                </div>
                <div>
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Pinjaman Aktif</small>
                    <h4 class="fw-bold mb-0 text-dark">{{ $pinjamanAktif->count() }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
                <div>
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Menunggu ACC</small>
                    <h4 class="fw-bold mb-0 text-dark">{{ $menungguAcc->count() }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-3">
                    <i class="bi bi-check-circle-fill fs-4"></i>
                </div>
                <div>
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Selesai Kembali</small>
                    <h4 class="fw-bold mb-0 text-dark">{{ $riwayatSelesai->total() }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-3">
                    <i class="bi bi-cash-coin fs-4"></i>
                </div>
                <div>
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Total Denda/Rusak</small>
                    <h5 class="fw-bold mb-0 text-danger font-monospace">Rp {{ number_format($totalKewajiban, 0, ',', '.') }}</h5>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Visual Timeline Transaksi Terkini -->
@if($latestLoan)
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary fw-bold">Status Sirkulasi Terkini</span>
            <span class="font-monospace fw-bold text-dark">{{ $latestLoan->kode_transaksi }}</span>
        </div>
        <a href="{{ route('peminjaman.nota', $latestLoan->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-printer me-1"></i> Cetak Dokumen Nota
        </a>
    </div>
    <div class="card-body p-4">
        <div class="d-flex justify-content-between flex-wrap gap-2 text-center position-relative">
            <!-- Step 1 -->
            <div class="d-flex flex-column align-items-center" style="flex: 1;">
                <div class="rounded-circle bg-success text-white p-2 mb-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <strong class="small text-dark">1. Diajukan</strong>
                <span class="text-muted" style="font-size: 0.7rem;">{{ $latestLoan->tanggal_pinjam->format('d M Y') }}</span>
            </div>

            <!-- Step 2 -->
            <div class="d-flex flex-column align-items-center" style="flex: 1;">
                @if($latestLoan->status === 'menunggu_acc')
                    <div class="rounded-circle bg-warning text-dark p-2 mb-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <strong class="small text-warning">2. Menunggu ACC</strong>
                    <span class="text-muted" style="font-size: 0.7rem;">Verifikasi Petugas Lab</span>
                @elseif($latestLoan->status === 'ditolak')
                    <div class="rounded-circle bg-danger text-white p-2 mb-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <strong class="small text-danger">2. Ditolak Admin</strong>
                    <span class="text-muted" style="font-size: 0.7rem;">{{ $latestLoan->catatan_admin }}</span>
                @else
                    <div class="rounded-circle bg-success text-white p-2 mb-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-check2"></i>
                    </div>
                    <strong class="small text-dark">2. Di-ACC Admin</strong>
                    <span class="text-muted" style="font-size: 0.7rem;">Disetujui</span>
                @endif
            </div>

            <!-- Step 3 -->
            <div class="d-flex flex-column align-items-center" style="flex: 1;">
                @if(in_array($latestLoan->status, ['dipinjam', 'dikembalikan']))
                    <div class="rounded-circle {{ $latestLoan->isTerlambat() && $latestLoan->status === 'dipinjam' ? 'bg-danger' : 'bg-primary' }} text-white p-2 mb-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <strong class="small {{ $latestLoan->isTerlambat() && $latestLoan->status === 'dipinjam' ? 'text-danger' : 'text-dark' }}">
                        {{ $latestLoan->status === 'dipinjam' ? '3. Sedang Dipinjam' : '3. Selesai Dipinjam' }}
                    </strong>
                    <span class="text-muted" style="font-size: 0.7rem;">Tempo: {{ $latestLoan->tanggal_jatuh_tempo->format('d M Y') }}</span>
                @else
                    <div class="rounded-circle bg-light text-muted p-2 mb-2 d-flex align-items-center justify-content-center border" style="width: 40px; height: 40px;">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <strong class="small text-muted">3. Penggunaan Alat</strong>
                    <span class="text-muted" style="font-size: 0.7rem;">Menunggu ACC</span>
                @endif
            </div>

            <!-- Step 4 -->
            <div class="d-flex flex-column align-items-center" style="flex: 1;">
                @if($latestLoan->status === 'dikembalikan')
                    <div class="rounded-circle bg-success text-white p-2 mb-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-patch-check-fill"></i>
                    </div>
                    <strong class="small text-success">4. Bebas Lab (Selesai)</strong>
                    <span class="text-muted" style="font-size: 0.7rem;">Kembali: {{ $latestLoan->tanggal_kembali ? $latestLoan->tanggal_kembali->format('d M Y') : '-' }}</span>
                @else
                    <div class="rounded-circle bg-light text-muted p-2 mb-2 d-flex align-items-center justify-content-center border" style="width: 40px; height: 40px;">
                        <i class="bi bi-patch-check"></i>
                    </div>
                    <strong class="small text-muted">4. Bebas Lab</strong>
                    <span class="text-muted" style="font-size: 0.7rem;">Belum Selesai</span>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<!-- Tab Navigasi Data Transaksi -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white p-2 border-bottom">
        <ul class="nav nav-pills" id="peminjamanTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-semibold" id="aktif-tab" data-bs-toggle="pill" data-bs-target="#aktif" type="button" role="tab">
                    <i class="bi bi-box-arrow-right me-1"></i> Sedang Dipinjam 
                    <span class="badge bg-warning text-dark ms-1">{{ $pinjamanAktif->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold" id="menunggu-tab" data-bs-toggle="pill" data-bs-target="#menunggu" type="button" role="tab">
                    <i class="bi bi-hourglass-split me-1"></i> Menunggu ACC Admin 
                    <span class="badge bg-primary ms-1">{{ $menungguAcc->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold" id="riwayat-tab" data-bs-toggle="pill" data-bs-target="#riwayat" type="button" role="tab">
                    <i class="bi bi-archive me-1"></i> Riwayat Selesai
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body p-4">
        <div class="tab-content" id="peminjamanTabContent">
            
            <!-- TAB 1: PINJAMAN AKTIF & PERINGATAN DENDA -->
            <div class="tab-pane fade show active" id="aktif" role="tabpanel">
                @if($pinjamanAktif->isEmpty())
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle fs-1 text-success d-block mb-2"></i>
                        <h6 class="fw-bold text-dark">Tidak Ada Alat yang Sedang Dipinjam</h6>
                        <p class="text-muted small mb-0">Anda saat ini tidak memiliki tanggungan peminjaman alat laboratorium.</p>
                    </div>
                @else
                    <div class="row g-3">
                        @foreach($pinjamanAktif as $loan)
                            @php
                                $isTelat = $loan->isTerlambat();
                                $hariTelat = $loan->hitungHariTerlambat();
                                $hariKenaDenda = $loan->hitungHariKenaDenda();
                                $estimasiDenda = $loan->hitungDenda();
                            @endphp
                            <div class="col-12 col-lg-6">
                                <div class="card border {{ $isTelat ? 'border-danger' : 'border-primary border-opacity-25' }} h-100 shadow-sm">
                                    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                                        <span class="font-monospace fw-bold text-primary">{{ $loan->kode_transaksi }}</span>
                                        @if($isTelat)
                                            <span class="badge bg-danger">Terlambat {{ $hariTelat }} Hari</span>
                                        @else
                                            <span class="badge bg-success">Dalam Masa Pinjam</span>
                                        @endif
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-3">
                                            <small class="text-muted d-block" style="font-size: 0.72rem;">ALAT YANG DIPINJAM:</small>
                                            <ul class="list-unstyled mb-0 small">
                                                @foreach($loan->detailPeminjaman as $d)
                                                    <li class="d-flex justify-content-between py-1 border-bottom border-light">
                                                        <span><strong>{{ $d->jumlah }}x</strong> {{ $d->alat->nama_alat }}</span>
                                                        <span class="text-muted font-monospace">{{ $d->alat->kode_alat }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>

                                        <div class="row g-2 mb-3 bg-light p-2 rounded small">
                                            <div class="col-6">
                                                <span class="text-muted d-block">Tanggal Pinjam:</span>
                                                <strong>{{ $loan->tanggal_pinjam->format('d M Y') }}</strong>
                                            </div>
                                            <div class="col-6">
                                                <span class="text-muted d-block">Batas Jatuh Tempo:</span>
                                                <strong class="{{ $isTelat ? 'text-danger' : 'text-dark' }}">{{ $loan->tanggal_jatuh_tempo->format('d M Y') }}</strong>
                                            </div>
                                        </div>

                                        <!-- Rincian Keterlambatan & Denda Transparan -->
                                        @if($isTelat)
                                            <div class="alert alert-danger p-2 mb-3 small">
                                                <div class="d-flex justify-content-between">
                                                    <span>Hari Terlambat:</span>
                                                    <strong>{{ $hariTelat }} Hari</strong>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span>Masa Toleransi:</span>
                                                    <span>3 Hari Tanpa Denda</span>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span>Hari Dikenai Denda:</span>
                                                    <strong>{{ $hariKenaDenda }} Hari</strong>
                                                </div>
                                                <div class="d-flex justify-content-between pt-1 border-top border-danger border-opacity-25 mt-1">
                                                    <strong>Estimasi Denda Akumulasi:</strong>
                                                    <strong class="font-monospace text-danger">Rp {{ number_format($estimasiDenda, 0, ',', '.') }}</strong>
                                                </div>
                                                <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">
                                                    *Harap segera kembalikan ke meja laboratorium agar denda tidak bertambah Rp2.000/hari.
                                                </small>
                                            </div>
                                        @endif

                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('peminjaman.nota', $loan->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-printer me-1"></i> Cetak Bukti Pinjam
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- TAB 2: PENGAJUAN MENUNGGU ACC ADMIN -->
            <div class="tab-pane fade" id="menunggu" role="tabpanel">
                @if($menungguAcc->isEmpty())
                    <div class="text-center py-4">
                        <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                        <h6 class="fw-bold text-dark">Tidak Ada Pengajuan Menunggu Persetujuan</h6>
                        <p class="text-muted small mb-0">Seluruh permohonan Anda telah direspons oleh petugas lab.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No. Transaksi</th>
                                    <th>Alat yang Diajukan</th>
                                    <th>Rencana Tanggal Pinjam</th>
                                    <th>Batas Rencana Kembali</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($menungguAcc as $acc)
                                    <tr>
                                        <td class="font-monospace text-primary fw-bold">{{ $acc->kode_transaksi }}</td>
                                        <td>
                                            @foreach($acc->detailPeminjaman as $det)
                                                <div><strong>{{ $det->jumlah }}x</strong> {{ $det->alat->nama_alat }}</div>
                                            @endforeach
                                        </td>
                                        <td>{{ $acc->tanggal_pinjam->format('d/m/Y') }}</td>
                                        <td>{{ $acc->tanggal_jatuh_tempo->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="badge bg-warning text-dark px-3 py-1 rounded-pill">
                                                <i class="bi bi-hourglass-split me-1"></i> Menunggu ACC Petugas
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- TAB 3: RIWAYAT PENGEMBALIAN & BEBAS LAB -->
            <div class="tab-pane fade" id="riwayat" role="tabpanel">
                @if($riwayatSelesai->isEmpty())
                    <div class="text-center py-4">
                        <i class="bi bi-clock-history fs-1 text-muted d-block mb-2"></i>
                        <h6 class="fw-bold text-dark">Belum Ada Riwayat Pengembalian</h6>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No. Transaksi</th>
                                    <th>Alat & Kondisi Kembali</th>
                                    <th>Tanggal Kembali</th>
                                    <th>Denda Telat</th>
                                    <th>Biaya Rusak</th>
                                    <th>Total Tagihan</th>
                                    <th class="text-end">Dokumen</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($riwayatSelesai as $hist)
                                    <tr>
                                        <td class="font-monospace text-dark fw-bold">{{ $hist->kode_transaksi }}</td>
                                        <td>
                                            @foreach($hist->detailPeminjaman as $dh)
                                                <div class="small">
                                                    <strong>{{ $dh->jumlah }}x</strong> {{ $dh->alat->nama_alat }}
                                                    @if($dh->kondisi_kembali)
                                                        <span class="text-muted">({{ $dh->kondisi_kembali }})</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </td>
                                        <td>{{ $hist->tanggal_kembali ? $hist->tanggal_kembali->format('d/m/Y') : '-' }}</td>
                                        <td class="font-monospace text-{{ $hist->total_denda > 0 ? 'danger' : 'muted' }}">
                                            Rp {{ number_format($hist->total_denda, 0, ',', '.') }}
                                        </td>
                                        <td class="font-monospace text-{{ $hist->total_biaya_perbaikan > 0 ? 'danger' : 'muted' }}">
                                            Rp {{ number_format($hist->total_biaya_perbaikan, 0, ',', '.') }}
                                        </td>
                                        <td class="font-monospace fw-bold text-{{ ($hist->total_denda + $hist->total_biaya_perbaikan) > 0 ? 'danger' : 'success' }}">
                                            Rp {{ number_format($hist->total_denda + $hist->total_biaya_perbaikan, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('peminjaman.nota', $hist->id) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Cetak Surat Bebas Lab">
                                                <i class="bi bi-printer me-1"></i> Nota Bebas Lab
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $riwayatSelesai->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
