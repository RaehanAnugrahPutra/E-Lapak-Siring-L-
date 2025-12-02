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
        Schema::create('pengajuans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pengaju');
            $table->string('no_hp');
            
            //tanggal mulai dan selesai sewa
            $table->date('tanggal_mulai_sewa');
            $table->date('tanggal_selesai_sewa');

            $table->string('surat_pengajuan');

            // status pengajuan: pending, disetujui, ditolak
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');

            // berelasi dengan tabel users
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // berelasi dengan tabel stands
            $table->foreignId('stand_id')->constrained('stands')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuans');
    }
};
