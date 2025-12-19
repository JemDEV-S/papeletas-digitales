<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkApprovalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();

        // Verificar que hay usuario autenticado
        if (!$user) {
            return false;
        }

        return $user->isSupervisor() || $user->isHRChief();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'permission_ids' => [
                'required',
                'array',
                'min:1',
                'max:50', // Limitar a 50 solicitudes por lote
            ],
            'permission_ids.*' => [
                'exists:permission_requests,id',
            ],
            'bulk_comments' => [
                'nullable',
                'string',
                'max:500',
            ],
            'action' => [
                'required',
                'in:approve,reject',
            ],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'permission_ids.required' => 'Debe seleccionar al menos una solicitud.',
            'permission_ids.max' => 'No puede procesar más de 50 solicitudes a la vez.',
            'permission_ids.*.exists' => 'Una o más solicitudes seleccionadas no son válidas.',
            'action.required' => 'Debe especificar la acción a realizar.',
            'action.in' => 'La acción debe ser aprobar o rechazar.',
            'bulk_comments.max' => 'Los comentarios no pueden exceder 500 caracteres.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Si la acción es rechazar, los comentarios son obligatorios
            if ($this->input('action') === 'reject' && empty($this->input('bulk_comments'))) {
                $validator->errors()->add(
                    'bulk_comments',
                    'Los comentarios son obligatorios al rechazar solicitudes.'
                );
            }
        });
    }
}