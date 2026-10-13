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
        Schema::create('detail_peminjaman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peminjaman_id')->constrained('peminjaman')->onDelete('cascade');
            $table->foreignId('alat_id')->constrained('alat')->onDelete('restrict');
            $table->unsignedInteger('jumlah');
            $table->string('kondisi_kembali', 50)->nullable();
            $table->decimal('biaya_perbaikan', 12, 2)->default(0);
            $table->timestamps();

            $table->index('peminjaman_id');
            $table->index('alat_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_peminjaman');
    }
};
