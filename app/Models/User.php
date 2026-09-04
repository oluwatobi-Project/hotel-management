<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'role_id',
        'extra_modules',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'extra_modules' => 'array',
        ];
    }

    public function accessRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Modules granted through the assigned role plus any per-user overrides.
     * Always-on modules (dashboard, notifications) are included implicitly.
     */
    public function effectiveModules(): array
    {
        $modules = [];

        foreach (config('rbac.modules', []) as $key => $definition) {
            if ($definition['always'] ?? false) {
                $modules[] = $key;
            }
        }

        if ($this->accessRole) {
            $modules = array_merge($modules, $this->accessRole->modules ?? []);
        }

        $modules = array_merge($modules, $this->extra_modules ?? []);

        return array_values(array_unique($modules));
    }

    /**
     * Can this user operate the given staff module?
     */
    public function canModule(string $module): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (in_array($module, array_keys(config('rbac.admin_modules', [])), true)) {
            return false;
        }

        return in_array($module, $this->effectiveModules(), true);
    }

    /**
     * Module keys (assignable staff modules) available to this user,
     * used to render the sidebar.
     */
    public function visibleModules(): array
    {
        if ($this->isAdmin()) {
            return array_keys(config('rbac.modules', []));
        }

        return array_values(array_intersect(
            array_keys(config('rbac.modules', [])),
            $this->effectiveModules()
        ));
    }

    /**
     * Assignable module keys this user may additionally be granted
     * when assigning per-staff overrides (all staff modules, excluding always-on).
     */
    public static function assignableModules(): array
    {
        return collect(config('rbac.modules', []))
            ->reject(fn ($definition) => $definition['always'] ?? false)
            ->keys()
            ->values()
            ->all();
    }
}
