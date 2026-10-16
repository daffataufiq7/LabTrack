@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<!-- Header Sambutan & Widget Tanggal (Screen 2) -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Selamat Datang, Admin Lab!</h4>
        <p class="text-muted small mb-0">Berikut ringkasan aktivitas peminjaman alat laboratorium hari ini.</p>
    </div>
    <div class="d-inline-flex align-items-center gap-2 bg-white px-3 py-2 rounded-3 border text-secondary shadow-sm" style="font-size: 0.82rem;">
        <i class="bi bi-calendar3 text-primary"></i>
        <span>{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</span>
        <span class="text-muted opacity-50">|</span>
        <span class="fw-medium text-dark">Laboratorium Teknik Informatika</span>
    </div>
</div>

<!-- 4 Kartu Statistik Utama (Screen 2) -->
<div class="row g-3 mb-4">
    <!-- 1. Total Mahasiswa -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card-custom">
            <div>
                <span class="text-muted small d-block mb-1">Total Mahasiswa</span>
                <h3 class="fw-bold mb-1 text-dark">{{ number_format($totalMahasiswa) }}</h3>
                <small class="text-success fw-semibold" style="font-size: 0.75rem;">
                    <i class="bi bi-arrow-up-short"></i> 12% <span class="text-muted fw-normal">dari bulan lalu</span>
                </small>
            </div>
            <div class="stat-icon-wrapper" style="background-color: #eff6ff; color: #2563eb;">
                <i class="bi bi-person"></i>
            </div>
        </div>
    </div>

    <!-- 2. Total Alat -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card-custom">
            <div>
                <span class="text-muted small d-block mb-1">Total Alat</span>
                <h3 class="fw-bold mb-1 text-dark">{{ number_format($totalJenisAlat) }}</h3>
                <small class="text-success fw-semibold" style="font-size: 0.75rem;">
                    <i class="bi bi-arrow-up-short"></i> 5% <span class="text-muted fw-normal">dari bulan lalu</span>
                </small>
            </div>
            <div class="stat-icon-wrapper" style="background-color: #fefce8; color: #ca8a04;">
                <i class="bi bi-box-seam"></i>
            </div>
        </div>
    </div>

    <!-- 3. Total Peminjaman -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card-custom">
            <div>
                <span class="text-muted small d-block mb-1">Total Peminjaman</span>
                <h3 class="fw-bold mb-1 text-dark">{{ number_format($totalPeminjaman) }}</h3>
                <small class="text-success fw-semibold" style="font-size: 0.75rem;">
                    <i class="bi bi-arrow-up-short"></i> 18% <span class="text-muted fw-normal">dari bulan lalu</span>
                </small>
            </div>
            <div class="stat-icon-wrapper" style="background-color: #f0fdf4; color: #16a34a;">
                <i class="bi bi-file-earmark-text"></i>
            </div>
        </div>
    </div>

    <!-- 4. Total Denda -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card-custom">
            <div>
                <span class="text-muted small d-block mb-1">Total Denda</span>
                <h3 class="fw-bold mb-1 text-dark">Rp {{ number_format($totalDenda, 0, ',', '.') }}</h3>
                <small class="text-success fw-semibold" style="font-size: 0.75rem;">
                    <i class="bi bi-arrow-up-short"></i> 30% <span class="text-muted fw-normal">dari bulan lalu</span>
                </small>
            </div>
            <div class="stat-icon-wrapper" style="background-color: #fef2f2; color: #dc2626;">
                <i class="bi bi-cash"></i>
            </div>
        </div>
    </div>
</div>

<!-- Middle Section: Line Chart, Donut Chart, & Peminjaman Terbaru (Screen 2) -->
<div class="row g-3">
    <!-- Chart Tren Peminjaman Alat (7 Hari Terakhir) -->
    <div class="col-12 col-xl-5">
        <div class="card h-100 p-3 p-md-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold text-dark mb-0" style="font-size: 0.95rem;">Tren Peminjaman Alat (7 Hari Terakhir)</h6>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle py-1 px-2" style="font-size: 0.75rem;" type="button" data-bs-toggle="dropdown">
                        7 Hari Terakhir
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm small">
                        <li><a class="dropdown-item active" href="#">7 Hari Terakhir</a></li>
                        <li><a class="dropdown-item" href="#">30 Hari Terakhir</a></li>
                        <li><a class="dropdown-item" href="#">Bulan Ini</a></li>
                    </ul>
                </div>
            </div>
            <div class="flex-grow-1" style="min-height: 240px; position: relative;">
                <canvas id="trendPeminjamanChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart Status Ketersediaan & Kondisi Fisik Alat (Donut) -->
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card h-100 p-3 p-md-4">
            <h6 class="fw-bold text-dark mb-1" style="font-size: 0.95rem;">Kondisi & Status Alat</h6>
            <small class="text-muted d-block mb-3" style="font-size: 0.72rem;">Ketersediaan unit fisik real-time</small>
            <div class="d-flex flex-column align-items-center justify-content-center flex-grow-1">
                <div style="width: 150px; height: 150px; position: relative;">
                    <canvas id="kategoriDonutChart"></canvas>
                </div>
                <!-- Legend items with unit count & percentages -->
                <div class="w-100 mt-3 pt-2 border-top">
                    @foreach($donutLabels as $index => $label)
                        <div class="d-flex align-items-center justify-content-between mb-1" style="font-size: 0.75rem;">
                            <div class="d-flex align-items-center gap-2 text-truncate" style="max-width: 140px;">
                                <span class="rounded-circle d-inline-block flex-shrink-0" style="width: 8px; height: 8px; background-color: {{ $donutColors[$index % count($donutColors)] }};"></span>
                                <span class="text-secondary text-truncate">{{ $label }}</span>
                            </div>
                            <div class="text-end">
                                <span class="text-muted" style="font-size: 0.72rem;">{{ $donutValues[$index] }} unit</span>
                                <span class="fw-bold text-dark ms-1">({{ $donutPercentages[$index] ?? 0 }}%)</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Peminjaman Terbaru (List) -->
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card h-100 p-3 p-md-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold text-dark mb-0" style="font-size: 0.95rem;">Peminjaman Terbaru</h6>
                <a href="{{ route('peminjaman.index') }}" class="text-decoration-none text-danger small fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.78rem;">
                    Lihat Semua <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="d-flex flex-column gap-3">
                @forelse($transaksiTerbaru as $t)
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <!-- Avatar Initial -->
                            <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary fw-bold d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                {{ strtoupper(substr($t->mahasiswa->nama ?? 'M', 0, 1)) }}
                            </div>
                            <div>
                                <span class="fw-semibold text-dark d-block lh-sm" style="font-size: 0.82rem;">
                                    {{ Str::limit($t->mahasiswa->nama ?? 'Mahasiswa', 16) }}
                                </span>
                                <small class="text-muted d-block" style="font-size: 0.72rem;">
                                    {{ Str::limit($t->detailPeminjaman->first()->alat->nama_alat ?? 'Alat Lab', 18) }}
                                </small>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="text-secondary small" style="font-size: 0.72rem;">
                                {{ \Carbon\Carbon::parse($t->tanggal_pinjam)->isoFormat('DD MMM Y') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small text-center my-4">Belum ada transaksi peminjaman.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Line Chart: Tren Peminjaman Alat
    const ctxTrend = document.getElementById('trendPeminjamanChart');
    if (ctxTrend) {
        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: {!! json_encode($trendLabels) !!},
                datasets: [{
                    label: 'Peminjaman',
                    data: {!! json_encode($trendValues) !!},
                    borderColor: '#1e40af',
                    backgroundColor: 'rgba(30, 64, 175, 0.08)',
                    borderWidth: 2,
                    tension: 0.35,
                    fill: true,
                    pointBackgroundColor: '#1e40af',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#111c2e',
                        titleFont: { family: 'Plus Jakarta Sans', size: 12 },
                        bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                        padding: 10,
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Plus Jakarta Sans', size: 10 }, color: '#94a3b8' }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 5, font: { family: 'Plus Jakarta Sans', size: 10 }, color: '#94a3b8' },
                        grid: { color: '#f1f5f9' }
                    }
                }
            }
        });
    }

    // 2. Donut Chart: Kategori Alat
    const ctxDonut = document.getElementById('kategoriDonutChart');
    if (ctxDonut) {
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($donutLabels) !!},
                datasets: [{
                    data: {!! json_encode($donutValues) !!},
                    backgroundColor: {!! json_encode($donutColors) !!},
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#111c2e',
                        titleFont: { family: 'Plus Jakarta Sans', size: 12 },
                        bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const val = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                                return ` ${val} unit (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
