<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('track_records', function (Blueprint $table) {
            $table->json('translations')->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('track_records', function (Blueprint $table) {
            $table->dropColumn('translations');
        });
    }
};
