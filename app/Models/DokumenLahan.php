<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenLahan extends Model
{
    protected $table = 'dokumen_lahan';

    protected $fillable = [
        'id_lahan', 'jenis', 'file_path', 'original_name', 'mime_type', 'size_bytes', 'uploaded_by',
    ];

    public function lahan(): BelongsTo
    {
        return $this->belongsTo(Lahan::class, 'id_lahan', 'id_lahan');
    }

    public function pengunggah(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'uploaded_by', 'id_pengguna');
    }
}
