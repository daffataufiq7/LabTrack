<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Peminjaman Alat Laboratorium — LabTrack</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background: #fff;
            padding: 20px;
        }
        .header-kop {
            border-bottom: 3px double #000;
            padding-bottom: 12px;
            margin-bottom: 24px;
            text-align: center;
        }
        .header-kop h4 {
            font-weight: bold;
            margin-bottom: 2px;
            text-transform: uppercase;
        }
        .header-kop p {
            margin-bottom: 0;
            font-size: 0.9rem;
        }
        table {
            font-size: 0.85rem;
        }
        .table-bordered th, .table-bordered td {
            border-color: #333 !important;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Tombol Tindakan Cetak -->
    <div class="no-print mb-4 d-flex justify-content-between align-items-center p-3 bg-light border rounded">
        <div>
            <h6 class="mb-0 fw-bold">Pratinjau Cetak Dokumen Laporan Laboratorium</h6>
            <small class="text-muted">Klik tombol di sebelah kanan untuk mencetak atau simpan sebagai PDF.</small>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                Cetak Dokumen (Ctrl+P)
            </button>
            <button onclick="window.close()" class="btn btn-secondary btn-sm">
                Tutup Jendela
            </button>
        </div>
    </div>

    <!-- Kop Dokumen Resmi -->
    <div class="header-kop">
        <h4>LABORATORIUM TEKNOLOGI INFORMASI & KOMPUTASI</h4>
        <p>Sistem Manajemen & Sirkulasi Inventaris Alat Laboratorium Terpadu (LabTrack)</p>
        <p class="small text-muted">Jalan Pendidikan Kampus Terpadu &bull; Telp: (0274) 555-1234 &bull; Email: lab@universitas.ac.id</p>
    </div>

    <div class="text-center mb-4">
        <h5 class="fw-bold text-decoration-underline mb-1">LAPORAN REKAPITULASI PEMINJAMAN ALAT & KEUANGAN</h5>
        <div class="small text-muted">
            Periode: 
            <strong>{{ $dariTanggal ? \Carbon\Carbon::parse($dariTanggal)->translatedFormat('d M Y') : 'Awal' }}</strong> 
            s/d 
            <strong>{{ $sampaiTanggal ? \Carbon\Carbon::parse($sampaiTanggal)->translatedFormat('d M Y') : 'Sekarang' }}</strong>
            &bull; Status Filter: <strong>{{ ucfirst($statusFilter) }}</strong>
        </div>
    </div>

    <!-- Ringkasan Finansial -->
    <div class="row g-2 mb-3">
        <div class="col-4">
            <div class="p-2 border rounded">
                <small class="d-block text-muted">Total Transaksi</small>
                <strong>{{ $laporan->count() }} Data</strong>
            </div>
        </div>
        <div class="col-4">
            <div class="p-2 border rounded">
                <small class="d-block text-muted">Total Denda Terkumpul</small>
                <strong class="text-dark">Rp {{ number_format($totalDenda, 0, ',', '.') }}</strong>
            </div>
        </div>
        <div class="col-4">
            <div class="p-2 border rounded">
                <small class="d-block text-muted">Total Biaya Perbaikan</small>
                <strong class="text-dark">Rp {{ number_format($totalBiayaPerbaikan, 0, ',', '.') }}</strong>
            </div>
        </div>
    </div>

    <!-- Tabel Data Laporan -->
    <table class="table table-bordered table-sm align-middle">
        <thead class="table-light text-center">
            <tr>
                <th style="width: 30px;">No</th>
                <th>Kode TRX</th>
                <th>Nama Mahasiswa (NIM)</th>
                <th>Tgl Pinjam</th>
                <th>Jatuh Tempo</th>
                <th>Tgl Kembali</th>
                <th>Alat yang Dipinjam</th>
                <th>Status</th>
                <th>Denda</th>
                <th>Perbaikan</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporan as $idx => $trx)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-monospace fw-bold">{{ $trx->kode_transaksi }}</td>
                    <td>
                        {{ $trx->mahasiswa->nama }}<br>
                        <small class="text-muted">{{ $trx->mahasiswa->nim }}</small>
                    </td>
                    <td class="text-center">{{ $trx->tanggal_pinjam->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $trx->tanggal_jatuh_tempo->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $trx->tanggal_kembali ? $trx->tanggal_kembali->format('d/m/Y') : '-' }}</td>
                    <td>
                        @foreach($trx->detailPeminjaman as $d)
                            <div>&bull; {{ $d->alat->nama_alat }} ({{ $d->jumlah }} unit)</div>
                        @endforeach
                    </td>
                    <td class="text-center text-uppercase fw-semibold" style="font-size: 0.75rem;">
                        {{ $trx->status }}
                    </td>
                    <td class="text-end">Rp {{ number_format($trx->total_denda, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($trx->total_biaya_perbaikan, 0, ',', '.') }}</td>
                    <td class="text-end fw-bold">Rp {{ number_format($trx->total_denda + $trx->total_biaya_perbaikan, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center py-3">Tidak ada data untuk rentang filter ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot class="table-light fw-bold">
            <tr>
                <td colspan="8" class="text-end">TOTAL KESELURUHAN:</td>
                <td class="text-end">Rp {{ number_format($totalDenda, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($totalBiayaPerbaikan, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($totalDenda + $totalBiayaPerbaikan, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Tanda Tangan & Pengesahan -->
    <div class="row mt-5 pt-3">
        <div class="col-7">
            <div class="small">
                <strong>Catatan Aturan Laboratorium:</strong>
                <ol class="ps-3 mb-0" style="font-size: 0.8rem;">
                    <li>Masa tenggang keterlambatan: 3 hari dari tanggal jatuh tempo.</li>
                    <li>Tarif denda hari keterlambatan: Rp2.000 / hari yang dikenai denda.</li>
                    <li>Tarif biaya penggantian / perbaikan unit alat rusak: Rp10.000 / unit.</li>
                </ol>
            </div>
        </div>
        <div class="col-5 text-center">
            <div class="small mb-1">Dicetak pada: {{ date('d F Y, H:i') }} WIB</div>
            <div class="small fw-bold mb-5">Kepala Laboratorium Komputer & TI</div>
            <br><br>
            <div class="fw-bold text-decoration-underline">( Dr. Ir. H. Bambang Subagyo, M.Kom. )</div>
            <div class="small text-muted">NIP. 19780512 200312 1 002</div>
        </div>
    </div>
</body>
</html>
