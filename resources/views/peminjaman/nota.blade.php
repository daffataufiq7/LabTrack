<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Transaksi {{ $peminjaman->kode_transaksi }} — LabTrack</title>
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --primary: #1e3a8a;
            --primary-light: #3b82f6;
            --dark: #0f172a;
            --gray-border: #e2e8f0;
            --gray-bg: #f8fafc;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            line-height: 1.5;
            padding: 24px;
        }

        .action-bar {
            max-width: 800px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: var(--primary);
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
        }

        .btn-outline {
            background-color: #ffffff;
            color: #475569;
            border: 1px solid var(--gray-border);
        }
        .btn-outline:hover {
            background-color: #f8fafc;
        }

        .receipt-card {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            padding: 40px;
            border: 1px solid var(--gray-border);
        }

        /* Kop Surat */
        .kop-surat {
            display: flex;
            align-items: center;
            gap: 20px;
            padding-bottom: 20px;
            border-bottom: 3px double #334155;
            margin-bottom: 24px;
        }

        .kop-logo {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            font-weight: 800;
        }

        .kop-teks h2 {
            font-size: 1.15rem;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kop-teks h1 {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--primary);
            text-transform: uppercase;
        }

        .kop-teks p {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* Header Transaksi */
        .trx-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            background-color: var(--gray-bg);
            border: 1px solid var(--gray-border);
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 24px;
        }

        .meta-col {
            font-size: 0.85rem;
        }

        .meta-row {
            display: flex;
            margin-bottom: 6px;
        }

        .meta-row:last-child {
            margin-bottom: 0;
        }

        .meta-label {
            width: 140px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .meta-value {
            font-weight: 600;
            color: #0f172a;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        .badge-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-dipinjam { background: #fef3c7; color: #92400e; }
        .badge-dikembalikan { background: #dcfce7; color: #166534; }
        .badge-dibatalkan { background: #fee2e2; color: #991b1b; }

        /* Tabel Rincian */
        .section-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        table.trx-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
            margin-bottom: 24px;
        }

        table.trx-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 700;
            text-align: left;
            padding: 10px 12px;
            border-top: 1px solid var(--gray-border);
            border-bottom: 2px solid var(--gray-border);
        }

        table.trx-table td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--gray-border);
            vertical-align: middle;
        }

        table.trx-table tr:last-child td {
            border-bottom: 2px solid var(--gray-border);
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* Ringkasan Finansial */
        .finance-summary {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 28px;
        }

        .summary-box {
            width: 340px;
            background: #ffffff;
            border: 1px solid var(--gray-border);
            border-radius: 8px;
            padding: 14px 18px;
            font-size: 0.85rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .summary-row.total {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 2px dashed var(--gray-border);
            font-size: 1rem;
            font-weight: 800;
            color: var(--primary);
        }

        /* Ketentuan */
        .terms-box {
            font-size: 0.75rem;
            color: var(--text-muted);
            background: var(--gray-bg);
            border: 1px solid var(--gray-border);
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 30px;
        }

        .terms-box ol {
            padding-left: 18px;
            margin-top: 4px;
        }

        /* Tanda Tangan */
        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            text-align: center;
            font-size: 0.85rem;
            margin-top: 30px;
        }

        .signature-box {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .signature-space {
            height: 70px;
        }

        .signature-name {
            font-weight: 700;
            color: #0f172a;
            border-bottom: 1px solid #334155;
            padding-bottom: 2px;
            min-width: 180px;
        }

        .signature-role {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* Print Media */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .action-bar {
                display: none !important;
            }
            .receipt-card {
                box-shadow: none;
                border: none;
                padding: 0;
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- Bar Aksi (Hanya di Layar) -->
    <div class="action-bar">
        <a href="{{ route('peminjaman.show', $peminjaman->id) }}" class="btn btn-outline">
            <i class="bi bi-arrow-left"></i> Kembali ke Detail
        </a>
        <div style="display: flex; gap: 10px;">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Cetak Dokumen / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Lembar Nota Resmi -->
    <div class="receipt-card">
        <!-- Kop Surat -->
        <div class="kop-surat">
            <div class="kop-logo">
                <i class="bi bi-cpu"></i>
            </div>
            <div class="kop-teks">
                <h2>Laboratorium Terpadu Komputer & Instrumentasi</h2>
                <h1>LabTrack — Bukti Sirkulasi Peminjaman Alat</h1>
                <p>Gedung Laboratorium Kampus • Kontak: (0274) 586168 • Email: lab@kampus.ac.id</p>
            </div>
        </div>

        <!-- Meta Data Transaksi & Mahasiswa -->
        <div class="trx-meta">
            <div class="meta-col">
                <div class="meta-row">
                    <span class="meta-label">No. Transaksi</span>
                    <span class="meta-value font-mono">{{ $peminjaman->kode_transaksi }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Nama Peminjam</span>
                    <span class="meta-value">{{ $peminjaman->mahasiswa->nama }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">NIM</span>
                    <span class="meta-value font-mono">{{ $peminjaman->mahasiswa->nim }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Program Studi</span>
                    <span class="meta-value">{{ $peminjaman->mahasiswa->program_studi }}</span>
                </div>
            </div>
            <div class="meta-col">
                <div class="meta-row">
                    <span class="meta-label">Tanggal Pinjam</span>
                    <span class="meta-value">{{ $peminjaman->tanggal_pinjam->translatedFormat('d F Y') }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Jatuh Tempo</span>
                    <span class="meta-value">{{ $peminjaman->tanggal_jatuh_tempo->translatedFormat('d F Y') }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Tanggal Kembali</span>
                    <span class="meta-value">
                        {{ $peminjaman->tanggal_kembali ? $peminjaman->tanggal_kembali->translatedFormat('d F Y') : '— Belum Dikembalikan —' }}
                    </span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Status Transaksi</span>
                    <span class="meta-value">
                        <span class="badge-status badge-{{ $peminjaman->status }}">
                            {{ strtoupper($peminjaman->status) }}
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Rincian Alat -->
        <div class="section-title">
            <i class="bi bi-box-seam"></i> Rincian Alat Laboratorium yang Dipinjam
        </div>
        <table class="trx-table">
            <thead>
                <tr>
                    <th style="width: 40px;" class="text-center">No</th>
                    <th style="width: 130px;">Kode Alat</th>
                    <th>Nama Instrumen / Alat</th>
                    <th style="width: 70px;" class="text-center">Jumlah</th>
                    <th>Kondisi Pengembalian</th>
                    <th style="width: 130px;" class="text-right">Biaya Rusak</th>
                </tr>
            </thead>
            <tbody>
                @foreach($peminjaman->detailPeminjaman as $index => $detail)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-mono">
                        {{ $detail->alat->kode_alat }}
                        @if($detail->kode_unit_dipinjam)
                            <div style="font-size: 0.7rem; color: #1e40af; margin-top: 3px; font-weight: 600;">
                                Unit: {{ $detail->kode_unit_dipinjam }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $detail->alat->nama_alat }}</strong>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $detail->alat->kategori }}</div>
                    </td>
                    <td class="text-center"><strong>{{ $detail->jumlah }}</strong> Unit</td>
                    <td>
                        @if($detail->kondisi_kembali)
                            {{ $detail->kondisi_kembali }}
                        @else
                            <span style="color: var(--text-muted); font-style: italic;">Sedang Digunakan</span>
                        @endif
                    </td>
                    <td class="text-right font-mono">
                        Rp {{ number_format($detail->biaya_perbaikan, 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Ringkasan Finansial -->
        @php
            $totalTagihan = $peminjaman->total_denda + $peminjaman->total_biaya_perbaikan;
            $hariTelat = $peminjaman->hitungHariTerlambat();
            $hariKenaDenda = $peminjaman->hitungHariKenaDenda();
        @endphp

        <div class="finance-summary">
            <div class="summary-box">
                <div class="summary-row">
                    <span>Hari Terlambat:</span>
                    <span><strong>{{ $hariTelat }}</strong> hari</span>
                </div>
                <div class="summary-row">
                    <span>Hari Kena Denda (setelah -3 hari):</span>
                    <span><strong>{{ $hariKenaDenda }}</strong> hari</span>
                </div>
                <div class="summary-row">
                    <span>Denda Keterlambatan:</span>
                    <span class="font-mono">Rp {{ number_format($peminjaman->total_denda, 0, ',', '.') }}</span>
                </div>
                <div class="summary-row">
                    <span>Biaya Perbaikan Fisik:</span>
                    <span class="font-mono">Rp {{ number_format($peminjaman->total_biaya_perbaikan, 0, ',', '.') }}</span>
                </div>
                <div class="summary-row total">
                    <span>TOTAL KEWAJIBAN:</span>
                    <span class="font-mono">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</span>
                </div>
                @if($peminjaman->status === 'dikembalikan')
                <div class="summary-row" style="margin-top: 8px; padding-top: 8px; border-top: 1px dashed #cbd5e1;">
                    <span>Status Pembayaran:</span>
                    <span style="font-weight: 700; color: {{ $peminjaman->isLunas() ? '#16a34a' : '#dc2626' }};">
                        {{ $peminjaman->isLunas() ? 'LUNAS (BEBAS TANGGUNGAN)' : 'BELUM LUNAS (MENUNGGAK)' }}
                    </span>
                </div>
                @if($peminjaman->isLunas() && $peminjaman->metode_pembayaran)
                <div class="summary-row">
                    <span>Kanal / Metode Bayar:</span>
                    <span>{{ $peminjaman->metode_pembayaran }}</span>
                </div>
                @endif
                @endif
            </div>
        </div>

        <!-- Ketentuan Operasional -->
        <div class="terms-box">
            <strong>Ketentuan Sirkulasi Peminjaman Laboratorium:</strong>
            <ol>
                <li>Masa toleransi keterlambatan adalah 3 hari kalender setelah tanggal jatuh tempo tanpa pengenaan denda.</li>
                <li>Keterlambatan melebihi 3 hari dikenakan denda administratif sebesar Rp2.000 / hari.</li>
                <li>Alat yang dikembalikan dalam kondisi rusak dikenakan biaya perbaikan Rp10.000 / unit.</li>
                <li>Nota ini sah sebagai bukti peminjaman dan bukti bebas tanggungan laboratorium setelah ditandatangani.</li>
            </ol>
        </div>

        <!-- Tanda Tangan -->
        <div class="signature-grid">
            <div class="signature-box">
                <div>Yogyakarta, {{ now()->translatedFormat('d F Y') }}</div>
                <div>Mahasiswa Peminjam,</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $peminjaman->mahasiswa->nama }}</div>
                <div class="signature-role">NIM. {{ $peminjaman->mahasiswa->nim }}</div>
            </div>
            <div class="signature-box">
                <div>Mengetahui & Menyetujui,</div>
                <div>Petugas Laboratorium,</div>
                <div class="signature-space"></div>
                <div class="signature-name">( Petugas Jaga Lab )</div>
                <div class="signature-role">NIP / ID Petugas Laboratorium</div>
            </div>
        </div>
    </div>

</body>
</html>
