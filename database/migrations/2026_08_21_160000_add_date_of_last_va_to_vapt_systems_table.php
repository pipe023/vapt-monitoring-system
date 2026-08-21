<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vapt_systems', 'date_of_last_va')) {
            Schema::table('vapt_systems', function (Blueprint $table) {
                $table->date('date_of_last_va')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('vapt_systems', 'date_of_last_va')) {
            Schema::table('vapt_systems', function (Blueprint $table) {
                $table->dropColumn('date_of_last_va');
            });
        }
    }
};
