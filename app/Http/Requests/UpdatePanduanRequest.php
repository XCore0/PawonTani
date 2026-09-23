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
        $id = $this->route('id_panduan') ?? $this->input('id_panduan');

        return [
            'judul' => ['required', 'string', 'min:5', 'max:255', Rule::unique('panduan', 'judul')->ignore($id, 'id_panduan')],
            'kategori' => ['required', 'string', 'in:' . implode(',', self::CATEGORIES)],
            'komoditas_id' => ['nullable', 'string', 'max:20', 'exists:komoditas,id_komoditas'],
            'ringkasan' => ['required', 'string', 'min:10', 'max:1000'],
            'isi' => ['required', 'string', 'min:20', Rule::unique('panduan', 'isi')->ignore($id, 'id_panduan')],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'status' => ['required', 'in:Publik,Draft'],
        ];
    }

    public function messages(): array
    {
        return [
            'judul.required' => 'Judul panduan wajib diisi.',
            'judul.min' => 'Judul panduan minimal 5 karakter.',
            'judul.max' => 'Judul panduan maksimal 255 karakter.',
            'judul.unique' => 'Judul panduan sudah digunakan.',
            'kategori.required' => 'Kategori panduan wajib dipilih.',
            'kategori.in' => 'Kategori panduan tidak valid.',
            'komoditas_id.exists' => 'Komoditas sasaran harus dipilih dari daftar yang tersedia.',
            'ringkasan.required' => 'Ringkasan panduan wajib diisi.',
            'ringkasan.min' => 'Ringkasan panduan minimal 10 karakter.',
            'ringkasan.max' => 'Ringkasan panduan maksimal 1000 karakter.',
            'isi.required' => 'Langkah-langkah / rincian panduan wajib diisi.',
            'isi.min' => 'Langkah-langkah / rincian panduan minimal 20 karakter.',
            'isi.unique' => 'Isi panduan sudah digunakan.',
            'status.required' => 'Status panduan wajib dipilih.',
            'status.in' => 'Status harus bernilai Publik atau Draft.',
            'gambar.image' => 'File harus berupa gambar.',
            'gambar.mimes' => 'Format gambar yang diperbolehkan hanya JPG, JPEG, PNG, WEBP, atau SVG.',
            'gambar.max' => 'Ukuran gambar maksimal 2MB.',
        ];
    }
}
