<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('bio')->nullable()->after('area_id');
            $table->unsignedInteger('experience_years')->nullable()->after('bio');
            $table->boolean('is_available')->default(true)->after('experience_years');
            $table->string('avatar')->nullable()->after('is_available');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bio',
                'experience_years',
                'is_available',
                'avatar',
            ]);
        });
    }
};
