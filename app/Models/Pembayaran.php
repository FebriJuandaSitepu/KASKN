<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    // Nama tabel eksplisit
    protected $table = 'pembayaran';

    // Kolom yang dapat diisi (mass assignment) - HANYA yang ada di database
    protected $fillable = [
        'konsumen_id',      // relasi ke Konsumen (no_identitas)
        'tipe',             // pemasukan / pengeluaran
        'jumlah',
        'metode',
        'tanggal',
        'status',
        'bukti',            // hanya 1 kolom bukti (bukan bukti_pembayaran)
        'pemesanan_id',     // jika berasal dari pemesanan
    ];

    // Otomatis cast ke tipe data yang tepat
    protected $casts = [
        'tanggal'     => 'datetime',
        'jumlah'      => 'integer',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /**
     * Relasi ke konsumen (menggunakan no_identitas sebagai foreign key).
     */
    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class, 'konsumen_id', 'no_identitas');
    }

    /**
     * Scope untuk filter berdasarkan tipe
     */
    public function scopePemasukan($query)
    {
        return $query->where('tipe', 'pemasukan');
    }

    public function scopePengeluaran($query)
    {
        return $query->where('tipe', 'pengeluaran');
    }

    public function scopeValid($query)
    {
        return $query->where('status', 'valid');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Accessor untuk format rupiah
     */
    public function getJumlahFormatAttribute()
    {
        return 'Rp ' . number_format($this->jumlah, 0, ',', '.');
    }

    /**
     * Accessor untuk URL bukti
     */
    public function getBuktiUrlAttribute()
    {
        return $this->bukti ? asset('storage/' . $this->bukti) : null;
    }
}