<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-4">
        <div class="max-w-full mx-auto px-2 sm:px-4 lg:px-6 xl:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="sm:flex sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center">
                            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $permission->request_number }}</h1>
                            @php
                                $statusColors = [
                                    'draft' => 'gray',
                                    'pending_immediate_boss' => 'yellow',
                                    'pending_hr' => 'yellow',
                                    'approved' => 'green',
                                    'rejected' => 'red',
                                    'cancelled' => 'gray'
                                ];
                                $color = $statusColors[$permission->status] ?? 'gray';
                            @endphp
                            <span class="ml-3 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                {{ $permission->getStatusLabel() }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-600">
                            Solicitud creada el {{ $permission->created_at->format('d/m/Y \a \l\a\s H:i') }}
                        </p>
                    </div>
                    <div class="mt-4 sm:mt-0">
                        <a href="{{ route('permissions.index') }}" 
                           class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Volver al listado
                        </a>
                    </div>
                </div>
            </div>

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

            {{-- Indicador de solicitud especial sin jefe inmediato --}}
            @if($permission->metadata['skip_immediate_supervisor'] ?? false)
                <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">
                                Solicitud Especial - Jefe Inmediato No Disponible
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>
                                    Esta solicitud fue marcada como <strong>especial</strong> porque el jefe inmediato
                                    <strong>{{ $permission->user->immediateSupervisor->name ?? 'N/A' }}</strong>
                                    no estaba disponible al momento del envío.
                                </p>
                                <p class="mt-1">
                                    <strong>Flujo de aprobación:</strong> RRHH aprueba ambos niveles (Nivel 1 y Nivel 2).
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Layout principal en 2 columnas -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <!-- Columna izquierda - Información de la solicitud -->
                <div class="xl:col-span-2 space-y-6">
                    <!-- Información del Solicitante y Detalles del Permiso -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Solicitante -->
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Información del Solicitante</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-lg font-medium text-gray-900">{{ $permission->user->full_name }}</p>
                                    <p class="text-sm text-gray-500">DNI: {{ $permission->user->dni }}</p>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Departamento</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $permission->user->department->name ?? 'Sin asignar' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Jefe Inmediato</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $permission->user->immediateSupervisor->full_name ?? 'Sin asignar' }}</dd>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles del Permiso -->
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Detalles del Permiso</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Tipo de Permiso</dt>
                                <dd class="mt-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-900">{{ $permission->permissionType->name }}</span>
                                        @if($permission->permissionType->with_pay)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Con goce
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Sin goce
                                            </span>
                                        @endif
                                    </div>
                                </dd>
                            </div>


                            <!-- Información del seguimiento -->
                            @if($permission->tracking)
                                <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                                    <div class="flex items-center mb-3">
                                        <svg class="h-5 w-5 text-indigo-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <h4 class="text-sm font-medium text-indigo-800">Estado del Seguimiento</h4>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <dt class="text-xs font-medium text-indigo-600">Estado</dt>
                                            <dd class="text-sm font-semibold text-indigo-900">{{ $permission->tracking->getStatusLabel() }}</dd>
                                        </div>
                                        @if($permission->tracking->departure_datetime)
                                            <div>
                                                <dt class="text-xs font-medium text-indigo-600">Salida registrada</dt>
                                                <dd class="text-sm text-indigo-900">{{ $permission->tracking->departure_datetime->format('d/m/Y H:i') }}</dd>
                                            </div>
                                        @endif
                                        @if($permission->tracking->return_datetime)
                                            <div>
                                                <dt class="text-xs font-medium text-indigo-600">Regreso registrado</dt>
                                                <dd class="text-sm text-indigo-900">{{ $permission->tracking->return_datetime->format('d/m/Y H:i') }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs font-medium text-indigo-600">Tiempo real utilizado</dt>
                                                <dd class="text-sm font-semibold text-indigo-900">{{ $permission->tracking->actual_hours_used }} horas</dd>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-600">Seguimiento</dt>
                                            <dd class="text-sm text-gray-500">Se creará automáticamente al aprobar la solicitud</dd>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Motivo -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Motivo de la Solicitud</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-900 leading-relaxed bg-gray-50 p-4 rounded-lg border border-gray-200">
                            {{ $permission->reason }}
                        </p>
                    </div>
                </div>

                <!-- Documentos Adjuntos -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">Documentos Adjuntos</h3>
                            <span class="text-sm text-gray-500">{{ $permission->documents->count() }} archivo(s)</span>
                        </div>
                    </div>
                    <div class="p-6">
                        @if($permission->documents->count() > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($permission->documents as $document)
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:bg-gray-100 transition-colors">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center mb-2">
                                                    <svg class="h-8 w-8 text-gray-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-medium text-gray-900 truncate">
                                                            {{ $document->getDocumentTypeLabel() }}
                                                        </p>
                                                        <p class="text-xs text-gray-500 truncate">{{ $document->original_name }}</p>
                                                    </div>
                                                </div>
                                                <p class="text-xs text-gray-400">{{ $document->human_file_size ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                        <div class="mt-3 flex items-center space-x-2">
                                            <a href="{{ $document->url }}" target="_blank" 
                                               class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 transition-colors">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                Ver
                                            </a>
                                            @if($permission->canUploadDocuments() && $permission->user_id === auth()->id())
                                                <form action="{{ route('permissions.documents.delete', [$permission, $document]) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 transition-colors"
                                                            onclick="return confirm('¿Está seguro de eliminar este documento?')">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                        Eliminar
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Formulario para agregar más documentos -->
                            @if($permission->canUploadDocuments() && $permission->user_id === auth()->id())
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="text-sm font-medium text-gray-900">Agregar Documento</h4>
                                        @if($permission->status === 'approved')
                                            @php
                                                $finalApprovalDate = $permission->getFinalApprovalDate();
                                                $deadline = $finalApprovalDate ? $finalApprovalDate->copy()->addHours(24) : null;
                                                $hoursLeft = $deadline ? now()->diffInHours($deadline, false) : null;
                                            @endphp
                                            @if($hoursLeft !== null && $hoursLeft > 0)
                                                <span class="text-xs text-orange-600 bg-orange-50 px-2 py-1 rounded-full">
                                                    ⏰ {{ round($hoursLeft) }}h restantes para subir documentos
                                                </span>
                                            @endif
                                        @elseif(in_array($permission->status, ['draft', 'pending_immediate_boss', 'pending_hr', 'in_progress']))
                                            <span class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full">
                                                📄 Documentos disponibles
                                            </span>
                                        @endif
                                    </div>
                                    <form action="{{ route('permissions.documents.upload', $permission) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 items-end">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-2">Tipo de documento</label>
                                                <select name="document_type" 
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                                                    <option value="certificado_medico">Certificado Médico</option>
                                                    <option value="citacion">Copia de Citación</option>
                                                    <option value="acreditacion">Acreditación</option>
                                                    <option value="resolucion_nombramiento">Resolución de Nombramiento</option>
                                                    <option value="horario_ensenanza">Horario de Enseñanza</option>
                                                    <option value="horario_recuperacion">Horario de Recuperación</option>
                                                    <option value="partida_nacimiento">Partida de Nacimiento</option>
                                                    <option value="declaracion_jurada">Declaración Jurada</option>
                                                    <option value="otros">Otros</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-2">Archivo</label>
                                                <input type="file" name="document" 
                                                       accept=".pdf,.jpg,.jpeg,.png" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                                       required>
                                            </div>
                                            <div>
                                                <button type="submit" 
                                                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                                    </svg>
                                                    Subir
                                                </button>
                                            </div>
                                        </div>
                                        <p class="mt-2 text-xs text-gray-500">
                                            Formatos permitidos: PDF, JPG, PNG. Tamaño máximo: 2MB
                                        </p>
                                    </form>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">No hay documentos adjuntos</p>
                                
                                @if($permission->canUploadDocuments() && $permission->user_id === auth()->id())
                                    <div class="mt-6">
                                        <div class="flex items-center justify-between mb-4">
                                            <h4 class="text-sm font-medium text-gray-900">Agregar Documento</h4>
                                            @if($permission->status === 'approved')
                                                @php
                                                    $finalApprovalDate = $permission->getFinalApprovalDate();
                                                    $deadline = $finalApprovalDate ? $finalApprovalDate->copy()->addHours(24) : null;
                                                    $hoursLeft = $deadline ? now()->diffInHours($deadline, false) : null;
                                                @endphp
                                                @if($hoursLeft !== null && $hoursLeft > 0)
                                                    <span class="text-xs text-orange-600 bg-orange-50 px-2 py-1 rounded-full">
                                                        ⏰ {{ round($hoursLeft) }}h restantes para subir documentos
                                                    </span>
                                                @endif
                                            @elseif(in_array($permission->status, ['draft', 'pending_immediate_boss', 'pending_hr', 'in_progress']))
                                                <span class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full">
                                                    📄 Documentos disponibles
                                                </span>
                                            @endif
                                        </div>
                                        <form action="{{ route('permissions.documents.upload', $permission) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="max-w-lg mx-auto grid grid-cols-1 gap-4">
                                                <select name="document_type" 
                                                        class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                                                    <option value="certificado_medico">Certificado Médico</option>
                                                    <option value="citacion">Copia de Citación</option>
                                                    <option value="acreditacion">Acreditación</option>
                                                    <option value="resolucion_nombramiento">Resolución de Nombramiento</option>
                                                    <option value="horario_ensenanza">Horario de Enseñanza</option>
                                                    <option value="horario_recuperacion">Horario de Recuperación</option>
                                                    <option value="partida_nacimiento">Partida de Nacimiento</option>
                                                    <option value="declaracion_jurada">Declaración Jurada</option>
                                                    <option value="otros">Otros</option>
                                                </select>
                                                <input type="file" name="document" 
                                                       accept=".pdf,.jpg,.jpeg,.png" 
                                                       class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                                       required>
                                                <button type="submit" 
                                                        class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                                    </svg>
                                                    Subir Documento
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Firma Digital FIRMA PERÚ -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-200" data-permission-id="{{ $permission->id }}">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">
                                <i class="fas fa-digital-tachograph text-blue-600 mr-2"></i>
                                Firmas Digitales FIRMA PERÚ
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
                                    <h4 class="font-medium text-sm">Etapa 1:</h4>
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
                                    <h4 class="font-medium text-sm">Etapa 2:</h4>
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
                                    <h4 class="font-medium text-sm">Etapa 3:</h4>
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

                        <!-- Botones de acción -->
                        <div class="signature-actions">
                            @if($canUserSign['can_sign'])
                                @if($canUserSign['stage'] === 1)
                                    <!-- Botón para firma de empleado -->
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h4 class="font-medium text-blue-900">Listo para firmar</h4>
                                                <p class="text-sm text-blue-700">Su solicitud está lista para ser firmada digitalmente.</p>
                                            </div>
                                            <button type="button" 
                                                    class="btn-sign-employee inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                                    data-permission-id="{{ $permission->id }}">
                                                <i class="fas fa-pen mr-2"></i>
                                                Firmar como Empleado
                                            </button>
                                        </div>
                                    </div>
                                @elseif($canUserSign['stage'] === 2)
                                    <!-- Botón para firma de jefe inmediato -->
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h4 class="font-medium text-yellow-900">Pendiente de su aprobación</h4>
                                                <p class="text-sm text-yellow-700">Esta solicitud requiere su aprobación y firma digital como jefe inmediato.</p>
                                            </div>
                                            <button type="button" 
                                                    class="btn-sign-level1 inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors"
                                                    data-permission-id="{{ $permission->id }}">
                                                <i class="fas fa-check-circle mr-2"></i>
                                                Aprobar y Firmar
                                            </button>
                                        </div>
                                    </div>
                                @elseif($canUserSign['stage'] === 3)
                                    <!-- Botón para firma de RRHH -->
                                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h4 class="font-medium text-purple-900">Aprobación final RRHH</h4>
                                                <p class="text-sm text-purple-700">Esta solicitud requiere la aprobación final y firma digital de RRHH.</p>
                                            </div>
                                            <button type="button" 
                                                    class="btn-sign-level2 inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors"
                                                    data-permission-id="{{ $permission->id }}">
                                                <i class="fas fa-stamp mr-2"></i>
                                                Aprobación Final RRHH
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <!-- Mensaje de estado -->
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                    <div class="text-center">
                                        @if($signatureStatus['is_fully_signed'])
                                            <i class="fas fa-check-circle text-green-600 text-2xl mb-2"></i>
                                            <h4 class="font-medium text-green-900">Documento completamente firmado</h4>
                                            <p class="text-sm text-green-700">Esta solicitud ha sido firmada digitalmente por todas las partes requeridas.</p>
                                        @else
                                            <i class="fas fa-hourglass-half text-gray-400 text-2xl mb-2"></i>
                                            <h4 class="font-medium text-gray-700">{{ $canUserSign['reason'] ?? 'En proceso de firma' }}</h4>
                                            <p class="text-sm text-gray-500">
                                                @if($signatureStatus['current_stage'] === 1)
                                                    Esperando firma del empleado
                                                @elseif($signatureStatus['current_stage'] === 2)
                                                    Esperando aprobación del jefe inmediato
                                                @elseif($signatureStatus['current_stage'] === 3)
                                                    Esperando aprobación final de RRHH
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Lista detallada de firmas -->
                        <div class="signature-status mt-6">
                            @if($signatureStatus['total_signatures'] > 0)
                                <h4 class="font-medium text-gray-900 mb-3">Detalle de firmas</h4>
                                <div class="space-y-2">
                                    @foreach($signatureStatus['signatures'] as $signature)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div class="flex items-center">
                                                @if($signature['stage'] === 1)
                                                    <i class="fas fa-user text-blue-600 mr-3"></i>
                                                @elseif($signature['stage'] === 2)
                                                    <i class="fas fa-user-tie text-yellow-600 mr-3"></i>
                                                @else
                                                    <i class="fas fa-building text-purple-600 mr-3"></i>
                                                @endif
                                                <div>
                                                    <p class="font-medium text-sm">{{ $signature['signer_name'] }}</p>
                                                    <p class="text-xs text-gray-500">{{ $signature['signer_dni'] ?? '' }}</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs text-gray-600">
                                                    {{ \Carbon\Carbon::parse($signature['signed_at'])->format('d/m/Y H:i') }}
                                                </p>
                                                <p class="text-xs {{ $signature['integrity_valid'] ? 'text-green-600' : 'text-red-600' }}">
                                                    <i class="fas fa-{{ $signature['integrity_valid'] ? 'shield-alt' : 'exclamation-triangle' }} mr-1"></i>
                                                    {{ $signature['integrity_valid'] ? 'Íntegra' : 'Comprometida' }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Historial de Aprobaciones -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Proceso de Aprobación</h3>
                    </div>
                    <div class="p-6">
                        @if($permission->approvals->count() > 0)
                            <div class="flow-root">
                                <ul class="-mb-8">
                                    @foreach($permission->approvals->sortBy('approval_level') as $index => $approval)
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
                                                        @elseif($approval->status === 'rejected')
                                                            <span class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                                                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                                </svg>
                                                            </span>
                                                        @else
                                                            <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                                                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
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
                                                            @if($approval->approved_at)
                                                                <p>{{ $approval->approved_at->format('d/m/Y') }}</p>
                                                                <p>{{ $approval->approved_at->format('H:i') }}</p>
                                                            @else
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                                    Pendiente
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">El proceso de aprobación aún no ha comenzado</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Acciones -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-200">
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row gap-3">
                            @if($permission->user_id === auth()->id())
                                @if($permission->isEditable())
                                    <a href="{{ route('permissions.edit', $permission) }}" 
                                       class="inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Editar Solicitud
                                    </a>
                                @endif

                                @if($permission->status === 'draft')
                                    @if($permission->canBeSubmitted())
                                        <form action="{{ route('permissions.submit', $permission) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                                    onclick="return confirm('¿Está seguro de enviar esta solicitud para aprobación?')">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                                </svg>
                                                Enviar para Aprobación
                                            </button>
                                        </form>
                                    @else
                                        <div class="inline-flex items-center px-4 py-2 border border-yellow-300 rounded-lg shadow-sm text-sm font-medium text-yellow-800 bg-yellow-50">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-2.694-.833-3.464 0L3.35 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                            </svg>
                                            @if(!$permission->hasRequiredDocuments())
                                                Debe completar los documentos requeridos
                                            @elseif(!$permission->hasEmployeeFirmaPeruSignature())
                                                Debe firmar digitalmente antes de enviar
                                            @else
                                                No se puede enviar la solicitud
                                            @endif
                                        </div>

                                        @if(!$permission->hasEmployeeFirmaPeruSignature())
                                            <form action="{{ route('permissions.submit-without-signature', $permission) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors"
                                                        onclick="return confirm('¿Está seguro de enviar esta solicitud SIN FIRMA DIGITAL para aprobación?\n\nNota: La solicitud se enviará sin su firma digital.')">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                                    </svg>
                                                    Enviar sin Firma Digital
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                @endif

                                @if(in_array($permission->status, ['draft', 'pending_immediate_boss']))
                                    <form action="{{ route('permissions.cancel', $permission) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
                                                onclick="return confirm('¿Está seguro de cancelar esta solicitud?')">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Cancelar Solicitud
                                        </button>
                                    </form>
                                @endif
                            @endif

                            @if(auth()->user()->canApprove($permission) && $permission->currentApproval() && $permission->currentApproval()->isPending())
                                <a href="{{ route('approvals.show', $permission) }}" 
                                   class="inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                    </svg>
                                    Revisar y Aprobar
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                
                </div>
                
                <!-- Columna derecha - Visualizador de PDF -->
                <div class="xl:col-span-1">
                    <div class="sticky top-4 space-y-4">
                        @if($permission->hasSignedDocument())
                        <!-- PDF Original (con firmas digitales intactas) -->
                        <div class="bg-white shadow-sm rounded-xl border border-gray-200">
                            <div class="px-4 py-3 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        <i class="fas fa-file-pdf text-red-600 mr-2"></i>
                                        Solicitud de Permiso
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
                                <div id="pdf-container" class="bg-gray-100 rounded-lg overflow-hidden" style="height: calc(50vh - 100px);">
                                    <iframe id="pdf-viewer"
                                            src="{{ route('permissions.pdf', $permission) }}#toolbar=0&navpanes=0&scrollbar=1&zoom=100"
                                            class="w-full h-full border-0 rounded-lg"
                                            title="Vista previa de la solicitud de permiso">
                                    </iframe>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($permission->hasTrackingPdf())
                        <!-- PDF de Tracking (separado) -->
                        <div class="bg-white shadow-sm rounded-xl border border-gray-200">
                            <div class="px-4 py-3 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        <i class="fas fa-clock text-blue-600 mr-2"></i>
                                        Registro de Tracking
                                        <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Completado
                                        </span>
                                    </h3>
                                    <div class="flex space-x-2">
                                        <button onclick="downloadTrackingPdf()"
                                                class="inline-flex items-center px-3 py-1 border border-gray-300 rounded text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                                title="Descargar Tracking">
                                            <i class="fas fa-download text-sm"></i>
                                        </button>
                                        <button onclick="printBothDocuments()"
                                                class="inline-flex items-center px-3 py-1 border border-transparent rounded text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                                title="Imprimir todo">
                                            <i class="fas fa-print text-sm mr-1"></i>
                                            Imprimir Todo
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="p-2">
                                <!-- Contenedor del PDF de tracking -->
                                <div id="tracking-pdf-container" class="bg-gray-100 rounded-lg overflow-hidden" style="height: calc(40vh - 100px);">
                                    <iframe id="tracking-pdf-viewer"
                                            src="{{ route('permissions.tracking-pdf', $permission) }}#toolbar=0&navpanes=0&scrollbar=1&zoom=100"
                                            class="w-full h-full border-0 rounded-lg"
                                            title="Registro de tracking">
                                    </iframe>
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
        @if($permission->hasTrackingPdf())
        const trackingPdfUrl = "{{ route('permissions.tracking-pdf', $permission) }}";
        @endif
        
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar visualizador PDF solo si existe
            if (document.getElementById('pdf-viewer')) {
                initializePdfViewer();
            }

            // Esperar a que jQuery esté disponible
            function initializeFirmaPeru() {
                if (typeof window.jqFirmaPeru !== 'undefined') {
                    console.log('✅ Inicializando FIRMA PERÚ...');
                    // Inicializar integración FIRMA PERÚ
                    window.firmaPeruIntegration = new FirmaPeruIntegration();

                    // Actualizar estado de firmas al cargar la página
                    window.firmaPeruIntegration.updateSignatureStatus();

                    // Configurar callback para actualizar PDF después de firma
                    window.firmaPeruIntegration.onSignatureComplete = function() {
                        console.log('✅ Firma completada - Actualizando vista PDF...');
                        // Recargar la página para mostrar el PDF generado
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
                    // Intentar acceder al contenido del iframe para verificar que se cargó
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

        @if($permission->hasTrackingPdf())
        function downloadTrackingPdf() {
            const link = document.createElement('a');
            link.href = trackingPdfUrl;
            link.download = 'tracking_{{ $permission->request_number }}.pdf';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function printBothDocuments() {
            // Abrir ambos PDFs en ventanas nuevas para que el usuario los imprima juntos
            const originalWindow = window.open(pdfUrl, '_blank');
            setTimeout(() => {
                const trackingWindow = window.open(trackingPdfUrl, '_blank');

                // Mensaje al usuario
                alert('Se han abierto ambos documentos en pestañas separadas.\n\n' +
                      '1. PDF de Solicitud (con firmas digitales)\n' +
                      '2. Página de Tracking\n\n' +
                      'Use Ctrl+P para imprimir cada uno o use "Imprimir como PDF" del navegador para combinarlos.');
            }, 500);
        }
        @endif
        
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

            // Verificar que el iframe existe
            if (!iframe) {
                console.log('⚠️ PDF viewer no encontrado, recargando página...');
                location.reload();
                return;
            }

            // Agregar timestamp para evitar caché
            const timestamp = new Date().getTime();
            const newUrl = pdfUrl + '?t=' + timestamp + '#toolbar=0&navpanes=0&scrollbar=0&zoom=' + currentZoom;

            console.log('🔄 Actualizando PDF viewer con URL:', newUrl);

            // Ocultar error div si estaba visible
            if (errorDiv) {
                errorDiv.classList.add('hidden');
            }
            iframe.style.display = 'block';

            // Cargar nueva URL
            iframe.src = newUrl;
        }
        
        // Atajos de teclado para el visualizador
        document.addEventListener('keydown', function(e) {
            // Solo activar si el foco está en el área del PDF y existe
            const pdfContainer = document.getElementById('pdf-container');
            if (pdfContainer && document.activeElement.closest('#pdf-container')) {
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
        const pdfContainer = document.getElementById('pdf-container');
        if (pdfContainer) {
            pdfContainer.setAttribute('tabindex', '0');
        }
    </script>
    @endpush
</x-app-layout>