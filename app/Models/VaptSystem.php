<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VaptSystem extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     * This array is required for the Add and Update functions to work.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'url',
        'personnel_in_charge',
        'status',
        'remarks',
    ];
}