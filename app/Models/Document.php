<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    public const BRANCHES = ['REB', 'ADMIN', 'ASDB', 'SMSB'];

    protected $fillable = [
        'title', 'category', 'branch', 'status', 'review_office', 'owner', 'due_date', 'description', 'completed_at',
        'date_of_completion', 'submitted_date', 'receiving_office', 'submitted_document_path', 'submitted_document_name',
        'file_path', 'file_name', 'user_id',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'date_of_completion' => 'date',
        'submitted_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}