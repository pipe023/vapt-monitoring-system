<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarActivity extends Model
{
    protected $fillable = [
        'type',
        'agenda',
        'start_time',
        'end_time',
        'presiding_officer',
        'attendees',
        'venue',
        'personnel',
        'location',
        'note',
        'reference_path',
        'reference_name',
        'completed_at',
        'completed_by',
        'completion_reference_path',
        'completion_reference_name',
        'user_id'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];
}