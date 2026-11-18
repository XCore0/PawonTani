<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenAnggota extends Model
{
    protected $table = 'dokumen_anggota';

    protected $fillable = [
        'id_pengguna', 'jenis', 'file_path', 'original_name', 'mime_type', 'size_bytes', 'uploaded_by',
    ];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }

    public function pengunggah(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'uploaded_by', 'id_pengguna');
    }
}
