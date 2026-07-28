<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vapt_systems', function (Blueprint $table) {
            $table->string('personnel_in_charge')->nullable()->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('vapt_systems', function (Blueprint $table) {
            $table->dropColumn('personnel_in_charge');
        });
    }
};