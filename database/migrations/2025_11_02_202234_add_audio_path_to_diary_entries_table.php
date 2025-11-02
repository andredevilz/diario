<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 public function up(): void
{
    Schema::table('diary_entries', function (Illuminate\Database\Schema\Blueprint $table) {
        if (!Schema::hasColumn('diary_entries', 'audio_path')) {
            $table->string('audio_path')->nullable()->after('transcription');
        }
    });
}

public function down(): void
{
    Schema::table('diary_entries', function (Illuminate\Database\Schema\Blueprint $table) {
        if (Schema::hasColumn('diary_entries', 'audio_path')) {
            $table->dropColumn('audio_path');
        }
    });
}

};
