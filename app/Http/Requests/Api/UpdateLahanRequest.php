<?php

namespace App\Http\Requests\Api;

use App\Models\Lahan;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateLahanRequest extends FormRequest
{
    /**
     * Otorisasi pembaruan lahan menggunakan LahanPolicy::update.
     */
    public function authorize(): bool
    {
        $lahan = $this->route('lahan');
        if (is_string($lahan)) {
            $lahan = Lahan::find($lahan);
        }

        return $lahan && ($this->user()?->can('update', $lahan) ?? false);
    }

    /**
     * Sanitasi dan perlindungan mass assignment terhadap field internal sebelum validasi.
     * Mencegah perubahan langsung terhadap identitas pemilik, status verifikasi, nomor revisi, maupun status operasional.
     */
    protected function prepareForValidation(): void
    {
        $this->request->remove('id_lahan');
        $this->request->remove('id_pengguna');
        $this->request->remove('id_kelompok');
        $this->request->remove('status_verifikasi');
        $this->request->remove('catatan_verifikasi');
        $this->request->remove('tanggal_verifikasi');
        $this->request->remove('verifikator_id');
        $this->request->remove('nomor_revisi');
        $this->request->remove('status_operasional');
    }

    /**
     * Aturan validasi pembaruan lahan oleh Anggota.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_lahan' => ['sometimes', 'required', 'string', 'max:150'],
            'luas' => ['sometimes', 'required', 'numeric', 'gt:0', 'max:999999.9999'],
            'provinsi_id' => ['nullable', 'string', 'max:10'],
            'kabupaten_id' => ['nullable', 'string', 'max:10'],
            'kecamatan_id' => ['nullable', 'string', 'max:10'],
            'desa_id' => ['nullable', 'string', 'max:20'],
            'alamat_lahan' => ['sometimes', 'required', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'id_komoditas' => ['nullable', 'string', 'exists:komoditas,id_komoditas'],
            'musim_tanam' => ['nullable', 'string', 'max:50'],
            'foto_lahan' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'keterangan' => ['nullable', 'string'],
        ];
    }

    /**
     * Pesan kustom validasi dalam Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_lahan.required' => 'Nama lahan wajib diisi.',
            'nama_lahan.max' => 'Nama lahan maksimal 150 karakter.',
            'luas.required' => 'Luas lahan wajib diisi.',
            'luas.numeric' => 'Luas lahan harus berupa angka desimal (hektare).',
            'luas.gt' => 'Luas lahan harus lebih besar dari 0 hektare.',
            'luas.max' => 'Luas lahan melebihi batas maksimum.',
            'alamat_lahan.required' => 'Alamat lahan wajib diisi.',
            'latitude.between' => 'Titik koordinat latitude harus berada di antara -90 dan 90 derajat.',
            'longitude.between' => 'Titik koordinat longitude harus berada di antara -180 dan 180 derajat.',
            'id_komoditas.exists' => 'Komoditas yang dipilih tidak terdaftar dalam sistem.',
            'musim_tanam.max' => 'Musim tanam maksimal 50 karakter.',
            'foto_lahan.image' => 'File foto lahan harus berupa gambar.',
            'foto_lahan.mimes' => 'Format foto lahan hanya boleh JPEG, JPG, PNG, atau WEBP.',
            'foto_lahan.max' => 'Ukuran foto lahan maksimal 2MB (2048 KB).',
        ];
    }

    /**
     * Tangani kegagalan validasi khusus endpoint API mobile dengan respons JSON 422 seragam.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validasi pembaruan lahan gagal. Periksa kembali input Anda.',
            'errors' => $validator->errors(),
        ], 422));
    }
}

