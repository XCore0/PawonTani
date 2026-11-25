<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSiklusTanamRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && $user->status === 'Aktif' && $user->role === 'Anggota';
    }

    public function rules(): array
    {
        return [
            'id_lahan' => ['required', 'string', 'exists:lahan,id_lahan'],
            'id_komoditas' => ['required', 'string', 'exists:komoditas,id_komoditas'],
            'varietas' => ['nullable', 'string', 'max:100'],
            'musim_tanam' => ['required', 'string', 'max:50'],
            'tanggal_mulai' => ['required', 'date', 'before_or_equal:today'],
            'estimasi_panen' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'target_panen_ton' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_lahan.required' => 'Lahan wajib dipilih.',
            'id_lahan.exists' => 'Data lahan tidak valid atau tidak ditemukan.',
            'id_komoditas.required' => 'Komoditas wajib dipilih dari daftar master komoditas.',
            'id_komoditas.exists' => 'Komoditas yang dipilih tidak valid.',
            'musim_tanam.required' => 'Musim tanam wajib diisi.',
            'tanggal_mulai.required' => 'Tanggal mulai siklus tanam wajib diisi.',
            'tanggal_mulai.date' => 'Format tanggal mulai tidak valid.',
            'tanggal_mulai.before_or_equal' => 'Tanggal mulai tidak boleh melebihi tanggal hari ini.',
            'estimasi_panen.date' => 'Format estimasi panen tidak valid.',
            'estimasi_panen.after_or_equal' => 'Estimasi panen tidak boleh sebelum tanggal mulai.',
            'target_panen_ton.numeric' => 'Target panen harus berupa angka desimal.',
            'target_panen_ton.min' => 'Target panen tidak boleh bernilai negatif.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validasi gagal. Periksa kembali data yang dimasukkan.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
