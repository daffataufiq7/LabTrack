@extends('layouts.app')

@section('title', 'Proses Pengembalian ' . $peminjaman->kode_transaksi)
@section('page_title', 'Form Pengembalian Alat Laboratorium')
@section('page_subtitle', 'Pemeriksaan kondisi fisik alat, pemulihan stok layak pakai, dan kalkulasi denda & biaya perbaikan')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <form action="{{ route('pengembalian.process', $peminjaman->id) }}" method="POST" id="formPengembalian">
            @csrf

            <!-- Card 1: Identitas Transaksi & Peminjam -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fs-6 fw-bold font-monospace text-primary">{{ $peminjaman->kode_transaksi }}</span>
                        <span class="badge bg-warning text-dark">Proses Pengembalian</span>
                    </div>
                    <a href="{{ route('pengembalian.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Mahasiswa Peminjam</small>
                            <div class="fw-bold text-dark mt-1">{{ $peminjaman->mahasiswa->nama }}</div>
                            <small class="text-muted">NIM: {{ $peminjaman->mahasiswa->nim }} &bull; {{ $peminjaman->mahasiswa->program_studi }}</small>
                        </div>
                        <div class="col-6 col-md-4">
                            <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Tanggal Pinjam</small>
                            <div class="fw-semibold text-dark mt-1">{{ $peminjaman->tanggal_pinjam->translatedFormat('d F Y') }}</div>
                        </div>
                        <div class="col-6 col-md-4">
                            <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Batas Jatuh Tempo</small>
                            <div class="fw-semibold text-danger mt-1">{{ $peminjaman->tanggal_jatuh_tempo->translatedFormat('d F Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Toolbar Simulasi Demo Presentasi Dosen -->
            <div class="card border-primary border-opacity-25 bg-primary bg-opacity-10 shadow-sm mb-4">
                <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="bi bi-magic fs-5"></i>
                        </div>
                        <div>
                            <strong class="text-primary d-block small">Preset Simulasi Demo Pengujian Dosen (Live Demo):</strong>
                            <span class="text-muted" style="font-size: 0.75rem;">Klik tombol di samping untuk membuktikan akurasi aturan bisnis secara instan:</span>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-outline-success bg-white shadow-sm" id="btnPresetTepatWaktu">
                            <i class="bi bi-check-circle me-1"></i> Tepat Waktu (Denda Rp0)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info bg-white shadow-sm" id="btnPresetGracePeriod">
                            <i class="bi bi-clock-history me-1"></i> Telat 3 Hari (Grace Period Rp0)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning bg-white shadow-sm" id="btnPresetTelat4Hari">
                            <i class="bi bi-exclamation-triangle me-1"></i> Telat 4 Hari (Denda Rp2.000)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger bg-white shadow-sm" id="btnPresetTelat5Rusak">
                            <i class="bi bi-lightning-charge me-1"></i> Telat 5 Hari + 1 Rusak (Rp14.000)
                        </button>
                    </div>
                </div>
            </div>

            <!-- Card 2: Tanggal Pengembalian & Kalkulator Keterlambatan Real-time -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-calendar-check-fill text-primary me-2"></i>Tanggal Pengembalian & Perhitungan Denda
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-12 col-md-5">
                            <label for="tanggal_kembali" class="form-label">Tanggal Alat Dikembalikan <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_kembali" id="tanggal_kembali" class="form-control @error('tanggal_kembali') is-invalid @enderror" 
                                   value="{{ old('tanggal_kembali', $today) }}" 
                                   min="{{ $peminjaman->tanggal_pinjam->format('Y-m-d') }}" required>
                            @error('tanggal_kembali')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Tidak boleh sebelum tanggal pinjam ({{ $peminjaman->tanggal_pinjam->translatedFormat('d M Y') }}).</div>
                        </div>

                        <div class="col-12 col-md-7">
                            <div class="p-3 bg-light rounded-3 border" id="boxKalkulasiDenda">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted small">Status Waktu:</span>
                                    <span id="labelStatusWaktu" class="fw-bold">-</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted small">Hari Terlambat (Masa Tenggang 3 Hari):</span>
                                    <span id="labelHariDenda" class="fw-bold">-</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                    <strong class="text-dark small">Denda Keterlambatan (Rp2.000 / hari kena denda):</strong>
                                    <h6 class="fw-bold text-danger mb-0" id="labelTotalDenda">Rp 0</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Inspeksi Kondisi Fisik Alat yang Dikembalikan -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-shield-check text-primary me-2"></i>Inspeksi Fisik Alat & Restorasi Stok
                        </h6>
                        <small class="text-muted">Unit rusak dikenai biaya perbaikan Rp10.000/unit & <strong>TIDAK</strong> dipulihkan ke stok tersedia</small>
                    </div>
                    <span class="badge bg-light text-dark border">
                        {{ $peminjaman->detailPeminjaman->count() }} Jenis Alat
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 250px;">Nama & Kode Alat</th>
                                    <th style="width: 110px;" class="text-center">Dipinjam</th>
                                    <th style="width: 140px;" class="text-center">Jumlah Rusak</th>
                                    <th style="width: 140px;" class="text-center">Layak Dipulihkan</th>
                                    <th style="width: 150px;" class="text-end">Biaya Perbaikan</th>
                                    <th style="min-width: 200px;">Catatan Kerusakan/Kondisi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($peminjaman->detailPeminjaman as $detail)
                                    <tr class="baris-detail-inspeksi" data-id="{{ $detail->id }}" data-jumlah="{{ $detail->jumlah }}">
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $detail->alat->nama_alat }}</div>
                                            <div class="small text-muted font-monospace">{{ $detail->alat->kode_alat }}</div>
                                            @if($detail->kode_unit_dipinjam)
                                                <div class="mt-1 d-flex flex-wrap gap-1 align-items-center">
                                                    <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.7rem;">
                                                        <i class="bi bi-upc-scan text-primary me-1"></i>Kode Unit:
                                                    </span>
                                                    @foreach($detail->unit_list as $unitCode)
                                                        <span class="badge bg-primary-subtle text-primary border border-primary font-monospace" style="font-size: 0.72rem;">
                                                            {{ $unitCode }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary px-3 py-1 rounded-pill">{{ $detail->jumlah }} Unit</span>
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   name="detail[{{ $detail->id }}][jumlah_rusak]" 
                                                   class="form-control text-center input-rusak" 
                                                   min="0" 
                                                   max="{{ $detail->jumlah }}" 
                                                   value="{{ old("detail.{$detail->id}.jumlah_rusak", 0) }}" 
                                                   required>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success-subtle text-success border border-success badge-layak px-2 py-1">
                                                {{ $detail->jumlah }} Unit
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-danger label-biaya-item">Rp 0</span>
                                        </td>
                                        <td>
                                            <input type="text" 
                                                   name="detail[{{ $detail->id }}][kondisi_catatan]" 
                                                   class="form-control form-control-sm" 
                                                   placeholder="Catatan fisik jika ada kendala..." 
                                                   value="{{ old("detail.{$detail->id}.kondisi_catatan") }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Card 4: Ringkasan Total Tagihan Finansial -->
            <div class="card shadow-sm mb-4 border-primary border-opacity-50">
                <div class="card-body p-4 bg-light bg-opacity-50 rounded-3">
                    <div class="row align-items-center">
                        <div class="col-12 col-md-7">
                            <h6 class="fw-bold text-dark mb-1">Total Biaya & Pembayaran yang Harus Diselesaikan:</h6>
                            <ul class="list-unstyled small mb-0 text-muted">
                                <li>&bull; Denda Keterlambatan: <strong id="summaryDenda">Rp 0</strong></li>
                                <li>&bull; Biaya Perbaikan Alat Rusak: <strong id="summaryPerbaikan">Rp 0</strong></li>
                            </ul>
                        </div>
                        <div class="col-12 col-md-5 text-md-end mt-3 mt-md-0">
                            <span class="text-muted small d-block">Total Keseluruhan Tagihan:</span>
                            <h3 class="fw-bold text-danger mb-0" id="summaryTotalSemua">Rp 0</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 5: Metode & Status Pembayaran Kasir Lab -->
            <div class="card shadow-sm mb-4 border-secondary border-opacity-25" id="cardMetodeBayar">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-wallet2 text-primary me-2"></i>Penyelesaian & Metode Pembayaran Kasir Lab
                    </h6>
                    <span class="badge bg-success-subtle text-success border border-success" id="badgeStatusBayarInfo">Bebas Biaya (Otomatis Lunas)</span>
                </div>
                <div class="card-body p-4" id="boxFormPembayaran">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-dark">Status Pelunasan Tagihan <span class="text-danger">*</span></label>
                            <div class="d-flex flex-column gap-2 mt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status_pembayaran" id="statusLunas" value="lunas" checked>
                                    <label class="form-check-label small fw-semibold text-success" for="statusLunas">
                                        <i class="bi bi-check-circle-fill me-1"></i> Langsung Lunas (Dibayar saat Serah Terima di Lab)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status_pembayaran" id="statusBelumLunas" value="belum_lunas">
                                    <label class="form-check-label small fw-semibold text-danger" for="statusBelumLunas">
                                        <i class="bi bi-exclamation-circle-fill me-1"></i> Belum Lunas (Menunggak Denda)
                                    </label>
                                </div>
                            </div>
                            <div class="form-text small mt-2" style="font-size: 0.75rem;">
                                Catatan: Jika mahasiswa belum membayar, status tercatat sebagai <strong>Belum Lunas</strong> dan sistem otomatis memblokir peminjaman baru sampai denda diselesaikan.
                            </div>
                        </div>

                        <div class="col-12 col-md-6" id="wrapperMetodeBayar">
                            <label for="metode_pembayaran" class="form-label small fw-semibold text-dark">Kanal / Metode Pembayaran <span class="text-danger">*</span></label>
                            <select name="metode_pembayaran" id="metode_pembayaran" class="form-select">
                                <option value="Tunai (Cash Meja Lab)">Tunai (Cash di Meja Lab)</option>
                                <option value="QRIS Kas Laboratorium">QRIS Kas Laboratorium Terpadu</option>
                                <option value="Transfer Bank Kas Lab">Transfer Bank (Rekening Kas Lab)</option>
                            </select>
                            <div class="form-text small" style="font-size: 0.75rem;">
                                Pilih kanal pembayaran yang digunakan mahasiswa di meja laboratorium.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Konfirmasi Aksi -->
            <div class="d-flex align-items-center justify-content-end gap-2 mb-5">
                <a href="{{ route('pengembalian.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
                <button type="submit" class="btn btn-primary px-4 py-2 shadow-sm" id="btnKonfirmasiPengembalian">
                    <i class="bi bi-check2-circle me-1"></i> Simpan & Selesaikan Pengembalian
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tglKembaliInput = document.getElementById('tanggal_kembali');
    const tglTempoStr = "{{ $peminjaman->tanggal_jatuh_tempo->format('Y-m-d') }}";
    const tarifDendaPerHari = {{ \App\Models\Peminjaman::TARIF_DENDA_PER_HARI }};
    const masaTenggangHari = {{ \App\Models\Peminjaman::HARI_MASA_TENGGANG }};
    const biayaPerbaikanPerUnit = {{ \App\Models\Peminjaman::BIAYA_PERBAIKAN_PER_UNIT }};

    const labelStatusWaktu = document.getElementById('labelStatusWaktu');
    const labelHariDenda = document.getElementById('labelHariDenda');
    const labelTotalDenda = document.getElementById('labelTotalDenda');

    const summaryDenda = document.getElementById('summaryDenda');
    const summaryPerbaikan = document.getElementById('summaryPerbaikan');
    const summaryTotalSemua = document.getElementById('summaryTotalSemua');

    const badgeStatusBayarInfo = document.getElementById('badgeStatusBayarInfo');
    const statusLunasRadio = document.getElementById('statusLunas');
    const statusBelumLunasRadio = document.getElementById('statusBelumLunas');
    const wrapperMetodeBayar = document.getElementById('wrapperMetodeBayar');

    function formatRupiah(num) {
        return 'Rp ' + (num || 0).toLocaleString('id-ID');
    }

    function hitungDenda() {
        const tglKembali = new Date(tglKembaliInput.value + 'T00:00:00');
        const tglTempo = new Date(tglTempoStr + 'T00:00:00');

        const diffTime = tglKembali.getTime() - tglTempo.getTime();
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        let hariKenaDenda = 0;
        let denda = 0;

        if (diffDays <= 0) {
            labelStatusWaktu.className = 'badge bg-success-subtle text-success border border-success';
            labelStatusWaktu.textContent = 'Tepat Waktu (Tidak Terlambat)';
            labelHariDenda.textContent = '0 Hari';
        } else {
            hariKenaDenda = Math.max(0, diffDays - masaTenggangHari);
            denda = hariKenaDenda * tarifDendaPerHari;

            if (diffDays <= masaTenggangHari) {
                labelStatusWaktu.className = 'badge bg-info-subtle text-info border border-info';
                labelStatusWaktu.textContent = `Terlambat ${diffDays} Hari (Dalam Masa Tenggang 3 Hari)`;
                labelHariDenda.textContent = '0 Hari (Bebas Denda)';
            } else {
                labelStatusWaktu.className = 'badge bg-danger-subtle text-danger border border-danger';
                labelStatusWaktu.textContent = `Terlambat ${diffDays} Hari (Lewat Masa Tenggang)`;
                labelHariDenda.textContent = `${hariKenaDenda} Hari Kena Denda`;
            }
        }

        labelTotalDenda.textContent = formatRupiah(denda);
        summaryDenda.textContent = formatRupiah(denda);

        return denda;
    }

    function hitungKerusakan() {
        let totalBiayaKerusakan = 0;
        const rows = document.querySelectorAll('.baris-detail-inspeksi');

        rows.forEach(row => {
            const totalJumlah = parseInt(row.dataset.jumlah || 0);
            const inputRusak = row.querySelector('.input-rusak');
            let rusak = parseInt(inputRusak.value || 0);

            if (rusak < 0) rusak = 0;
            if (rusak > totalJumlah) rusak = totalJumlah;
            inputRusak.value = rusak;

            const layak = totalJumlah - rusak;
            const biayaItem = rusak * biayaPerbaikanPerUnit;
            totalBiayaKerusakan += biayaItem;

            row.querySelector('.badge-layak').textContent = layak + ' Unit';
            row.querySelector('.label-biaya-item').textContent = formatRupiah(biayaItem);
        });

        summaryPerbaikan.textContent = formatRupiah(totalBiayaKerusakan);
        return totalBiayaKerusakan;
    }

    function kalkulasiTotalSemua() {
        const denda = hitungDenda();
        const perbaikan = hitungKerusakan();
        const totalSemua = denda + perbaikan;
        summaryTotalSemua.textContent = formatRupiah(totalSemua);

        // Update bagian metode pembayaran
        if (totalSemua === 0) {
            badgeStatusBayarInfo.textContent = 'Bebas Biaya (Otomatis Lunas)';
            badgeStatusBayarInfo.className = 'badge bg-success-subtle text-success border border-success';
            statusLunasRadio.checked = true;
            statusBelumLunasRadio.disabled = true;
            wrapperMetodeBayar.style.opacity = '0.5';
            wrapperMetodeBayar.querySelector('select').disabled = true;
        } else {
            badgeStatusBayarInfo.textContent = 'Ada Tagihan Kasir: ' + formatRupiah(totalSemua);
            badgeStatusBayarInfo.className = 'badge bg-danger-subtle text-danger border border-danger';
            statusBelumLunasRadio.disabled = false;
            updateRadioPembayaran();
        }
    }

    function updateRadioPembayaran() {
        if (statusBelumLunasRadio.checked) {
            wrapperMetodeBayar.style.opacity = '0.5';
            wrapperMetodeBayar.querySelector('select').disabled = true;
        } else {
            wrapperMetodeBayar.style.opacity = '1';
            wrapperMetodeBayar.querySelector('select').disabled = false;
        }
    }

    statusLunasRadio.addEventListener('change', updateRadioPembayaran);
    statusBelumLunasRadio.addEventListener('change', updateRadioPembayaran);

    // Preset Demo Handlers untuk Pengujian Cepat Presentasi Dosen
    function setPreset(daysOffset, rusakCount) {
        const tempo = new Date(tglTempoStr + 'T00:00:00');
        tempo.setDate(tempo.getDate() + daysOffset);
        const yyyy = tempo.getFullYear();
        const mm = String(tempo.getMonth() + 1).padStart(2, '0');
        const dd = String(tempo.getDate()).padStart(2, '0');
        tglKembaliInput.value = `${yyyy}-${mm}-${dd}`;

        const inputsRusak = document.querySelectorAll('.input-rusak');
        inputsRusak.forEach((inp, idx) => {
            const maxVal = parseInt(inp.getAttribute('max') || 1);
            if (idx === 0) {
                inp.value = Math.min(rusakCount, maxVal);
            } else {
                inp.value = 0;
            }
        });

        kalkulasiTotalSemua();
    }

    const btnTepatWaktu = document.getElementById('btnPresetTepatWaktu');
    if (btnTepatWaktu) btnTepatWaktu.addEventListener('click', () => setPreset(0, 0));

    const btnGracePeriod = document.getElementById('btnPresetGracePeriod');
    if (btnGracePeriod) btnGracePeriod.addEventListener('click', () => setPreset(3, 0));

    const btnTelat4Hari = document.getElementById('btnPresetTelat4Hari');
    if (btnTelat4Hari) btnTelat4Hari.addEventListener('click', () => setPreset(4, 0));

    const btnTelat5Rusak = document.getElementById('btnPresetTelat5Rusak');
    if (btnTelat5Rusak) btnTelat5Rusak.addEventListener('click', () => setPreset(5, 1));

    // Event listener
    tglKembaliInput.addEventListener('change', kalkulasiTotalSemua);
    document.querySelectorAll('.input-rusak').forEach(input => {
        input.addEventListener('input', kalkulasiTotalSemua);
    });

    // Inisialisasi awal kalkulasi
    kalkulasiTotalSemua();
});
</script>
@endpush
