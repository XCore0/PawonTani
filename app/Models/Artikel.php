<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artikel extends Model
{
    protected $table = 'artikel';
    protected $primaryKey = 'id_artikel';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'judul',
        'kategori',
        'komoditas_id',
        'ringkasan',
        'isi',
        'gambar',
        'tanggal',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Artikel $artikel): void {
            if (empty($artikel->id_artikel)) {
                $artikel->id_artikel = self::generateIdArtikel();
            }
        });
    }

    public static function generateIdArtikel(): string
    {
        do {
            $id = 'ATL-' . random_int(1000, 9999);
        } while (self::whereKey($id)->exists());

        return $id;
    }

    /**
     * Relationship to Komoditas
     */
    public function komoditas()
    {
        return $this->belongsTo(Komoditas::class, 'komoditas_id', 'id_komoditas');
    }
}
