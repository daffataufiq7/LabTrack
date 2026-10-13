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
        Schema::create('alat', function (Blueprint $table) {
            $table->id();
            $table->string('kode_alat', 30)->unique();
            $table->string('nama_alat', 150);
            $table->string('kategori', 100);
            $table->unsignedInteger('stok_total');
            $table->unsignedInteger('stok_tersedia');
            $table->string('kondisi', 50)->default('Baik');
            $table->timestamps();

            $table->index('nama_alat');
            $table->index('kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alat');
    }
};
