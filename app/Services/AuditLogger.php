<?php

namespace App\Services;

use App\Models\LogAktivitasSistem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AuditLogger
{
    /**
     * Catat tindakan penting tanpa menyimpan password, isi dokumen, atau data sensitif.
     */
    public static function log(
        string $event,
        string $description,
        ?string $subjectType = null,
        string|int|null $subjectId = null,
        ?string $groupId = null,
        ?Request $request = null
    ): void {
        try {
            $request ??= request();
            $actor = $request?->user() ?? Auth::user();

            LogAktivitasSistem::create([
                'actor_id' => $actor?->id_pengguna,
                'actor_name' => $actor?->nama,
                'actor_role' => $actor?->role,
                'id_kelompok' => $groupId ?? $actor?->id_kelompok,
                'event' => $event,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId !== null ? (string) $subjectId : null,
                'description' => substr($description, 0, 2000),
                'ip_address' => $request?->ip(),
                'user_agent' => substr((string) $request?->userAgent(), 0, 500),
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            // Kegagalan pencatatan audit tidak boleh membuka detail internal ke pengguna.
            report($exception);
        }
    }
}
