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
        Schema::create('alat_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alat_id')->constrained('alat')->onDelete('cascade');
            $table->string('kode_unit', 50)->unique();
            $table->string('nomor_seri', 100)->nullable();
            $table->enum('status', ['tersedia', 'dipinjam', 'rusak', 'maintenance'])->default('tersedia');
            $table->string('kondisi', 100)->default('Baik');
            $table->foreignId('peminjaman_id')->nullable()->constrained('peminjaman')->onDelete('set null');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index('alat_id');
            $table->index('status');
            $table->index('peminjaman_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alat_unit');
    }
};
