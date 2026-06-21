<?php

namespace App\Models;

use App\Casts\Aes256GcmEncrypted;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The primary key associated with the table.
     * Overridden to handle Supabase UUID formats securely.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the model's ID is auto-incrementing.
     * Set to false since IDs are managed externally by Supabase Auth.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id', 
        'fname',
        'lname',
        'email',
        'email_hash',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_hash',
    ];

    /**
     * The accessors to append to the model's array form.
     * This forces Livewire's serialization to include the decrypted value automatically.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'full_name',
    ];

    /**
     * The "booted" method of the model.
     * Automates fallback hashing checks on mutations.
     */
    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if ($user->isDirty('email') && is_string($user->email) && $user->email !== '') {
                $user->email_hash = static::hashEmail($user->email);
            }
        });
    }

    /**
     * Generates a deterministic, searchable SHA256 signature representation for looking up encrypted rows.
     */
    public static function hashEmail(string $email): string
    {
        return hash('sha256', strtolower(trim($email)));
    }

    /**
     * Helper check determining if administrative matrix privileges are active.
     */
    public function isAdmin(): bool
    {
        return $this->roleEnum()->isAdmin();
    }

    /**
     * Safe backing evaluation converter mapping raw roles to Enums.
     */
    public function roleEnum(): UserRole
    {
        return UserRole::tryFrom((string) $this->role) ?? UserRole::User;
    }

    /**
     * Accessor that automatically intercepts, handles, maps, and returns the 
     * decrypted presenting name sequence configuration layout.
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Accessing properties via $this handles decryption using your custom Cast class
                $firstName = $this->fname ?? '';
                $lastName = $this->lname ?? '';

                return trim($firstName . ' ' . $lastName) ?: 'Unknown';
            }
        );
    }

    /**
     * Structural relation trace mapping down to room reservation logs.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fname'             => Aes256GcmEncrypted::class,
            'lname'             => Aes256GcmEncrypted::class,
            'email'             => Aes256GcmEncrypted::class,
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }
}