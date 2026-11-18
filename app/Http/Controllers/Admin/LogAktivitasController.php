<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KelompokTani;
use App\Models\LogAktivitasSistem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogAktivitasController extends Controller
{
    public function index(Request $request): View
    {
        $query = LogAktivitasSistem::query()->with('actor:id_pengguna,nama,role');

        if ($groupId = $request->query('kelompok')) {
            $query->where('id_kelompok', $groupId);
        }
        if ($event = $request->query('event')) {
            $query->where('event', $event);
        }
        if ($search = trim((string) $request->query('q'))) {
            $like = \Illuminate\Support\Facades\DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(fn ($q) => $q->where('actor_name', $like, "%{$search}%")
                ->orWhere('description', $like, "%{$search}%")
                ->orWhere('subject_id', $like, "%{$search}%"));
        }

        $logs = $query->orderByDesc('created_at')->paginate(25)->withQueryString();
        $kelompok = KelompokTani::orderBy('nama_kelompok')->get(['id_kelompok', 'nama_kelompok']);
        $events = LogAktivitasSistem::query()->select('event')->distinct()->orderBy('event')->pluck('event');
        $totalHariIni = LogAktivitasSistem::whereDate('created_at', today())->count();
        $totalMingguIni = LogAktivitasSistem::where('created_at', '>=', now()->startOfWeek())->count();

        return view('Admin.Content.LogAktivitas', compact('logs', 'kelompok', 'events', 'totalHariIni', 'totalMingguIni'));
    }
}
