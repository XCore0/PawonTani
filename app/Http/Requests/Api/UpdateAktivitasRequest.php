<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAktivitasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        // Support alias lahan_id
        if (!$this->has('id_lahan') && $this->has('lahan_id')) {
            $this->merge(['id_lahan' => $this->input('lahan_id')]);
        }

        // Support alias condition -> kondisi_tanaman
        if (!$this->has('kondisi_tanaman') && $this->has('condition')) {
            $this->merge(['kondisi_tanaman' => $this->input('condition')]);
        }

        // Support alias notes -> catatan
        if (!$this->has('catatan') && $this->has('notes')) {
            $this->merge(['catatan' => $this->input('notes')]);
        }

        // Support alias title -> judul
        if (!$this->has('judul') && $this->has('title')) {
            $this->merge(['judul' => $this->input('title')]);
        }

        // Support alias day_number / dayNumber -> hari_setelah_tanam
        if (!$this->has('hari_setelah_tanam')) {
            if ($this->has('day_number')) {
                $this->merge(['hari_setelah_tanam' => $this->input('day_number')]);
            } elseif ($this->has('dayNumber')) {
                $this->merge(['hari_setelah_tanam' => $this->input('dayNumber')]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'id_lahan' => ['sometimes', 'required', 'string', 'exists:lahan,id_lahan'],
            'kategori' => ['sometimes', 'required', 'string', 'max:50'],
            'judul' => ['nullable', 'string', 'max:150'],
            'kondisi_tanaman' => ['nullable', 'string', 'max:50'],
            'tanggal' => ['sometimes', 'required', 'date'],
            'waktu' => ['nullable', 'string', 'max:30'],
            'hari_setelah_tanam' => ['nullable', 'integer', 'min:0'],
            'catatan' => ['nullable', 'string'],
            'biaya' => ['nullable', 'numeric', 'min:0'],
            'foto_aktivitas' => $this->hasFile('foto_aktivitas')
                ? ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']
                : ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_lahan.required' => 'Lahan wajib dipilih.',
            'id_lahan.exists' => 'Lahan yang dipilih tidak valid.',
            'kategori.required' => 'Kategori aktivitas wajib dipilih.',
            'tanggal.required' => 'Tanggal pelaksanaan aktivitas wajib diisi.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validasi pembaruan aktivitas gagal. Periksa kembali input Anda.',
            'errors' => $validator->errors(),
        ], 422));
    }
}

