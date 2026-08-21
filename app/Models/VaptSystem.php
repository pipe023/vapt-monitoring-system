<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

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
        'network',
        'url',
        'personnel_in_charge',
        'status',
        'remarks',
        'date_of_last_va', // <-- Add this line
    ];

    public function getEncryptedIdAttribute(): string
    {
        return rtrim(strtr(Crypt::encryptString((string) $this->getKey()), '+/', '-_'), '=');
    }

    public static function decryptId(string $encryptedId): int
    {
        try {
            $encryptedId .= str_repeat('=', (4 - strlen($encryptedId) % 4) % 4);
            $id = Crypt::decryptString(strtr($encryptedId, '-_', '+/'));
        } catch (DecryptException $exception) {
            abort(404);
        }

        abort_unless(ctype_digit($id), 404);

        return (int) $id;
    }
}