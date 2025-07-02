<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topup extends Model
{
    use HasFactory;

    protected $fillable = [
        'konsumen_id', 
        'nominal', 
        'bukti_transfer', 
        'status'
    ];

    protected $casts = [
        'nominal' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke konsumen
     */
    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class, 'konsumen_id', 'no_identitas');
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDiterima($query)
    {
        return $query->where('status', 'diterima');
    }

    public function scopeDitolak($query)
    {
        return $query->where('status', 'ditolak');
    }

    /**
     * Accessor untuk format nominal
     */
    public function getNominalFormatAttribute()
    {
        return 'Rp ' . number_format($this->nominal, 0, ',', '.');
    }

    /**
     * Accessor untuk URL bukti transfer
     */
    public function getBuktiTransferUrlAttribute()
    {
        return $this->bukti_transfer ? asset('storage/' . $this->bukti_transfer) : null;
    }

    /**
     * Accessor untuk badge class berdasarkan status
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'diterima' => 'bg-success',
            'ditolak' => 'bg-danger',
            'pending' => 'bg-warning',
            default => 'bg-secondary'
        };
    }
}