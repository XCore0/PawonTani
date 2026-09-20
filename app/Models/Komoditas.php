<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Komoditas extends Model
{
    use HasFactory;

    protected $table = 'komoditas';
    protected $primaryKey = 'id_komoditas';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_komoditas',
        'nama_komoditas',
        'kategori',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id_komoditas)) {
                $model->id_komoditas = self::generateIdKomoditas();
            }
        });
    }

    public static function generateIdKomoditas(): string
    {
        do {
            $id = 'kmdt-' . random_int(1000, 9999);
        } while (self::where('id_komoditas', $id)->exists());

        return $id;
    }
}
