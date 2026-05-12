<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hazard_reports', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(\Illuminate\Support\Facades\DB::raw('(UUID())'));
            $table->string('ticket_number', 30)->unique()->nullable();
            $table->uuid('user_id');
            $table->string('title', 200);
            $table->text('description');
            $table->enum('status', ['pending', 'open', 'in_progress', 'closed', 'rejected'])->default('pending');
            $table->string('sub_status', 50)->nullable();
            $table->string('location', 200);
            $table->text('image_url')->nullable();
            
            // Hazard-specific
            $table->enum('severity', ['low', 'medium', 'high'])->nullable();
            $table->string('company', 255)->nullable();
            $table->string('reported_department', 255)->nullable();
            $table->string('pic_department', 255)->nullable();
            $table->string('pelaku_pelanggaran', 255)->nullable();
            $table->string('pelapor_location', 255)->nullable();
            $table->string('kejadian_location', 255)->nullable();
            $table->date('due_date')->nullable();
            $table->string('hazard_category', 255)->nullable(); // e.g. TTA / KTA
            $table->string('hazard_subcategory', 255)->nullable();
            $table->text('suggestion')->nullable();
            $table->boolean('is_public')->default(true);

            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hazard_reports');
    }
};