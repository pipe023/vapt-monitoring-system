<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_POIC_SMSB = 'POIC, SMSB';
    public const ROLE_POIC_ASDB = 'POIC, ASDB';
    public const ROLE_POIC_ADMIN = 'POIC, ADMIN';
    public const ROLE_POIC_REB = 'POIC, REB';
    public const ROLE_OPNS = 'OPNS';
    public const ROLE_DUTY_SERVER = 'DUTY SERVER';

    public const ROLE_LEGACY_ADMIN = 'admin';
    public const ROLE_LEGACY_SUPERADMIN = 'superadmin';
    public const ROLE_LEGACY_VIEWER = 'viewer';

    public const OFFICE_ROLES = [
        self::ROLE_POIC_SMSB,
        self::ROLE_POIC_ASDB,
        self::ROLE_POIC_ADMIN,
        self::ROLE_POIC_REB,
    ];

    public const SUPERVISOR_ROLES = [
        self::ROLE_OPNS,
        self::ROLE_DUTY_SERVER,
    ];

    public const VALID_ROLES = [
        self::ROLE_LEGACY_SUPERADMIN,
        self::ROLE_LEGACY_ADMIN,
        self::ROLE_LEGACY_VIEWER,
        self::ROLE_POIC_SMSB,
        self::ROLE_POIC_ASDB,
        self::ROLE_POIC_ADMIN,
        self::ROLE_POIC_REB,
        self::ROLE_OPNS,
        self::ROLE_DUTY_SERVER,
    ];

    protected $fillable = [
        'username',
        'password',
        'role',
        'profile_photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public static function validRoles(): array
    {
        return self::VALID_ROLES;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_LEGACY_SUPERADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_LEGACY_ADMIN || $this->role === self::ROLE_LEGACY_SUPERADMIN;
    }

    public function isViewer(): bool
    {
        return $this->role === self::ROLE_LEGACY_VIEWER;
    }

    public function isDocumentOfficeRole(): bool
    {
        return in_array($this->role, self::OFFICE_ROLES, true);
    }

    public function canViewAllDocuments(): bool
    {
        return in_array($this->role, self::SUPERVISOR_ROLES, true);
    }

    public function canAccessDocumentTracking(): bool
    {
        return $this->isDocumentOfficeRole() || $this->canViewAllDocuments() || $this->isAdmin() || $this->isSuperAdmin();
    }

    public function canManageDocumentTracking(): bool
    {
        return $this->canAccessDocumentTracking();
    }
}