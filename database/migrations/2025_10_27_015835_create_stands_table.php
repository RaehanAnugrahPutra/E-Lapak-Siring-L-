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
        Schema::create('stands', function (Blueprint $table) {
            $table->id();
            //kode stand unique 3 huruf character
            $table->string('kode_stand', 3)->unique();

            //harga sewa per bulan 750 ribu
            $table->bigInteger('harga_sewa')->default(750000);

            //stand sedang kosong dan terisi
            $table->enum('status_stand', ['kosong', 'terisi', 'maintenance'])->default('kosong');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stands');
    }
};
