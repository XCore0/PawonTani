<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePanenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_lahan' => ['nullable', 'string', 'exists:lahan,id_lahan'],
            'komoditas_nama' => ['nullable', 'string', 'max:100'],
            'varietas' => ['nullable', 'string', 'max:100'],
            'jenis_panen' => ['nullable', 'string', 'max:100'],
            'tanggal_panen' => ['nullable', 'date'],
            'waktu_panen' => ['nullable', 'string', 'max:20'],
            'musim_tanam' => ['nullable', 'string', 'max:50'],
            'berat_kg' => ['nullable', 'numeric', 'min:0.1', 'max:99999999'],
            'kualitas_grade' => ['nullable', 'string', 'max:50'],
            'kadar_air' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'persen_butir_utuh' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'persen_hampa_kotoran' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'metode_panen' => ['nullable', 'string', 'max:100'],
            'biaya_panen' => ['nullable', 'numeric', 'min:0'],
            'keterangan_tenaga_kerja' => ['nullable', 'string', 'max:150'],
            'harga_per_kg' => ['nullable', 'numeric', 'min:0'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'foto_panen' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'foto_panen_list' => ['nullable', 'array', 'max:10'],
            'foto_panen_list.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            // Foto lama yang masih dipertahankan saat edit (path relatif atau URL).
            'existing_photos' => ['nullable', 'array'],
            'existing_photos.*' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'foto_panen.image' => 'Berkas foto panen harus berupa gambar.',
            'foto_panen.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'foto_panen.max' => 'Ukuran foto maksimal 5 MB.',
            'foto_panen_list.array' => 'Daftar foto panen tidak valid.',
            'foto_panen_list.max' => 'Maksimal 10 foto per catatan panen.',
            'foto_panen_list.*.image' => 'Setiap berkas foto harus berupa gambar.',
            'foto_panen_list.*.mimes' => 'Format setiap foto harus JPG, PNG, atau WEBP.',
            'foto_panen_list.*.max' => 'Ukuran setiap foto maksimal 5 MB.',
            'foto_panen_list.*.uploaded' => 'Gagal mengunggah foto. Pastikan format file sesuai (JPG, PNG, WEBP) dan ukuran maksimal 5 MB.',
            'existing_photos.array' => 'Daftar foto lama yang dipertahankan tidak valid.',
            'existing_photos.*.string' => 'Setiap foto lama harus berupa teks path yang valid.',
        ];
    }
}
