<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artikel extends Model
{
    protected $table = 'artikel';
    protected $primaryKey = 'id_artikel';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'judul',
        'kategori',
        'komoditas',
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
}
