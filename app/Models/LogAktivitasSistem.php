<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAktivitasSistem extends Model
{
    protected $table = 'log_aktivitas_sistem';
    public $timestamps = false;

    protected $fillable = [
        'actor_id', 'actor_name', 'actor_role', 'id_kelompok', 'event',
        'subject_type', 'subject_id', 'description', 'ip_address', 'user_agent', 'created_at',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'actor_id', 'id_pengguna');
    }
}
