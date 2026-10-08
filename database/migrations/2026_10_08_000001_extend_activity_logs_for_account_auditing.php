<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('actor_username')->nullable()->after('user_id');
            $table->string('method', 10)->nullable()->after('action');
            $table->string('path')->nullable()->after('method');
            $table->unsignedSmallInteger('status_code')->nullable()->after('path');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index('actor_username');
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['actor_username']);
            $table->dropIndex(['action']);
            $table->dropIndex(['created_at']);
            $table->dropColumn(['actor_username', 'method', 'path', 'status_code', 'user_agent']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
