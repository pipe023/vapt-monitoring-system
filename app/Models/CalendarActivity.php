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
        'user_id'
    ];
}