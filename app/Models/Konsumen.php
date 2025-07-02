<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Konsumen extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // ✅ Added HasApiTokens for authentication

    protected $primaryKey = 'no_identitas';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'no_identitas',
        'nama',
        'email',
        'no_telepon',
        'saldo',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'saldo' => 'integer', // ✅ FIXED: Keep as integer to match migration
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ✅ FIXED: Override getAuthIdentifierName for Sanctum
    public function getAuthIdentifierName()
    {
        return 'no_identitas';
    }

    // ✅ FIXED: Override getKeyName for primary key
    public function getKeyName()
    {
        return 'no_identitas';
    }

    // Relasi: Konsumen memiliki banyak topup
    public function topups()
    {
        return $this->hasMany(Topup::class, 'konsumen_id', 'no_identitas');
    }

    // Relasi: Konsumen memiliki banyak pembayaran
    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'konsumen_id', 'no_identitas');
    }

    // ✅ IMPROVED: Auto-generate no_identitas saat pembuatan
    protected static function booted(): void
    {
        static::creating(function (Konsumen $konsumen) {
            if (empty($konsumen->no_identitas)) {
                // Get the latest konsumen and increment
                $latest = self::orderByRaw('CAST(SUBSTRING(no_identitas, 4) AS UNSIGNED) DESC')->first();
                
                if ($latest && preg_match('/KNS(\d+)/', $latest->no_identitas, $matches)) {
                    $lastNumber = (int) $matches[1];
                } else {
                    $lastNumber = 0;
                }
                
                $konsumen->no_identitas = 'KNS' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    // ✅ NEW: Helper method to get formatted saldo
    public function getFormattedSaldoAttribute()
    {
        return 'Rp ' . number_format($this->saldo ?? 0, 0, ',', '.');
    }

    // ✅ NEW: Scope untuk konsumen aktif
    public function scopeActive($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    // ✅ NEW: Method untuk update saldo
    public function addSaldo($amount)
    {
        $this->increment('saldo', $amount);
        return $this;
    }

    public function subtractSaldo($amount)
    {
        if ($this->saldo >= $amount) {
            $this->decrement('saldo', $amount);
            return $this;
        }
        
        throw new \Exception('Saldo tidak mencukupi');
    }
}