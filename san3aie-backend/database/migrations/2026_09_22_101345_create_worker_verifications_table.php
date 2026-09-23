<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('document_type');
            $table->string('document_path');
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
            ])->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_verifications');
    }
};
