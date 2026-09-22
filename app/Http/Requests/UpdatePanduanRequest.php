<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePanduanRequest extends FormRequest
{
    public const CATEGORIES = StorePanduanRequest::CATEGORIES;

    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255', Rule::unique('panduan', 'judul')->ignore($this->route('id_panduan'), 'id_panduan')],
            'kategori' => ['required', 'string', 'in:' . implode(',', self::CATEGORIES)],
            'komoditas' => ['required', 'string', function ($attribute, $value, $fail) {
                if ($value !== 'Semua Komoditas' && !\App\Models\Komoditas::where('nama_komoditas', $value)->exists()) {
                    $fail('Komoditas sasaran harus dipilih dari daftar yang tersedia.');
                }
            }],
            'ringkasan' => ['required', 'string'],
            'isi' => ['required', 'string', Rule::unique('panduan', 'isi')->ignore($this->route('id_panduan'), 'id_panduan')],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'tanggal' => ['nullable', 'date'],
            'status' => ['required', 'in:Publik,Draft'],
        ];
    }
}
