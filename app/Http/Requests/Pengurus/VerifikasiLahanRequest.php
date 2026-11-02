<?php

namespace App\Http\Requests\Pengurus;

use App\Models\Lahan;
use Illuminate\Foundation\Http\FormRequest;

class VerifikasiLahanRequest extends FormRequest
{
    /**
     * Otorisasi verifikasi lahan menggunakan LahanPolicy.
     * Hanya Ketua aktif dari kelompok tani yang sama yang diizinkan.
     */
    public function authorize(): bool
    {
        $lahan = $this->route('lahan');
        if (is_string($lahan)) {
            $lahan = Lahan::find($lahan);
        }

        if (!$lahan) {
            return false;
        }

        $aksi = $this->input('aksi');

        if ($aksi === 'setujui') {
            return $this->user()?->can('verify', $lahan) ?? false;
        }

        if (in_array($aksi, ['perlu_perbaikan', 'cabut_verifikasi'])) {
            return $this->user()?->can('requestRevision', $lahan) ?? false;
        }

        return $this->user()?->can('verify', $lahan) ?? false;
    }

    /**
     * Sanitasi dan perlindungan mass assignment field internal verifikasi.
     * Mencegah pemalsuan verifikator_id, tanggal_verifikasi, maupun identitas pemilik.
     */
    protected function prepareForValidation(): void
    {
        $this->request->remove('verifikator_id');
        $this->request->remove('tanggal_verifikasi');
        $this->request->remove('id_pengguna');
        $this->request->remove('id_kelompok');
        $this->request->remove('nomor_revisi');
        $this->request->remove('status_operasional');
    }

    /**
     * Aturan validasi aksi verifikasi dan catatan perbaikan.
     * Catatan wajib diisi jika meminta perbaikan atau mencabut verifikasi.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'aksi' => ['required', 'string', 'in:setujui,perlu_perbaikan,cabut_verifikasi'],
            'catatan' => [
                'required_if:aksi,perlu_perbaikan,cabut_verifikasi',
                'nullable',
                'string',
                'min:5',
                'max:1000',
            ],
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
            'aksi.required' => 'Aksi verifikasi wajib dipilih.',
            'aksi.in' => 'Aksi verifikasi harus bernilai setujui, perlu_perbaikan, atau cabut_verifikasi.',
            'catatan.required_if' => 'Alasan atau catatan perbaikan wajib diisi ketika meminta perbaikan atau mencabut verifikasi.',
            'catatan.min' => 'Catatan verifikasi minimal 5 karakter.',
            'catatan.max' => 'Catatan verifikasi maksimal 1000 karakter.',
        ];
    }
}

