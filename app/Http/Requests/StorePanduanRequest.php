<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePanduanRequest extends FormRequest
{
    public const CATEGORIES = [
        'Budidaya Tanaman', 'Hama & Penyakit', 'Irigasi & Air',
        'Nutrisi & Pupuk', 'Perawatan Tanaman', 'Panen & Pasca Panen',
    ];

    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255', 'unique:panduan,judul'],
            'kategori' => ['required', 'string', 'in:' . implode(',', self::CATEGORIES)],
            'komoditas' => ['required', 'string', function ($attribute, $value, $fail) {
                if ($value !== 'Semua Komoditas' && !\App\Models\Komoditas::where('nama_komoditas', $value)->exists()) {
                    $fail('Komoditas sasaran harus dipilih dari daftar yang tersedia.');
                }
            }],
            'ringkasan' => ['required', 'string'],
            'isi' => ['required', 'string', 'unique:panduan,isi'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'tanggal' => ['nullable', 'date'],
            'status' => ['required', 'in:Publik,Draft'],
        ];
    }
}
