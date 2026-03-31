<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['hazard', 'inspection'])->default('hazard');
            $table->enum('severity', ['low', 'medium', 'high'])->default('low');
            $table->enum('status', ['open', 'in_progress', 'closed'])->default('open');
            $table->string('location')->nullable();
            $table->timestamps();
        });

        // Foto laporan dipisah (satu laporan bisa banyak foto)
        Schema::create('report_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->string('photo_url');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_photos');
        Schema::dropIfExists('reports');
    }
};