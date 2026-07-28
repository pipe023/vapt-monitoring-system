<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vapt_systems', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url')->nullable();
            // Updated statuses here:
            $table->enum('status', ['FOR PATCHING', 'COMPLETED', 'ONGOING PATCHING', 'ONGOING VAPT'])->default('ONGOING VAPT');
            $table->text('remarks')->nullable();
            $table->timestamps(); // This automatically handles the Add/Edit timestamps
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vapt_systems');
    }
};
