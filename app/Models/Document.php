<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'title', 'category', 'status', 'review_office', 'owner', 'due_date', 'description',
        'file_path', 'file_name', 'user_id',
    ];

    protected $casts = ['due_date' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}