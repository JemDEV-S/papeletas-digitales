<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    /**
     * Constants for role names
     */
    const EMPLEADO = 'empleado';
    const JEFE_INMEDIATO = 'jefe_inmediato';
    const JEFE_RRHH = 'jefe_rrhh';
    const ADMIN = 'admin';

    /**
     * Get all users with this role
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if role has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->permissions) {
            return false;
        }

        return in_array($permission, $this->permissions);
    }

    /**
     * Get role by name
     */
    public static function getByName(string $name)
    {
        return static::where('name', $name)->first();
    }
}