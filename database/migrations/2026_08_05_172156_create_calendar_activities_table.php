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
    Schema::create('calendar_activities', function (Blueprint $table) {
        $table->id();
        $table->enum('type', ['Conference', 'Dispatch', 'Mission', 'TIAC']);
        $table->string('agenda')->nullable();
        $table->dateTime('start_time');
        $table->dateTime('end_time')->nullable();
        $table->string('presiding_officer')->nullable();
        $table->text('attendees')->nullable();
        $table->string('venue')->nullable();
        $table->text('personnel')->nullable();
        $table->string('location')->nullable();
        $table->text('note')->nullable();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_activities');
    }
};
