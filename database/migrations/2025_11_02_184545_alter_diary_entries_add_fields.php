<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) user_id
        if (! Schema::hasColumn('diary_entries', 'user_id')) {
            Schema::table('diary_entries', function (Blueprint $table) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->nullOnDelete();
            });
        }

        // 2) company_id
        if (! Schema::hasColumn('diary_entries', 'company_id')) {
            Schema::table('diary_entries', function (Blueprint $table) {
                $table->foreignId('company_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained()
                    ->nullOnDelete();
            });
        }

        // 3) entry_date
        if (! Schema::hasColumn('diary_entries', 'entry_date')) {
            Schema::table('diary_entries', function (Blueprint $table) {
                $table->date('entry_date')->nullable()->after('company_id');
            });
        }

        // 4) site_name
        if (! Schema::hasColumn('diary_entries', 'site_name')) {
            Schema::table('diary_entries', function (Blueprint $table) {
                $table->string('site_name')->nullable()->after('entry_date');
            });
        }

        // 5) payload
        if (! Schema::hasColumn('diary_entries', 'payload')) {
            Schema::table('diary_entries', function (Blueprint $table) {
                $table->json('payload')->nullable()->after('site_name');
            });
        }

        // 6) transcription
        if (! Schema::hasColumn('diary_entries', 'transcription')) {
            Schema::table('diary_entries', function (Blueprint $table) {
                $table->text('transcription')->nullable()->after('payload');
            });
        }
    }

    public function down(): void
    {
        // fazer o inverso mas só se existirem
        if (Schema::hasColumn('diary_entries', 'transcription')) {
            Schema::table('diary_entries', function (Blueprint $table) {
                $table->dropColumn('transcription');
            });
        }

        if (Schema::hasColumn('diary_entries', 'payload')) {
            Schema::table('diary_entries', function (Blueprint $table) {
                $table->dropColumn('payload');
            });
        }

        if (Schema::hasColumn('diary_entries', 'site_name')) {
            Schema::table('diary_entries', function (Blueprint $table) {
                $table->dropColumn('site_name');
            });
        }

        if (Schema::hasColumn('diary_entries', 'entry_date')) {
            Schema::table('diary_entries', function (Blueprint $table) {
                $table->dropColumn('entry_date');
            });
        }

        if (Schema::hasColumn('diary_entries', 'company_id')) {
            Schema::table('diary_entries', function (Blueprint $table) {
                $table->dropConstrainedForeignId('company_id');
            });
        }

        if (Schema::hasColumn('diary_entries', 'user_id')) {
            Schema::table('diary_entries', function (Blueprint $table) {
                $table->dropConstrainedForeignId('user_id');
            });
        }
    }
};
