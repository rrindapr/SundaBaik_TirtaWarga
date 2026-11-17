<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Dipakai oleh form Ketua RT (LaporanController@store) dan endpoint
 * penerima data (PenerimaanLaporanController@store).
 *
 * PBI-02: validasi isian wajib.
 * Spasi di awal/akhir dan string kosong sudah diubah Laravel menjadi null
 * (middleware TrimStrings dan ConvertEmptyStringsToNull), jadi isian yang
 * hanya berisi spasi juga ditolak oleh aturan "required".
 */
class StoreLaporanRequest extends FormRequest
{
    public const ALAMAT_MIN = 5;
    public const ALAMAT_MAX = 255;
    public const KK_MAX = 10000;
    public const KETERANGAN_MAX = 1000;

    public function authorize(): bool
    {
        return true; // pembatasan role sudah ditangani middleware di route
    }

    public function rules(): array
    {
        return [
            'alamat'              => ['required', 'string', 'min:' . self::ALAMAT_MIN, 'max:' . self::ALAMAT_MAX],
            'jumlah_kk_terdampak' => ['required', 'integer', 'min:1', 'max:' . self::KK_MAX],
            'keterangan'          => ['nullable', 'string', 'max:' . self::KETERANGAN_MAX],
        ];
    }

    public function messages(): array
    {
        return [
            'alamat.required' => 'Alamat lengkap wajib diisi.',
            'alamat.string'   => 'Alamat lengkap harus berupa teks.',
            'alamat.min'      => 'Alamat terlalu pendek, tulis minimal :min karakter.',
            'alamat.max'      => 'Alamat maksimal :max karakter.',

            'jumlah_kk_terdampak.required' => 'Jumlah KK terdampak wajib diisi.',
            'jumlah_kk_terdampak.integer'  => 'Jumlah KK terdampak harus berupa angka bulat.',
            'jumlah_kk_terdampak.min'      => 'Jumlah KK terdampak minimal :min KK.',
            'jumlah_kk_terdampak.max'      => 'Jumlah KK terdampak maksimal :max KK.',

            'keterangan.string' => 'Catatan tambahan harus berupa teks.',
            'keterangan.max'    => 'Catatan tambahan maksimal :max karakter.',
        ];
    }

    public function attributes(): array
    {
        return [
            'alamat'              => 'alamat lengkap',
            'jumlah_kk_terdampak' => 'jumlah KK terdampak',
            'keterangan'          => 'catatan tambahan',
        ];
    }
}