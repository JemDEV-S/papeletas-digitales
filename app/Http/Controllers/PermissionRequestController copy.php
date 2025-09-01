<?php

namespace App\Http\Controllers;

use App\Models\PermissionRequest;
use App\Models\PermissionType;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PermissionRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        $requests = $user->permissionRequests()
            ->with(['permissionType', 'approvals.approver'])
            ->latest()
            ->paginate(10);

        return view('permissions.index', compact('requests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissionTypes = PermissionType::active()->get();
        
        return view('permissions.create', compact('permissionTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'permission_type_id' => 'required|exists:permission_types,id',
            'start_datetime' => 'required|date|after:now',
            'end_datetime' => 'required|date|after:start_datetime',
            'reason' => 'required|string|max:500',
            'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();
        
        try {
            $permissionType = PermissionType::find($validated['permission_type_id']);
            
            // Crear la solicitud
            $permissionRequest = Auth::user()->permissionRequests()->create([
                'permission_type_id' => $validated['permission_type_id'],
                'start_datetime' => $validated['start_datetime'],
                'end_datetime' => $validated['end_datetime'],
                'reason' => $validated['reason'],
                'status' => PermissionRequest::STATUS_DRAFT,
            ]);

            // Calcular horas
            $permissionRequest->requested_hours = $permissionRequest->calculateHours();
            
            // Validar restricciones del tipo de permiso
            $validationErrors = $this->validatePermissionRestrictions($permissionRequest, $permissionType);
            
            if (!empty($validationErrors)) {
                DB::rollBack();
                return back()->withErrors($validationErrors)->withInput();
            }
            
            $permissionRequest->save();

            // Subir documentos si se requieren
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $index => $file) {
                    $path = $file->store('permission-documents/' . date('Y/m'), 'public');
                    
                    Document::create([
                        'permission_request_id' => $permissionRequest->id,
                        'original_name' => $file->getClientOriginalName(),
                        'stored_name' => basename($path),
                        'file_path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'document_type' => $request->input('document_types.' . $index, 'otros'),
                        'file_hash' => hash_file('sha256', $file->getRealPath()),
                    ]);
                }
            }

            DB::commit();
            
            return redirect()->route('permissions.show', $permissionRequest)
                ->with('success', 'Solicitud creada exitosamente. Puede editarla antes de enviarla.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear la solicitud: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PermissionRequest $permission)
    {
        // Verificar que el usuario puede ver esta solicitud
        if (!$this->canView($permission)) {
            abort(403, 'No tiene permisos para ver esta solicitud.');
        }

        $permission->load(['user', 'permissionType', 'documents', 'approvals.approver']);
        
        return view('permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PermissionRequest $permission)
    {
        // Solo se pueden editar borradores propios
        if ($permission->user_id !== Auth::id() || !$permission->isEditable()) {
            abort(403, 'No puede editar esta solicitud.');
        }

        $permissionTypes = PermissionType::active()->get();
        
        return view('permissions.edit', compact('permission', 'permissionTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PermissionRequest $permission)
    {
        // Solo se pueden actualizar borradores propios
        if ($permission->user_id !== Auth::id() || !$permission->isEditable()) {
            abort(403, 'No puede actualizar esta solicitud.');
        }

        $validated = $request->validate([
            'permission_type_id' => 'required|exists:permission_types,id',
            'start_datetime' => 'required|date|after:now',
            'end_datetime' => 'required|date|after:start_datetime',
            'reason' => 'required|string|max:500',
        ]);

        $permission->update($validated);
        $permission->requested_hours = $permission->calculateHours();
        $permission->save();

        // Si el usuario eligió "Guardar y Enviar"
        if ($request->input('submit_after_save')) {
            if ($permission->canBeSubmitted()) {
                $permission->submit();
                return redirect()->route('permissions.show', $permission)
                    ->with('success', 'Solicitud actualizada y enviada para aprobación exitosamente.');
            } else {
                return redirect()->route('permissions.show', $permission)
                    ->with('warning', 'Solicitud actualizada, pero no puede ser enviada. Verifique los documentos requeridos.');
            }
        }

        return redirect()->route('permissions.show', $permission)
            ->with('success', 'Solicitud actualizada exitosamente.');
    }

    /**
     * Submit the permission request for approval
     */
    public function submit(PermissionRequest $permission)
    {
        if ($permission->user_id !== Auth::id()) {
            abort(403, 'No puede enviar esta solicitud.');
        }

        if (!$permission->canBeSubmitted()) {
            return back()->with('error', 'La solicitud no puede ser enviada. Verifique que tenga todos los documentos requeridos.');
        }

        // En lugar de enviar directamente, redirigir al proceso de firma
        return redirect()->route('permissions.signature-process', $permission)
                ->with('info', 'Antes de enviar su solicitud, debe firmarla digitalmente.');
        }

    /**
     * Cancel a permission request
     */
    public function cancel(PermissionRequest $permission)
    {
        if ($permission->user_id !== Auth::id()) {
            abort(403, 'No puede cancelar esta solicitud.');
        }

        if ($permission->cancel()) {
            return redirect()->route('permissions.index')
                ->with('success', 'Solicitud cancelada exitosamente.');
        }

        return back()->with('error', 'No se puede cancelar esta solicitud en su estado actual.');
    }

    /**
     * Upload additional documents
     */
    public function uploadDocument(Request $request, PermissionRequest $permission)
    {
        if ($permission->user_id !== Auth::id() || !$permission->isEditable()) {
            abort(403, 'No puede agregar documentos a esta solicitud.');
        }

        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'document_type' => 'required|string',
        ]);

        $file = $request->file('document');
        $path = $file->store('permission-documents/' . date('Y/m'), 'public');
        
        Document::create([
            'permission_request_id' => $permission->id,
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => basename($path),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'document_type' => $request->document_type,
            'file_hash' => hash_file('sha256', $file->getRealPath()),
        ]);

        return back()->with('success', 'Documento agregado exitosamente.');
    }

    /**
     * Delete a document
     */
    public function deleteDocument(PermissionRequest $permission, Document $document)
    {
        if ($permission->user_id !== Auth::id() || !$permission->isEditable()) {
            abort(403, 'No puede eliminar documentos de esta solicitud.');
        }

        if ($document->permission_request_id !== $permission->id) {
            abort(404);
        }

        $document->delete();

        return back()->with('success', 'Documento eliminado exitosamente.');
    }

    /**
     * Validate permission restrictions based on type
     */
    private function validatePermissionRestrictions(PermissionRequest $request, PermissionType $type): array
    {
        $errors = [];
        $user = Auth::user();
        
        // Validar límite diario
        if ($type->hasDailyLimit() && $request->requested_hours > $type->max_hours_per_day) {
            $errors[] = "Las horas solicitadas exceden el límite diario de {$type->max_hours_per_day} horas para este tipo de permiso.";
        }

        // Validar límite mensual
        if ($type->hasMonthlyLimit()) {
            $month = $request->start_datetime->month;
            $year = $request->start_datetime->year;
            
            $monthlyHours = $user->permissionRequests()
                ->where('permission_type_id', $type->id)
                ->whereYear('start_datetime', $year)
                ->whereMonth('start_datetime', $month)
                ->whereNotIn('status', ['rejected', 'cancelled'])
                ->sum('requested_hours');
            
            if (($monthlyHours + $request->requested_hours) > $type->max_hours_per_month) {
                $remaining = $type->max_hours_per_month - $monthlyHours;
                $errors[] = "Excede el límite mensual. Solo le quedan {$remaining} horas disponibles este mes.";
            }
        }

        // Validar frecuencia mensual
        if ($type->hasFrequencyLimit()) {
            $month = $request->start_datetime->month;
            $year = $request->start_datetime->year;
            
            $monthlyCount = $user->permissionRequests()
                ->where('permission_type_id', $type->id)
                ->whereYear('start_datetime', $year)
                ->whereMonth('start_datetime', $month)
                ->whereNotIn('status', ['rejected', 'cancelled'])
                ->count();
            
            if ($monthlyCount >= $type->max_times_per_month) {
                $errors[] = "Ya ha alcanzado el límite de {$type->max_times_per_month} permisos de este tipo por mes.";
            }
        }

        return $errors;
    }

    /**
     * Check if user can view a permission request
     */
    private function canView(PermissionRequest $permission): bool
    {
        $user = Auth::user();
        
        // Puede ver si es suya
        if ($permission->user_id === $user->id) {
            return true;
        }
        
        // Puede ver si es supervisor del solicitante
        if ($permission->user->immediate_supervisor_id === $user->id) {
            return true;
        }
        
        // Puede ver si es RRHH o Admin
        if ($user->hasRole('jefe_rrhh') || $user->hasRole('admin')) {
            return true;
        }
        
        return false;
    }
}