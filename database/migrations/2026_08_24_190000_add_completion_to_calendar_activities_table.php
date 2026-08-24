<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_activities', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('reference_name');
            $table->foreignId('completed_by')->nullable()->after('completed_at')->constrained('users')->nullOnDelete();
            $table->string('completion_reference_path')->nullable()->after('completed_by');
            $table->string('completion_reference_name')->nullable()->after('completion_reference_path');
        });
    }

    public function down(): void
    {
        Schema::table('calendar_activities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('completed_by');
            $table->dropColumn(['completed_at', 'completion_reference_path', 'completion_reference_name']);
        });
    }
};