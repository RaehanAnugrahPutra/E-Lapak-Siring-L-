<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::create('penyewaans', function (Blueprint $table) {
            $table->id();

            // Relasi ke tabel pengajuans
            $table->foreignId('pengajuan_id')
                  ->constrained('pengajuans')
                  ->onDelete('cascade');

            // Relasi ke tabel users (penyewa)
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Relasi ke tabel stands
            $table->foreignId('stand_id')
                  ->constrained('stands')
                  ->onDelete('cascade');

            // Tanggal mulai dan selesai sewa
            $table->date('tanggal_mulai_sewa');
            $table->date('tanggal_selesai_sewa');

            // Harga sewa per 30 hari (disalin dari tabel stands saat approve pengajuan)
            $table->unsignedBigInteger('harga_sewa');

            // Durasi sewa dalam HARI
            $table->unsignedInteger('durasi_sewa');

            // Total yang harus dibayar untuk seluruh durasi sewa
            $table->unsignedBigInteger('total_pembayaran');

            // Status sewa: menunggu pembayaran, aktif, selesai, dibatalkan
            $table->enum('status_sewa', ['menunggu pembayaran', 'aktif', 'selesai', 'dibatalkan'])
                  ->default('menunggu pembayaran');

            // Metode pembayaran yang dipilih penyewa: QRIS atau VA (boleh kosong sebelum dipilih)
            $table->enum('metode_pembayaran', ['qris', 'va'])->nullable();

            // Nomor VA (8 digit) jika metode VA
            $table->string('va_number', 8)->nullable();

            // Payload QRIS (string unik) jika metode QRIS
            $table->string('qris_payload', 255)->nullable();

            // Status pembayaran: menunggu pembayaran, berhasil, gagal
            $table->enum('status_pembayaran', ['menunggu pembayaran', 'berhasil', 'gagal'])
                  ->default('menunggu pembayaran');

            // Waktu pembayaran berhasil (boleh kosong kalau belum bayar)
            $table->timestamp('waktu_pembayaran')->nullable();

            // Terakhir kali dikirim pengingat bayar (boleh null = belum pernah dikirimi)
            $table->timestamp('last_notified_at')->nullable();

            // Alasan pembatalan sewa oleh admin (boleh null kalau belum dibatalkan)
            $table->text('alasan_pembatalan')->nullable();

            // created_at dan updated_at
            $table->timestamps();
        });
    }

    /**
     * Membatalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyewaans');
    }
};
