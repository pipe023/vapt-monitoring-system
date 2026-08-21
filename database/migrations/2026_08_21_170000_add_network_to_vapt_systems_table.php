<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vapt_systems', 'network')) {
            Schema::table('vapt_systems', function (Blueprint $table) {
                $table->string('network')->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('vapt_systems', 'network')) {
            Schema::table('vapt_systems', function (Blueprint $table) {
                $table->dropColumn('network');
            });
        }
    }
};
