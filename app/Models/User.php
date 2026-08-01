<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\Email\Contracts\HasEmailAuthentication;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use SensitiveParameter;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'asociado_id'])]
#[Hidden(['password', 'remember_token', 'app_authentication_secret', 'app_authentication_recovery_codes'])]
class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery, HasEmailAuthentication
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles, LogsActivity, Notifiable;

    public const string ROL_SUPER_ADMIN = 'super_admin';

    public const string ROL_SUBADMIN = 'subadmin';

    public const string ROL_ASOCIADO = 'asociado';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'app_authentication_secret' => 'encrypted',
            'app_authentication_recovery_codes' => 'encrypted:array',
            'has_email_authentication' => 'boolean',
        ];
    }

    /** El establecimiento del que es dueño, para /mi-cuenta. */
    public function asociado(): BelongsTo
    {
        return $this->belongsTo(Asociado::class);
    }

    /**
     * El rol `asociado` no entra al panel: su sesión sirve solo para /mi-cuenta.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole([self::ROL_SUPER_ADMIN, self::ROL_SUBADMIN]);
    }

    public function esSuperAdmin(): bool
    {
        return $this->hasRole(self::ROL_SUPER_ADMIN);
    }

    public function esSubadmin(): bool
    {
        return $this->hasRole(self::ROL_SUBADMIN);
    }

    public function esAsociado(): bool
    {
        return $this->hasRole(self::ROL_ASOCIADO);
    }

    // --- MFA del núcleo de Filament (RF-40) ---

    public function getAppAuthenticationSecret(): ?string
    {
        return $this->app_authentication_secret;
    }

    public function saveAppAuthenticationSecret(#[SensitiveParameter] ?string $secret): void
    {
        $this->app_authentication_secret = $secret;
        $this->save();
    }

    public function getAppAuthenticationHolderName(): string
    {
        return $this->email;
    }

    /** @return ?array<string> */
    public function getAppAuthenticationRecoveryCodes(): ?array
    {
        return $this->app_authentication_recovery_codes;
    }

    /** @param  ?array<string>  $codes */
    public function saveAppAuthenticationRecoveryCodes(#[SensitiveParameter] ?array $codes): void
    {
        $this->app_authentication_recovery_codes = $codes;
        $this->save();
    }

    /**
     * Segundo factor por código al correo: la alternativa para quien no
     * quiera instalar una app de autenticación. Cada quien lo activa desde
     * su perfil; no viene encendido de fábrica.
     */
    public function hasEmailAuthentication(): bool
    {
        return (bool) $this->has_email_authentication;
    }

    public function toggleEmailAuthentication(bool $condition): void
    {
        $this->has_email_authentication = $condition;
        $this->save();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email'])
            ->logOnlyDirty()
            ->useLogName('usuario')
            ->setDescriptionForEvent(fn (string $evento): string => "Usuario {$this->name}: {$evento}");
    }
}
