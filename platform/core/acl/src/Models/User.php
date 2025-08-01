<?php

namespace Botble\ACL\Models;

use Botble\ACL\Concerns\HasPreferences;
use Botble\ACL\Contracts\HasPermissions as HasPermissionsContract;
use Botble\ACL\Contracts\HasPreferences as HasPreferencesContract;
use Botble\ACL\Notifications\ResetPasswordNotification;
use Botble\ACL\Traits\PermissionTrait;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\Base\Supports\Avatar;
use Botble\Media\Facades\RvMedia;
use Botble\Media\Models\MediaFile;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Throwable;

class User extends BaseModel implements
    HasPermissionsContract,
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract,
    HasPreferencesContract
{
    use Authenticatable;
    use Authorizable;
    use CanResetPassword;
    use HasApiTokens;
    use HasFactory;
    use PermissionTrait {
        PermissionTrait::hasPermission as traitHasPermission;
        PermissionTrait::hasAnyPermission as traitHasAnyPermission;
    }
    use Notifiable;
    use HasPreferences;

    protected $table = 'users';

    protected $fillable = [
        'username',
        'email',
        'phone',
        'first_name',
        'last_name',
        'password',
        'avatar_id',
        'permissions',
        'last_login',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'email_verified_at' => 'datetime',
        'permissions' => 'json',
        'username' => SafeContent::class,
        'first_name' => SafeContent::class,
        'last_name' => SafeContent::class,
        'last_login' => 'datetime',
    ];

    public function avatar(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class)->withDefault();
    }

    public function roles(): BelongsToMany
    {
        return $this
            ->belongsToMany(Role::class, 'role_users', 'user_id', 'role_id')
            ->withTimestamps();
    }

    protected function firstName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucfirst((string) $value),
            set: fn ($value) => ucfirst((string) $value),
        );
    }

    protected function lastName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucfirst((string) $value),
            set: fn ($value) => ucfirst((string) $value),
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->first_name . ' ' . $this->last_name,
        );
    }

    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getKey() ? route('users.profile.view', $this->getKey()) : null,
        );
    }

    protected function activated(): Attribute
    {
        return Attribute::get(fn (): bool => $this->activations()->where('completed', true)->exists());
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::get(function () {
            if ($this->avatar && $this->avatar->url) {
                return RvMedia::url($this->avatar->url);
            }

            try {
                return Avatar::createBase64Image($this->name);
            } catch (Throwable) {
                return RvMedia::getDefaultImage();
            }
        });
    }

    public function isSuperUser(): bool
    {
        return $this->super_user || $this->traitHasPermission(ACL_ROLE_SUPER_USER);
    }

    public function hasPermission(string|array $permissions): bool
    {
        if ($this->isSuperUser()) {
            return true;
        }

        return $this->traitHasPermission($permissions);
    }

    public function hasAnyPermission(string|array $permissions): bool
    {
        if ($this->isSuperUser()) {
            return true;
        }

        return $this->traitHasAnyPermission($permissions);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function activations(): HasMany
    {
        return $this->hasMany(Activation::class, 'user_id');
    }

    public function inRole($role): bool
    {
        $roleId = null;
        if ($role instanceof Role) {
            $roleId = $role->getKey();
        }

        foreach ($this->roles as $instance) {
            if ($role instanceof Role) {
                if ($instance->getKey() === $roleId) {
                    return true;
                }
            } elseif ($instance->getKey() == $role || $instance->slug == $role) {
                return true;
            }
        }

        return false;
    }

    public function delete(): ?bool
    {
        if ($this->exists) {
            $this->activations()->delete();
            $this->roles()->detach();
        }

        return parent::delete();
    }
}
