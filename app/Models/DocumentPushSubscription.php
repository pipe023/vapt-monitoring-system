<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentPushSubscription extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['endpoint', 'public_key', 'auth_token'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
