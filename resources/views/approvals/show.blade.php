<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Revisar Solicitud de Permiso #{{ $permission->request_number }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto px-2 sm:px-4 lg:px-6 xl:px-8">
            
            <!-- Mensajes -->
            @if (session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Layout principal en 2 columnas -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                
                <!-- Columna izquierda - Información de la solicitud -->
                <div class="xl:col-span-2 space-y-6">

                    <!-- Información del Solicitante -->
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Información del Solicitante</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Empleado:</label>
                                    <p class="font-medium text-gray-900">{{ $permission->user->full_name }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">DNI:</label>
                                    <p class="font-medium text-gray-900">{{ $permission->user->dni }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Departamento:</label>
                                    <p class="font-medium text-gray-900">{{ $permission->user->department->name ?? 'Sin asignar' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Email:</label>
                                    <p class="font-medium text-gray-900">{{ $permission->user->email }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles del Permiso -->
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Detalles del Permiso</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Tipo de Permiso:</label>
                                    <p class="font-medium text-gray-900">{{ $permission->permissionType->name }}</p>
                                    @if($permission->permissionType->description)
                                        <p class="text-sm text-gray-500 mt-1">{{ $permission->permissionType->description }}</p>
                                    @endif
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Con/Sin Goce:</label>
                                    <p class="font-medium">
                                        @if($permission->permissionType->with_pay)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Con goce
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Sin goce
                                            </span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Fecha de Solicitud:</label>
                                    <p class="font-medium text-gray-900">{{ $permission->submitted_at ? $permission->submitted_at->format('d/m/Y H:i') : 'No enviada' }}</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <label class="text-sm font-medium text-gray-500">Motivo del Permiso:</label>
                                <div class="bg-gray-50 p-4 rounded-lg mt-1 border border-gray-200">
                                    <p class="text-gray-900">{{ $permission->reason }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documentos Adjuntos -->
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Documentos Adjuntos ({{ $permission->documents->count() }})</h3>
                        </div>
                        <div class="p-6">
                            @if($permission->documents->count() > 0)
                                <div class="space-y-3">
                                    @foreach($permission->documents as $document)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $document->getDocumentTypeLabel() }}</p>
                                                <p class="text-sm text-gray-600">
                                                    {{ $document->original_name }} ({{ $document->human_file_size }})
                                                </p>
                                            </div>
                                            <a href="{{ $document->url }}" target="_blank" 
                                               class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 transition-colors">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                Ver
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-6">
                                    <p class="text-gray-500">No hay documentos adjuntos.</p>
                                </div>
                            @endif
                            
                            @if($permission->permissionType->requires_document && $permission->documents->count() === 0)
                                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <p class="text-yellow-800 text-sm">
                                        <strong>Atención:</strong> Este tipo de permiso requiere documentación de respaldo, pero no se han adjuntado documentos.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Estado de Firmas Digitales FIRMA PERÚ -->
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200" data-permission-id="{{ $permission->id }}">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">
                                    <i class="fas fa-digital-tachograph text-blue-600 mr-2"></i>
                                    Estado de Firmas Digitales FIRMA PERÚ
                                </h3>
                                <button type="button" 
                                        class="btn-verify-signatures inline-flex items-center px-3 py-1 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                        data-permission-id="{{ $permission->id }}"
                                        title="Verificar integridad de firmas">
                                    <i class="fas fa-shield-alt mr-1"></i>
                                    Verificar
                                </button>
                            </div>
                        </div>
                        <div class="p-6">
                            @php
                                $signatureStatus = $permission->getFirmaPeruSignatureStatus();
                                $canUserSign = $permission->canUserSignFirmaPeru(auth()->user());
                            @endphp

                            <!-- Indicador de progreso de firmas -->
                            <div class="mb-6">
                                <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                                    <span>Progreso de firmas</span>
                                    <span>{{ $signatureStatus['total_signatures'] }} de 3 completadas</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                         style="width: {{ ($signatureStatus['total_signatures'] / 3) * 100 }}%"></div>
                                </div>
                            </div>

                            <!-- Etapas de firma -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <!-- Etapa 1: Empleado -->
                                <div class="border rounded-lg p-4 {{ $permission->hasEmployeeFirmaPeruSignature() ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }}">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-user {{ $permission->hasEmployeeFirmaPeruSignature() ? 'text-green-600' : 'text-gray-400' }} mr-2"></i>
                                        <h4 class="font-medium text-sm">Etapa 1: Empleado</h4>
                                        @if($permission->hasEmployeeFirmaPeruSignature())
                                            <i class="fas fa-check-circle text-green-600 ml-auto"></i>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-600">{{ $permission->user->name }}</p>
                                    @if($permission->hasEmployeeFirmaPeruSignature())
                                        @php $empSig = $signatureStatus['signatures']->firstWhere('stage', 1); @endphp
                                        <p class="text-xs text-green-600 mt-1">
                                            <i class="fas fa-clock mr-1"></i>
                                            Firmado el {{ \Carbon\Carbon::parse($empSig['signed_at'])->format('d/m/Y H:i') }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Etapa 2: Jefe Inmediato -->
                                <div class="border rounded-lg p-4 {{ $permission->hasLevel1FirmaPeruSignature() ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }}">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-user-tie {{ $permission->hasLevel1FirmaPeruSignature() ? 'text-green-600' : 'text-gray-400' }} mr-2"></i>
                                        <h4 class="font-medium text-sm">Etapa 2: Jefe Inmediato</h4>
                                        @if($permission->hasLevel1FirmaPeruSignature())
                                            <i class="fas fa-check-circle text-green-600 ml-auto"></i>
                                        @endif
                                    </div>
                                    @if($permission->hasLevel1FirmaPeruSignature())
                                        @php $supSig = $signatureStatus['signatures']->firstWhere('stage', 2); @endphp
                                        <p class="text-xs text-gray-600">{{ $supSig['signer_name'] ?? 'Jefe Inmediato' }}</p>
                                        <p class="text-xs text-green-600 mt-1">
                                            <i class="fas fa-clock mr-1"></i>
                                            Firmado el {{ \Carbon\Carbon::parse($supSig['signed_at'])->format('d/m/Y H:i') }}
                                        </p>
                                    @else
                                        <p class="text-xs text-gray-400">Pendiente de aprobación</p>
                                    @endif
                                </div>

                                <!-- Etapa 3: RRHH -->
                                <div class="border rounded-lg p-4 {{ $permission->hasLevel2FirmaPeruSignature() ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }}">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-building {{ $permission->hasLevel2FirmaPeruSignature() ? 'text-green-600' : 'text-gray-400' }} mr-2"></i>
                                        <h4 class="font-medium text-sm">Etapa 3: RRHH</h4>
                                        @if($permission->hasLevel2FirmaPeruSignature())
                                            <i class="fas fa-check-circle text-green-600 ml-auto"></i>
                                        @endif
                                    </div>
                                    @if($permission->hasLevel2FirmaPeruSignature())
                                        @php $hrSig = $signatureStatus['signatures']->firstWhere('stage', 3); @endphp
                                        <p class="text-xs text-gray-600">{{ $hrSig['signer_name'] ?? 'RRHH' }}</p>
                                        <p class="text-xs text-green-600 mt-1">
                                            <i class="fas fa-clock mr-1"></i>
                                            Firmado el {{ \Carbon\Carbon::parse($hrSig['signed_at'])->format('d/m/Y H:i') }}
                                        </p>
                                    @else
                                        <p class="text-xs text-gray-400">Pendiente de aprobación</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Historial de Aprobaciones Previas -->
                    @if($permission->approvals->where('status', '!=', 'pending')->count() > 0)
                        <div class="bg-white shadow-sm rounded-xl border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Aprobaciones Previas</h3>
                            </div>
                            <div class="p-6">
                                <div class="flow-root">
                                    <ul class="-mb-8">
                                        @foreach($permission->approvals->where('status', '!=', 'pending')->sortBy('approval_level') as $approval)
                                            <li>
                                                <div class="relative pb-8">
                                                    @if(!$loop->last)
                                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                    @endif
                                                    <div class="relative flex space-x-3">
                                                        <div>
                                                            @if($approval->status === 'approved')
                                                                <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                                    </svg>
                                                                </span>
                                                            @else
                                                                <span class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                                                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                                    </svg>
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                            <div>
                                                                <p class="text-sm font-medium text-gray-900">{{ $approval->getLevelLabel() }}</p>
                                                                <p class="text-sm text-gray-500">{{ $approval->approver->full_name }}</p>
                                                                @if($approval->comments)
                                                                    <div class="mt-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                                                        <p class="text-sm text-gray-700">{{ $approval->comments }}</p>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                                <p>{{ $approval->approved_at->format('d/m/Y') }}</p>
                                                                <p>{{ $approval->approved_at->format('H:i') }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Formulario de Decisión con Firma Digital -->
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Decisión de Aprobación</h3>
                        </div>
                        <div class="p-6">
                            
                            <!-- Estado actual -->
                            <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <p class="text-sm text-blue-800">
                                    <strong>Nivel de aprobación actual:</strong> 
                                    @if($permission->status === 'pending_immediate_boss')
                                        Pendiente de Jefe Inmediato (Nivel 1)
                                    @elseif($permission->status === 'pending_hr')
                                        Pendiente de Jefe de RRHH (Nivel 2)
                                    @endif
                                </p>
                            </div>

                            @php
                                $canUserSign = $permission->canUserSignFirmaPeru(auth()->user());
                            @endphp

                            <!-- Botones de Firma Digital y Aprobación -->
                            @if($canUserSign['can_sign'])
                                <div class="mb-6">
                                    @if($canUserSign['stage'] === 2)
                                        <!-- Botón para firma de jefe inmediato o RRHH en caso especial -->
                                        @php
                                            $isSpecialCase = $canUserSign['is_special_case'] ?? false;
                                        @endphp
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <h4 class="font-medium text-yellow-900">
                                                        Requiere su firma digital antes de aprobar
                                                        @if($isSpecialCase)
                                                            <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                                Caso Especial
                                                            </span>
                                                        @endif
                                                    </h4>
                                                    <p class="text-sm text-yellow-700">
                                                        @if($isSpecialCase)
                                                            Como jefe de RRHH, debe firmar digitalmente en representación del jefe inmediato no disponible.
                                                        @else
                                                            Para aprobar esta solicitud, primero debe firmarla digitalmente con FIRMA PERÚ.
                                                        @endif
                                                    </p>
                                                </div>
                                                <button type="button"
                                                        class="btn-sign-level1 inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors"
                                                        data-permission-id="{{ $permission->id }}">
                                                    <i class="fas fa-pen mr-2"></i>
                                                    Firmar Digitalmente
                                                </button>
                                            </div>
                                        </div>
                                    @elseif($canUserSign['stage'] === 3)
                                        <!-- Botón para firma de RRHH -->
                                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <h4 class="font-medium text-purple-900">Requiere su firma digital antes de aprobar</h4>
                                                    <p class="text-sm text-purple-700">Para dar la aprobación final de RRHH, primero debe firmar digitalmente con FIRMA PERÚ.</p>
                                                </div>
                                                <button type="button" 
                                                        class="btn-sign-level2 inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors"
                                                        data-permission-id="{{ $permission->id }}">
                                                    <i class="fas fa-stamp mr-2"></i>
                                                    Firmar Digitalmente
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Formularios de Aprobación/Rechazo -->
                            @php
                                $hasRequiredSignature = false;
                                if ($permission->status === 'pending_immediate_boss') {
                                    $hasRequiredSignature = $permission->hasLevel1FirmaPeruSignature();
                                } elseif ($permission->status === 'pending_hr') {
                                    $hasRequiredSignature = $permission->hasLevel2FirmaPeruSignature();
                                }
                                $isSpecialCase = ($permission->metadata['skip_immediate_supervisor'] ?? false) && $permission->current_approval_level === 1;
                            @endphp

                            {{-- Mensaje especial para casos sin jefe inmediato --}}
                            @if($isSpecialCase)
                                <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-5 rounded-r-lg shadow-sm">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <div class="w-12 h-12 bg-yellow-400 rounded-full flex items-center justify-center">
                                                <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <h4 class="text-lg font-bold text-yellow-900 mb-2">
                                                ⚠️ Aprobación Completa Requerida
                                            </h4>
                                            <div class="text-sm text-yellow-800 space-y-2">
                                                <p class="font-medium">
                                                    Esta solicitud requiere <strong>aprobación completa por RRHH</strong> porque el jefe inmediato
                                                    <strong>{{ $permission->user->immediateSupervisor->name ?? 'N/A' }}</strong> no estaba disponible.
                                                </p>
                                                <div class="bg-yellow-100 border border-yellow-300 rounded-lg p-3 mt-3">
                                                    <p class="font-semibold text-yellow-900 mb-2">
                                                        <i class="fas fa-info-circle mr-1"></i>
                                                        Al firmar y aprobar esta solicitud:
                                                    </p>
                                                    <ul class="list-disc list-inside space-y-1 text-yellow-800 ml-2">
                                                        <li>Aprobará <strong>ambos niveles</strong> (Jefe Inmediato + RRHH) con una sola firma</li>
                                                        <li>La solicitud pasará directamente a estado <strong>APROBADO</strong></li>
                                                        <li>Se creará el registro de seguimiento automáticamente</li>
                                                        <li>No se requerirá una segunda aprobación</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Formulario de Aprobación -->
                                <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                                    <h4 class="font-semibold text-green-800 mb-4">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        {{ $isSpecialCase ? 'Aprobación Completa (Ambos Niveles)' : 'Aprobar Solicitud' }}
                                    </h4>
                                    
                                    @if(!$hasRequiredSignature)
                                        <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                                            <p class="text-sm text-yellow-800">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                <strong>Debe firmar digitalmente antes de aprobar</strong>
                                            </p>
                                        </div>
                                    @endif
                                    
                                    <form action="{{ route('approvals.approve', $permission) }}" method="POST">
                                        @csrf
                                        <div class="mb-4">
                                            <label for="approve_comments" class="block text-sm font-medium text-gray-700 mb-2">
                                                Comentarios (opcional)
                                            </label>
                                            <textarea name="comments" id="approve_comments" rows="3" 
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                                placeholder="Comentarios adicionales..."></textarea>
                                        </div>
                                        <button type="submit"
                                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white {{ $hasRequiredSignature ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 cursor-not-allowed' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors"
                                                @if(!$hasRequiredSignature) disabled @endif
                                                onclick="return confirm('{{ $isSpecialCase ? '¿Está seguro de aprobar COMPLETAMENTE esta solicitud? Se aprobarán ambos niveles (Jefe Inmediato + RRHH) con esta acción.' : '¿Está seguro de aprobar esta solicitud?' }}')">
                                            <i class="fas fa-check mr-2"></i>
                                            @if($hasRequiredSignature)
                                                {{ $isSpecialCase ? 'Aprobar Completamente y Firmar' : 'Aprobar Solicitud' }}
                                            @else
                                                Firme Digitalmente Primero
                                            @endif
                                        </button>
                                    </form>
                                </div>

                                <!-- Formulario de Rechazo -->
                                <div class="bg-red-50 p-6 rounded-lg border border-red-200">
                                    <h4 class="font-semibold text-red-800 mb-4">
                                        <i class="fas fa-times-circle mr-2"></i>
                                        Rechazar Solicitud
                                    </h4>
                                    <form action="{{ route('approvals.reject', $permission) }}" method="POST">
                                        @csrf
                                        <div class="mb-4">
                                            <label for="reject_comments" class="block text-sm font-medium text-gray-700 mb-2">
                                                Motivo del rechazo <span class="text-red-500">*</span>
                                            </label>
                                            <textarea name="comments" id="reject_comments" rows="3" 
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                                placeholder="Explique el motivo del rechazo..." required></textarea>
                                        </div>
                                        <button type="submit" 
                                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
                                                onclick="return confirm('¿Está seguro de rechazar esta solicitud? Esta acción no se puede deshacer.')">
                                            <i class="fas fa-times mr-2"></i>
                                            Rechazar Solicitud
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="mt-6 text-center">
                                <a href="{{ route('approvals.index') }}" 
                                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                    </svg>
                                    Volver al listado de aprobaciones
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
                
                <!-- Columna derecha - Visualizador de PDF -->
                <div class="xl:col-span-1">
                    <div class="sticky top-4">
                        @if($permission->hasSignedDocument())
                            {{-- Solo mostrar visor si existe PDF generado --}}
                            <div class="bg-white shadow-sm rounded-xl border border-gray-200">
                                <div class="px-4 py-3 border-b border-gray-200">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg font-medium text-gray-900">
                                            <i class="fas fa-file-pdf text-red-600 mr-2"></i>
                                            Vista Previa PDF
                                            <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-signature mr-1"></i>
                                                Firmado
                                            </span>
                                        </h3>
                                    <div class="flex space-x-2">
                                        <button onclick="refreshPdfViewer()" 
                                                class="inline-flex items-center px-3 py-1 border border-gray-300 rounded text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                                title="Actualizar PDF">
                                            <i class="fas fa-sync-alt text-sm"></i>
                                        </button>
                                        <button onclick="downloadPdf()" 
                                                class="inline-flex items-center px-3 py-1 border border-gray-300 rounded text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                                title="Descargar PDF">
                                            <i class="fas fa-download text-sm"></i>
                                        </button>
                                        <button onclick="openPdfInNewTab()" 
                                                class="inline-flex items-center px-3 py-1 border border-gray-300 rounded text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                                title="Abrir en nueva pestaña">
                                            <i class="fas fa-external-link-alt text-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="p-2">
                                <!-- Contenedor del PDF -->
                                <div id="pdf-container" class="bg-gray-100 rounded-lg overflow-hidden" style="height: calc(100vh - 200px);">
                                    <iframe id="pdf-viewer"
                                            src="{{ route('permissions.pdf', $permission) }}#toolbar=0&navpanes=0&scrollbar=0&zoom=fit"
                                            class="w-full h-full border-0 rounded-lg"
                                            title="Vista previa de la solicitud de permiso">
                                    </iframe>
                                    
                                    <!-- Mensaje de error si no se puede cargar -->
                                    <div id="pdf-error" class="hidden flex flex-col items-center justify-center h-full text-gray-500">
                                        <i class="fas fa-exclamation-triangle text-4xl mb-4 text-yellow-500"></i>
                                        <p class="text-lg font-medium mb-2">No se pudo cargar la vista previa</p>
                                        <p class="text-sm mb-4">El PDF puede no estar disponible o su navegador no soporta la vista previa.</p>
                                        <a href="{{ route('permissions.pdf', $permission) }}" 
                                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                           target="_blank">
                                            <i class="fas fa-download mr-2"></i>
                                            Descargar PDF
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Controles de navegación para PDF -->
                                <div class="mt-3 flex items-center justify-center space-x-4 text-sm">
                                    <button onclick="zoomOut()" 
                                            class="inline-flex items-center px-2 py-1 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-50"
                                            title="Reducir zoom">
                                        <i class="fas fa-search-minus"></i>
                                    </button>
                                    <span id="zoom-level" class="text-gray-600 min-w-16 text-center">100%</span>
                                    <button onclick="zoomIn()" 
                                            class="inline-flex items-center px-2 py-1 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-50"
                                            title="Aumentar zoom">
                                        <i class="fas fa-search-plus"></i>
                                    </button>
                                    <span class="text-gray-400">|</span>
                                    <button onclick="fitToWidth()" 
                                            class="inline-flex items-center px-2 py-1 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-50"
                                            title="Ajustar al ancho">
                                        <i class="fas fa-arrows-alt-h"></i>
                                    </button>
                                    <button onclick="fitToPage()" 
                                            class="inline-flex items-center px-2 py-1 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-50"
                                            title="Ajustar a la página">
                                        <i class="fas fa-expand-arrows-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Mensaje cuando aún no existe PDF generado --}}
                        <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-8">
                            <div class="text-center">
                                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 mb-4">
                                    <i class="fas fa-file-pdf text-3xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">
                                    PDF No Disponible
                                </h3>
                                <p class="text-sm text-gray-600 mb-4">
                                    El documento PDF se generará después de que se complete la firma digital.
                                </p>
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-left">
                                    <p class="text-xs text-blue-800">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <strong>Nota:</strong> Debe firmar digitalmente la solicitud para generar el PDF. Una vez firmado, el documento aparecerá aquí automáticamente.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    @vite('resources/js/firma-peru.js')
    <script>
        window.firmaPeruRoutes = {
            initiateEmployee: "{{ route('api.firma-peru.initiate-employee', $permission) }}",
            initiateLevel1: "{{ route('api.firma-peru.initiate-level1', $permission) }}",
            initiateLevel2: "{{ route('api.firma-peru.initiate-level2', $permission) }}",
            signatureStatus: "{{ route('api.firma-peru.signature-status', $permission) }}",
            verifySignatures: "{{ route('api.firma-peru.verify-signatures', $permission) }}",
            param: "{{ route('api.firma-peru.parameters') }}"
        };
        // Variables globales para el visualizador PDF
        let currentZoom = 100;
        const pdfUrl = "{{ route('permissions.pdf', $permission) }}";
        
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar visualizador PDF solo si existe
            if (document.getElementById('pdf-viewer')) {
                initializePdfViewer();
            }
            
            // Esperar a que jQuery esté disponible
            function initializeFirmaPeru() {
                if (typeof window.jqFirmaPeru !== 'undefined') {
                    console.log('✅ Inicializando FIRMA PERÚ en vista de aprobaciones...');
                    // Inicializar integración FIRMA PERÚ
                    window.firmaPeruIntegration = new FirmaPeruIntegration();
                    
                    // Actualizar estado de firmas al cargar la página
                    window.firmaPeruIntegration.updateSignatureStatus();
                    
                    // Configurar callback para actualizar PDF después de firma
                    window.firmaPeruIntegration.onSignatureComplete = function() {
                        console.log('✅ Firma completada en aprobaciones - Actualizando vista...');

                        // Si existe el visor, actualizarlo; si no, recargar la página
                        if (document.getElementById('pdf-viewer')) {
                            refreshPdfViewer();
                        }

                        // Recargar la página después de un momento para actualizar estado de botones
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    };
                } else {
                    console.log('⏳ Esperando jQuery para FIRMA PERÚ...');
                    setTimeout(initializeFirmaPeru, 100);
                }
            }
            
            initializeFirmaPeru();
        });
        
        // Funciones del visualizador PDF
        function initializePdfViewer() {
            const iframe = document.getElementById('pdf-viewer');
            const errorDiv = document.getElementById('pdf-error');
            
            // Manejar error de carga del iframe
            iframe.addEventListener('error', function() {
                iframe.style.display = 'none';
                errorDiv.classList.remove('hidden');
            });
            
            // Verificar si el PDF se carga correctamente
            iframe.addEventListener('load', function() {
                try {
                    if (iframe.contentDocument === null) {
                        throw new Error('No se puede acceder al contenido del PDF');
                    }
                } catch (e) {
                    console.warn('PDF puede no estar cargado completamente:', e.message);
                }
            });
        }
        
        function downloadPdf() {
            const link = document.createElement('a');
            link.href = pdfUrl;
            link.download = 'solicitud_permiso_{{ $permission->request_number }}.pdf';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        function openPdfInNewTab() {
            window.open(pdfUrl, '_blank');
        }
        
        function zoomIn() {
            currentZoom += 25;
            if (currentZoom > 200) currentZoom = 200;
            updatePdfZoom();
        }
        
        function zoomOut() {
            currentZoom -= 25;
            if (currentZoom < 50) currentZoom = 50;
            updatePdfZoom();
        }
        
        function fitToWidth() {
            const iframe = document.getElementById('pdf-viewer');
            iframe.src = pdfUrl + '#toolbar=0&navpanes=0&scrollbar=0&zoom=fitH';
            document.getElementById('zoom-level').textContent = 'Ancho';
        }
        
        function fitToPage() {
            const iframe = document.getElementById('pdf-viewer');
            iframe.src = pdfUrl + '#toolbar=0&navpanes=0&scrollbar=0&zoom=fit';
            currentZoom = 100;
            document.getElementById('zoom-level').textContent = '100%';
        }
        
        function updatePdfZoom() {
            const iframe = document.getElementById('pdf-viewer');
            iframe.src = pdfUrl + '#toolbar=0&navpanes=0&scrollbar=0&zoom=' + currentZoom;
            document.getElementById('zoom-level').textContent = currentZoom + '%';
        }
        
        function refreshPdfViewer() {
            const iframe = document.getElementById('pdf-viewer');
            const errorDiv = document.getElementById('pdf-error');
            
            // Agregar timestamp para evitar caché
            const timestamp = new Date().getTime();
            const newUrl = pdfUrl + '?t=' + timestamp + '#toolbar=0&navpanes=0&scrollbar=0&zoom=' + currentZoom;
            
            console.log('🔄 Actualizando PDF viewer en aprobaciones con URL:', newUrl);
            
            // Ocultar error div si estaba visible
            errorDiv.classList.add('hidden');
            iframe.style.display = 'block';
            
            // Cargar nueva URL
            iframe.src = newUrl;
        }
        
        // Atajos de teclado para el visualizador
        document.addEventListener('keydown', function(e) {
            // Solo activar si el foco está en el área del PDF
            if (document.activeElement.closest('#pdf-container')) {
                switch(e.key) {
                    case '+':
                    case '=':
                        e.preventDefault();
                        zoomIn();
                        break;
                    case '-':
                        e.preventDefault();
                        zoomOut();
                        break;
                    case '0':
                        e.preventDefault();
                        fitToPage();
                        break;
                }
            }
        });
        
        // Hacer el contenedor PDF focusable para los atajos de teclado
        document.getElementById('pdf-container').setAttribute('tabindex', '0');
    </script>
    @endpush
</x-app-layout>