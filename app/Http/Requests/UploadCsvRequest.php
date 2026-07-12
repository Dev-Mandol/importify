<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadCsvRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $maxSize = config('uploads.max_size', 10240);
        $allowedMimes = implode(',', config('uploads.allowed_mimes', [
            'text/csv',
            'text/plain',
            'application/vnd.ms-excel',
        ]));

        return [
            'csv_file' => [
                'required',
                'file',
                'extensions:csv',
                'mimetypes:' . $allowedMimes,
                'max:' . $maxSize,
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        $maxSizeMb = round(config('uploads.max_size', 10240) / 1024, 2);

        return [
            'csv_file.required' => 'Please select a CSV file to upload.',
            'csv_file.file' => 'The uploaded item must be a valid file.',
            'csv_file.extensions' => 'The file must have a .csv extension.',
            'csv_file.mimetypes' => 'The file must be a valid CSV (allowed MIME types: text/csv, text/plain, application/vnd.ms-excel).',
            'csv_file.max' => "The CSV file must not be larger than {$maxSizeMb} MB.",
        ];
    }
}
