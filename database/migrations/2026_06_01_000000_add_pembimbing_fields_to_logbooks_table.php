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
        Schema::table('logbooks', function (Blueprint $table) {
            $table->text('komentar_pembimbing')->nullable()->after('status');
            $table->longText('tanda_tangan_pembimbing')->nullable()->after('tanda_tangan_admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropColumn(['komentar_pembimbing', 'tanda_tangan_pembimbing']);
        });
    }
};
