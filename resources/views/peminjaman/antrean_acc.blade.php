@extends('layouts.app')

@section('title', 'Antrean Persetujuan Peminjaman (ACC)')
@section('page_title', 'Antrean Persetujuan Peminjaman Alat')
@section('page_subtitle', 'Verifikasi ketersediaan stok fisik dan persetujuan (ACC) permohonan pinjam mahasiswa')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                        <i class="bi bi-clock-history fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Daftar Pengajuan Menunggu ACC Petugas</h6>
                        <small class="text-muted">Total: <strong>{{ $antrean->count() }} pengajuan</strong> menunggu tindakan Anda</small>
                    </div>
                </div>
                <a href="{{ route('peminjaman.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Peminjaman
                </a>
            </div>

            <div class="card-body p-0">
                @if($antrean->isEmpty())
                    <div class="text-center py-5">
                        <div class="bg-light d-inline-flex p-4 rounded-circle text-muted mb-3">
                            <i class="bi bi-check2-all fs-1 text-success"></i>
                        </div>
                        <h6 class="fw-bold text-dark">Tidak Ada Pengajuan Menunggu ACC</h6>
                        <p class="text-muted small mb-0">Semua permohonan peminjaman dari mahasiswa telah diproses.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 140px;">No. Transaksi</th>
                                    <th>Mahasiswa Peminjam</th>
                                    <th>Alat yang Diminta</th>
                                    <th style="width: 170px;">Rencana Pinjam</th>
                                    <th style="width: 160px;">Status Kesiapan Stok</th>
                                    <th style="width: 180px;" class="text-end">Aksi Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($antrean as $item)
                                    @php
                                        $semuaStokCukup = true;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="badge bg-light text-primary border font-monospace px-2 py-1">
                                                {{ $item->kode_transaksi }}
                                            </span>
                                            <small class="d-block text-muted mt-1" style="font-size: 0.7rem;">
                                                Diajukan: {{ $item->created_at ? $item->created_at->diffForHumans() : '-' }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $item->mahasiswa->nama }}</div>
                                            <small class="text-muted font-monospace">NIM: {{ $item->mahasiswa->nim }} &bull; {{ $item->mahasiswa->program_studi }}</small>
                                        </td>
                                        <td>
                                            <ul class="list-unstyled mb-0 small">
                                                @foreach($item->detailPeminjaman as $det)
                                                    @php
                                                        $cukup = $det->alat->stok_tersedia >= $det->jumlah;
                                                        if (!$cukup) $semuaStokCukup = false;
                                                    @endphp
                                                    <li class="d-flex align-items-center justify-content-between mb-1">
                                                        <span>
                                                            <strong>{{ $det->jumlah }}x</strong> {{ $det->alat->nama_alat }}
                                                        </span>
                                                        <span class="badge {{ $cukup ? 'bg-success-subtle text-success border border-success' : 'bg-danger-subtle text-danger border border-danger' }} ms-2" style="font-size: 0.7rem;">
                                                            Ready: {{ $det->alat->stok_tersedia }}
                                                        </span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <div><i class="bi bi-calendar-event me-1 text-muted"></i> Pinjam: <strong>{{ $item->tanggal_pinjam->format('d/m/Y') }}</strong></div>
                                                <div><i class="bi bi-calendar-check me-1 text-danger"></i> Tempo: <strong>{{ $item->tanggal_jatuh_tempo->format('d/m/Y') }}</strong></div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($semuaStokCukup)
                                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                                    <i class="bi bi-check-circle me-1"></i> Stok Terpenuhi
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">
                                                    <i class="bi bi-exclamation-triangle me-1"></i> Stok Kurang!
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <!-- Tombol Setujui / ACC -->
                                                <button type="button" class="btn btn-sm btn-success px-2 shadow-sm" 
                                                        data-bs-toggle="modal" data-bs-target="#modalAcc{{ $item->id }}" 
                                                        {{ !$semuaStokCukup ? 'disabled title="Stok tidak mencukupi untuk di-ACC"' : '' }}>
                                                    <i class="bi bi-check-lg me-1"></i> ACC
                                                </button>

                                                <!-- Tombol Tolak -->
                                                <button type="button" class="btn btn-sm btn-outline-danger px-2" 
                                                        data-bs-toggle="modal" data-bs-target="#modalReject{{ $item->id }}">
                                                    <i class="bi bi-x-lg me-1"></i> Tolak
                                                </button>
                                            </div>

                                            <!-- Modal ACC -->
                                            <div class="modal fade text-start" id="modalAcc{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <form action="{{ route('peminjaman.approve', $item->id) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-header bg-success text-white">
                                                                <h6 class="modal-title fw-bold">Konfirmasi ACC Peminjaman</h6>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body p-4">
                                                                <p class="mb-2">Anda akan menyetujui peminjaman untuk:</p>
                                                                <div class="p-3 bg-light rounded-3 mb-3 border">
                                                                    <strong>{{ $item->mahasiswa->nama }}</strong> ({{ $item->mahasiswa->nim }})<br>
                                                                    <small class="text-muted">Kode Transaksi: {{ $item->kode_transaksi }}</small>
                                                                </div>
                                                                <div class="alert alert-info py-2 small mb-3">
                                                                    <i class="bi bi-info-circle me-1"></i> Stok alat yang diminta akan otomatis dipotong secara atomik dari inventaris.
                                                                </div>
                                                                <div class="mb-0">
                                                                    <label class="form-label small fw-semibold">Catatan Petugas (Opsional):</label>
                                                                    <input type="text" name="catatan_admin" class="form-control form-control-sm" placeholder="Contoh: Disetujui, alat siap diambil di Meja Lab 2">
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

                                            <!-- Modal Tolak -->
                                            <div class="modal fade text-start" id="modalReject{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <form action="{{ route('peminjaman.reject', $item->id) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-header bg-danger text-white">
                                                                <h6 class="modal-title fw-bold">Tolak Permohonan Peminjaman</h6>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body p-4">
                                                                <p class="mb-2">Apakah Anda yakin ingin menolak permohonan dari <strong>{{ $item->mahasiswa->nama }}</strong>?</p>
                                                                <div class="mb-0">
                                                                    <label class="form-label small fw-semibold text-danger">Alasan Penolakan <span class="text-danger">*</span></label>
                                                                    <textarea name="catatan_admin" class="form-control" rows="3" placeholder="Contoh: Alat sedang dalam jadwal pemeliharaan berkala / kegiatan praktikum kelas lain..." required></textarea>
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
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
