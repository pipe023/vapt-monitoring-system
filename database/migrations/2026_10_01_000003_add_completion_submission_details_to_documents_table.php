<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->date('date_of_completion')->nullable()->after('completed_at');
            $table->date('submitted_date')->nullable()->after('date_of_completion');
            $table->string('receiving_office')->nullable()->after('submitted_date');
            $table->string('submitted_document_path')->nullable()->after('receiving_office');
            $table->string('submitted_document_name')->nullable()->after('submitted_document_path');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'date_of_completion',
                'submitted_date',
                'receiving_office',
                'submitted_document_path',
                'submitted_document_name',
            ]);
        });
    }
};