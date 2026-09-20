<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Panduan extends Model
{
    use HasFactory;

    protected $table = 'panduan';
    protected $primaryKey = 'id_panduan';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'judul',
        'slug',
        'kategori',
        'komoditas',
        'ringkasan',
        'isi',
        'gambar',
        'tanggal',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function author()
    {
        return $this->belongsTo(Pengguna::class, 'created_by', 'id_pengguna');
    }

    public function scopePublik(Builder $query): Builder
    {
        return $query->where('status', 'publik');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (!filled($search)) {
            return $query;
        }

        $term = '%' . mb_strtolower(trim($search)) . '%';

        return $query->where(function (Builder $q) use ($term) {
            $q->whereRaw('LOWER(judul) LIKE ?', [$term])
                ->orWhereRaw('LOWER(kategori) LIKE ?', [$term])
                ->orWhereRaw('LOWER(COALESCE(komoditas, \'\')) LIKE ?', [$term]);
        });
    }
}
