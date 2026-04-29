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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Insert default departments from the ones commonly used
        $defaultDepartments = [
            ['name' => 'IT', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Operational', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Environmental', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Maintenance', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'HSE', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'K3 / HSE', 'created_at' => now(), 'updated_at' => now()],
        ];
        
        \Illuminate\Support\Facades\DB::table('departments')->insert($defaultDepartments);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
