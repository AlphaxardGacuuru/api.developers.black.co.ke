<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The largest profile picture a user can upload, in kilobytes.
     */
    public const MAX_AVATAR_KB = 5120;

    /**
     * Where uploaded profile pictures live on the public disk.
     */
    public const AVATAR_UPLOAD_DIRECTORY = 'avatars/uploads';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
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
        ];
    }

    /**
     * The profile picture as a full URL, since the frontend runs on another
     * origin. Uploads are stored as paths on the public disk.
     */
    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => match (true) {
                blank($value) => null,
                Str::startsWith($value, ['http://', 'https://']) => $value,
                default => asset('storage/' . $value),
            }
        );
    }

    /**
     * Whether the user picked their own profile picture.
     */
    public function hasUploadedAvatar(): bool
    {
        return Str::of((string) $this->getRawOriginal('avatar'))
            ->startsWith(static::AVATAR_UPLOAD_DIRECTORY . '/');
    }
}
