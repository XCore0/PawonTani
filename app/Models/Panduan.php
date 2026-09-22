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
    public $incrementing = false;
    protected $keyType = 'string';

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

    protected static function booted(): void
    {
        static::creating(function (Panduan $panduan): void {
            if (empty($panduan->id_panduan)) {
                $panduan->id_panduan = self::generateIdPanduan();
            }
        });
    }

    public static function generateIdPanduan(): string
    {
        do {
            $id = 'PDU-' . random_int(1000, 9999);
        } while (self::whereKey($id)->exists());

        return $id;
    }

    public function author()
    {
        return $this->belongsTo(Pengguna::class, 'created_by', 'id_pengguna');
    }

    public function scopePublik(Builder $query): Builder
    {
        return $query->where('status', 'Publik');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'Draft');
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
