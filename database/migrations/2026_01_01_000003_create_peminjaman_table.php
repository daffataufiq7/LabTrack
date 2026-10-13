<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi', 40)->unique();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('restrict');
            $table->date('tanggal_pinjam');
            $table->date('tanggal_jatuh_tempo');
            $table->date('tanggal_kembali')->nullable();
            $table->enum('status', ['dipinjam', 'dikembalikan', 'dibatalkan'])->default('dipinjam');
            $table->decimal('total_denda', 12, 2)->default(0);
            $table->decimal('total_biaya_perbaikan', 12, 2)->default(0);
            $table->timestamps();

            $table->index('tanggal_pinjam');
            $table->index('tanggal_jatuh_tempo');
            $table->index('tanggal_kembali');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
    }
};
