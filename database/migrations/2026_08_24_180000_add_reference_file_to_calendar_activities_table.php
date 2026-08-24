<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_activities', function (Blueprint $table) {
            $table->string('reference_path')->nullable()->after('note');
            $table->string('reference_name')->nullable()->after('reference_path');
        });
    }

    public function down(): void
    {
        Schema::table('calendar_activities', function (Blueprint $table) {
            $table->dropColumn(['reference_path', 'reference_name']);
        });
    }
};