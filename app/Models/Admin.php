<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admins';

    protected $fillable = [
        'name',
        'username',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function setPasswordAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['password'] = $value;

            return;
        }

        if (preg_match('/^\$2[aby]\$/', $value)) {
            $this->attributes['password'] = $value;

            return;
        }

        $this->attributes['password'] = Hash::make($value);
    }

    public function validatePassword(string $password): bool
    {
        if (empty($this->password)) {
            return false;
        }

        if (preg_match('/^\$2[aby]\$/', $this->password)) {
            return Hash::check($password, $this->password);
        }

        return $this->password === $password;
    }
}
