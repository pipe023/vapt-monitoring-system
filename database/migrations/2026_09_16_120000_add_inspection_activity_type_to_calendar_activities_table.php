<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE calendar_activities MODIFY type ENUM('Conference', 'Dispatch', 'Mission', 'TIAC', 'Inspection') NOT NULL");
        }
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE calendar_activities MODIFY type ENUM('Conference', 'Dispatch', 'Mission', 'TIAC') NOT NULL");
        }
    }
};
