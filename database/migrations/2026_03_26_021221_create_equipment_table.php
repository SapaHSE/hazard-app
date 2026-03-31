<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // yang mendaftarkan
            $table->string('name');                // Nama alat, misal: APAR - Powder 6kg
            $table->string('qr_code')->unique();   // Kode unik QR, misal: BBE-APAR-2024-001234
            $table->string('location')->nullable();
            $table->enum('status', ['layak_pakai', 'perlu_servis', 'tidak_layak'])->default('layak_pakai');
            $table->date('last_checked_at')->nullable();
            $table->date('next_check_at')->nullable();
            $table->timestamps();
        });

        Schema::create('qr_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('scanned_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_scans');
        Schema::dropIfExists('equipment');
    }
};