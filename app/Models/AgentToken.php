<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AgentToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'name',
        'token',
        'abilities',
        'last_used_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'abilities' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    const DEFAULT_ABILITIES = [
        'agent:register',
        'agent:heartbeat',
        'agent:sync',
        'agent:access-events',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($token) {
            if (!$token->token) {
                $token->token = self::generateToken();
            }
            
            if (!$token->abilities) {
                $token->abilities = self::DEFAULT_ABILITIES;
            }
        });
    }

    public static function generateToken(): string
    {
        return 'zka_' . bin2hex(random_bytes(32));
    }

    public static function createForAgent(int $agentId, string $name): self
    {
        return self::create([
            'agent_id' => $agentId,
            'name' => $name,
            'expires_at' => now()->addYear(), // Token válido por 1 año
            'is_active' => true,
        ]);
    }

    public function updateLastUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    public function hasAbility(string $ability): bool
    {
        return in_array($ability, $this->abilities ?? []);
    }

    public function revoke(): void
    {
        $this->update(['is_active' => false]);
    }

    public function scopeForAgent($query, int $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    public static function findValidToken(string $token): ?self
    {
        return self::where('token', $token)
                   ->active()
                   ->first();
    }
}