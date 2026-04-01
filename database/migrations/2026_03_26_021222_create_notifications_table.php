<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // penerima
            $table->string('title');
            $table->text('body');
            $table->enum('type', ['report', 'announcement', 'personal'])->default('announcement');
            $table->boolean('is_read')->default(false);
            $table->nullableMorphs('notifiable'); // bisa link ke report, news, dll
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
