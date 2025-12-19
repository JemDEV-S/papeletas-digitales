<?php

namespace App\Http\Requests;

use App\Models\PermissionType;
use App\Models\PermissionRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequestRequest extends FormRequest
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

        return $permission->user_id === $user->id && $permission->isEditable();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'permission_type_id' => [
                'required',
                'exists:permission_types,id',
                Rule::exists('permission_types', 'id')->where('is_active', true),
            ],
            'reason' => [
                'required',
                'string',
                'min:10',
                'max:500',
            ],
            'documents' => [
                'array',
                'nullable',
            ],
            'documents.*' => [
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:2048', // 2MB
            ],
            'document_types' => [
                'array',
                'nullable',
            ],
            'document_types.*' => [
                'string',
                'in:certificado_medico,citacion,acreditacion,resolucion_nombramiento,horario_ensenanza,horario_recuperacion,partida_nacimiento,declaracion_jurada,otros',
            ],
            'skip_immediate_supervisor' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'permission_type_id.required' => 'Debe seleccionar un tipo de permiso.',
            'permission_type_id.exists' => 'El tipo de permiso seleccionado no es válido.',
            'reason.required' => 'Debe especificar el motivo del permiso.',
            'reason.min' => 'El motivo debe tener al menos 10 caracteres.',
            'reason.max' => 'El motivo no puede exceder 500 caracteres.',
            'documents.*.mimes' => 'Solo se permiten archivos PDF, JPG, JPEG y PNG.',
            'documents.*.max' => 'Los archivos no pueden exceder 2MB.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateRequiredDocuments($validator);
        });
    }

    /**
     * Validar documentos requeridos
     */
    private function validateRequiredDocuments($validator)
    {
        if (!$this->permission_type_id) {
            return;
        }

        $permissionType = PermissionType::find($this->permission_type_id);
        if (!$permissionType) {
            return;
        }

        $requiredDocs = $permissionType->getRequiredDocuments();
        
        if (empty($requiredDocs)) {
            return;
        }

        $uploadedTypes = $this->input('document_types', []);
        $missingDocs = array_diff($requiredDocs, $uploadedTypes);

        if (!empty($missingDocs)) {
            $docNames = $this->getDocumentNames($missingDocs);
            $validator->errors()->add(
                'documents',
                'Faltan documentos requeridos: ' . implode(', ', $docNames)
            );
        }
    }

    /**
     * Obtener nombres legibles de documentos
     */
    private function getDocumentNames(array $docTypes): array
    {
        $names = [
            'certificado_medico' => 'Certificado médico',
            'citacion' => 'Copia de citación',
            'acreditacion' => 'Acreditación',
            'resolucion_nombramiento' => 'Resolución de nombramiento',
            'horario_ensenanza' => 'Horario de enseñanza',
            'horario_recuperacion' => 'Horario de recuperación',
            'partida_nacimiento' => 'Partida de nacimiento',
            'declaracion_jurada' => 'Declaración jurada',
        ];

        return array_map(fn($type) => $names[$type] ?? $type, $docTypes);
    }
}