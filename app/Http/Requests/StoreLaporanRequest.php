<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLaporanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
{
    return true; // pembatasan role sudah ditangani middleware di route
}

public function rules(): array
{
    return [
        'alamat'              => 'required|string|max:255',
        'jumlah_kk_terdampak' => 'required|integer|min:1',
        'keterangan'          => 'nullable|string',
    ];
}
}
