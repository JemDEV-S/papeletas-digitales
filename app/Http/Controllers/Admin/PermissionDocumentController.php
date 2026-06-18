<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\PermissionRequest;
use App\Models\PermissionType;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class PermissionDocumentController extends Controller
{
    public function __construct(
        private PermissionService $permissionService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:50',
            'permission_type_id' => 'nullable|exists:permission_types,id',
        ]);

        $permissions = PermissionRequest::query()
            ->with(['user.department', 'permissionType', 'documents'])
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('request_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('dni', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['permission_type_id'] ?? null, fn ($query, int $typeId) => $query->where('permission_type_id', $typeId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $permissionTypes = PermissionType::active()->orderBy('name')->get(['id', 'name']);
        $statuses = [
            PermissionRequest::STATUS_DRAFT => 'Borrador',
            PermissionRequest::STATUS_PENDING_IMMEDIATE_BOSS => 'Pendiente Jefe Inmediato',
            PermissionRequest::STATUS_PENDING_HR => 'Pendiente RRHH',
            PermissionRequest::STATUS_APPROVED => 'Aprobado',
            PermissionRequest::STATUS_IN_PROGRESS => 'En curso',
            PermissionRequest::STATUS_REJECTED => 'Rechazado',
            PermissionRequest::STATUS_CANCELLED => 'Cancelado',
        ];

        return view('admin.permissions.index', compact('permissions', 'permissionTypes', 'statuses', 'filters'));
    }

    public function store(Request $request, PermissionRequest $permission)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'document_type' => 'required|string|in:certificado_medico,citacion,acreditacion,resolucion_nombramiento,horario_ensenanza,horario_recuperacion,partida_nacimiento,declaracion_jurada,otros',
        ]);

        try {
            $this->permissionService->uploadDocuments(
                $permission,
                [$request->file('document')],
                [$request->document_type]
            );

            return back()->with('success', 'Sustento adjuntado por administración correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al adjuntar el sustento: ' . $e->getMessage());
        }
    }

    public function destroy(PermissionRequest $permission, Document $document)
    {
        if ((int) $document->permission_request_id !== (int) $permission->id) {
            abort(404);
        }

        try {
            $this->permissionService->deleteDocument($document);

            return back()->with('success', 'Sustento eliminado por administración correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el sustento: ' . $e->getMessage());
        }
    }
}
