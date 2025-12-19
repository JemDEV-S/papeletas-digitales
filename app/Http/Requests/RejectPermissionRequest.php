<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectPermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $permission = $this->route('permission');
        $user = auth()->user();

        // Verificar que hay usuario autenticado y permiso válido
        if (!$user || !$permission) {
            return false;
        }

        return $user->canApprove($permission);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'comments' => [
                'required',
                'string',
                'min:10',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'comments.required' => 'Debe especificar el motivo del rechazo.',
            'comments.min' => 'El motivo del rechazo debe tener al menos 10 caracteres.',
            'comments.max' => 'El motivo del rechazo no puede exceder 500 caracteres.',
        ];
    }
}