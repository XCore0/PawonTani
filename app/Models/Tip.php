<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tip extends Model
{
    use HasFactory;

    protected $table = 'tips';
    protected $primaryKey = 'id_tips';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'judul',
        'kategori',
        'komoditas_id',
        'target',
        'ringkasan',
        'isi',
        'gambar',
        'tanggal',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Tip $tip): void {
            if (empty($tip->id_tips)) {
                $tip->id_tips = self::generateIdTips();
            }
        });
    }

    public static function generateIdTips(): string
    {
        do {
            $id = 'TPS-' . random_int(1000, 9999);
        } while (self::whereKey($id)->exists());

        return $id;
    }

    /**
     * Accessor for title compatibility
     */
    public function getTitleAttribute(): ?string
    {
        return $this->judul;
    }

    /**
     * Accessor for category compatibility
     */
    public function getCategoryAttribute(): ?string
    {
        return $this->kategori;
    }

    /**
     * Accessor for commodity compatibility
     */
    public function getCommodityAttribute(): ?string
    {
        return $this->komoditas?->nama_komoditas;
    }

    /**
     * Relationship to Komoditas
     */
    public function komoditas()
    {
        return $this->belongsTo(Komoditas::class, 'komoditas_id', 'id_komoditas');
    }

    /**
     * Accessor for excerpt compatibility
     */
    public function getExcerptAttribute(): ?string
    {
        return $this->ringkasan;
    }

    /**
     * Accessor for content compatibility
     */
    public function getContentAttribute(): ?string
    {
        return $this->isi;
    }

    /**
     * Accessor for image compatibility
     */
    public function getImageAttribute(): ?string
    {
        return $this->gambar;
    }

    /**
     * Accessor for date string compatibility (YYYY-MM-DD)
     */
    public function getDateAttribute(): string
    {
        if ($this->tanggal) {
            return $this->tanggal->format('Y-m-d');
        }

        if ($this->created_at) {
            return $this->created_at->format('Y-m-d');
        }

        return date('Y-m-d');
    }

    /**
     * Accessor for image URL
     */
    public function getImageUrlAttribute(): string
    {
        if (!empty($this->gambar)) {
            if (file_exists(public_path($this->gambar))) {
                return asset($this->gambar);
            }
            if (file_exists(public_path('uploads/tips/' . $this->gambar))) {
                return asset('uploads/tips/' . $this->gambar);
            }
        }

        return asset('images/Logo2.png');
    }
}
