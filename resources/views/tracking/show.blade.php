<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle del Seguimiento') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Navigation -->
            <div class="mb-6">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('tracking.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                                <i class="fas fa-list mr-2"></i>
                                Seguimientos
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                                <span class="text-sm font-medium text-gray-500">Detalle</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">
                                Seguimiento de {{ $tracking->permissionRequest->user->name }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-600">
                                Permiso: {{ $tracking->permissionRequest->code }}
                            </p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                       bg-{{ $tracking->getStatusColor() }}-100 text-{{ $tracking->getStatusColor() }}-800">
                                {{ $tracking->getStatusLabel() }}
                            </span>
                            @if($tracking->isOverdue())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    RETRASADO
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Employee Information -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">
                                <i class="fas fa-user text-blue-600 mr-2"></i>
                                Información del Empleado
                            </h4>
                            
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Nombre:</span>
                                    <p class="text-sm text-gray-900">{{ $tracking->permissionRequest->user->name }}</p>
                                </div>
                                
                                <div>
                                    <span class="text-sm font-medium text-gray-500">DNI:</span>
                                    <p class="text-sm text-gray-900">{{ $tracking->employee_dni }}</p>
                                </div>
                                
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Email:</span>
                                    <p class="text-sm text-gray-900">{{ $tracking->permissionRequest->user->email }}</p>
                                </div>
                                
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Departamento:</span>
                                    <p class="text-sm text-gray-900">{{ $tracking->permissionRequest->user->department->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Permission Information -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">
                                <i class="fas fa-file-alt text-green-600 mr-2"></i>
                                Información del Permiso
                            </h4>
                            
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Código:</span>
                                    <p class="text-sm text-gray-900">{{ $tracking->permissionRequest->code }}</p>
                                </div>
                                
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Tipo:</span>
                                    <p class="text-sm text-gray-900">{{ $tracking->permissionRequest->permissionType->name ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Motivo:</span>
                                    <p class="text-sm text-gray-900">{{ $tracking->permissionRequest->reason }}</p>
                                </div>
                                
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Estado del Permiso:</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                               @if($tracking->permissionRequest->status === 'approved') bg-green-100 text-green-800
                                               @elseif($tracking->permissionRequest->status === 'in_progress') bg-blue-100 text-blue-800
                                               @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($tracking->permissionRequest->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tracking Timeline -->
                    <div class="mt-8">
                        <h4 class="text-lg font-medium text-gray-900 mb-6">
                            <i class="fas fa-clock text-orange-600 mr-2"></i>
                            Cronología del Seguimiento
                        </h4>

                        <div class="flow-root">
                            <ul class="-mb-8">
                                <!-- Permission Approved -->
                                <li>
                                    <div class="relative pb-8">
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                    <i class="fas fa-check text-white text-sm"></i>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">
                                                        Permiso aprobado y seguimiento iniciado
                                                    </p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    {{ $tracking->created_at->format('d/m/Y H:i') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                <!-- Departure -->
                                @if($tracking->departure_datetime)
                                    <li>
                                        <div class="relative pb-8">
                                            @if(!$tracking->return_datetime)
                                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                            @endif
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                        <i class="fas fa-sign-out-alt text-white text-sm"></i>
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-500">
                                                            Empleado registró su salida
                                                            @if($tracking->registeredByUser)
                                                                <span class="font-medium">
                                                                    (por {{ $tracking->registeredByUser->name }})
                                                                </span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                        {{ $tracking->departure_datetime->format('d/m/Y H:i') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @else
                                    <li>
                                        <div class="relative pb-8">
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                                                        <i class="fas fa-clock text-white text-sm"></i>
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-500">
                                                            Pendiente de registrar salida
                                                        </p>
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                        -
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endif

                                <!-- Return -->
                                @if($tracking->return_datetime)
                                    <li>
                                        <div class="relative">
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                        <i class="fas fa-sign-in-alt text-white text-sm"></i>
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-500">
                                                            Empleado registró su regreso
                                                            @if($tracking->registeredByUser)
                                                                <span class="font-medium">
                                                                    (por {{ $tracking->registeredByUser->name }})
                                                                </span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                        {{ $tracking->return_datetime->format('d/m/Y H:i') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @elseif($tracking->departure_datetime)
                                    <li>
                                        <div class="relative">
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full 
                                                           @if($tracking->tracking_status === 'overdue') bg-red-500 @else bg-yellow-500 @endif 
                                                           flex items-center justify-center ring-8 ring-white">
                                                        <i class="fas fa-hourglass-half text-white text-sm"></i>
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-500">
                                                            @if($tracking->tracking_status === 'overdue')
                                                                Empleado con retraso en el regreso
                                                            @else
                                                                Empleado fuera de oficina - pendiente de regreso
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                        @if($tracking->isOverdue())
                                                            <span class="text-red-600 font-medium">RETRASADO</span>
                                                        @else
                                                            En curso
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- Time Statistics -->
                    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 rounded-lg p-4">
                            <h5 class="text-sm font-medium text-blue-900 mb-2">Tiempo Fuera</h5>
                            @if($tracking->departure_datetime && $tracking->return_datetime)
                                @php
                                    $hours = floor($tracking->actual_hours_used);
                                    $minutes = round(($tracking->actual_hours_used - $hours) * 60);
                                @endphp
                                <p class="text-2xl font-bold text-blue-600">
                                    {{ $hours }}h {{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}m
                                </p>
                                <p class="text-xs text-blue-700">Tiempo total utilizado</p>
                            @elseif($tracking->departure_datetime)
                                @php
                                    $currentTime = now();
                                    $totalMinutes = $currentTime->diffInMinutes($tracking->departure_datetime);
                                    $hours = floor($totalMinutes / 60);
                                    $minutes = $totalMinutes % 60;
                                @endphp
                                <p class="text-2xl font-bold text-blue-600">
                                    {{ $hours }}h {{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}m
                                </p>
                                <p class="text-xs text-blue-700">Tiempo transcurrido</p>
                            @else
                                <p class="text-lg text-blue-400">-</p>
                                <p class="text-xs text-blue-700">No iniciado</p>
                            @endif
                        </div>

                        <div class="bg-green-50 rounded-lg p-4">
                            <h5 class="text-sm font-medium text-green-900 mb-2">Estado Actual</h5>
                            <p class="text-lg font-bold text-green-600">
                                {{ $tracking->getStatusLabel() }}
                            </p>
                            <p class="text-xs text-green-700">
                                Actualizado: {{ $tracking->updated_at->format('d/m/Y H:i') }}
                            </p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <h5 class="text-sm font-medium text-gray-900 mb-2">Registrado por</h5>
                            @if($tracking->registeredByUser)
                                <p class="text-lg font-bold text-gray-600">
                                    {{ $tracking->registeredByUser->name }}
                                </p>
                                <p class="text-xs text-gray-700">
                                    {{ $tracking->registeredByUser->role->name ?? 'Usuario' }}
                                </p>
                            @else
                                <p class="text-lg text-gray-400">Sistema</p>
                                <p class="text-xs text-gray-700">Automático</p>
                            @endif
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($tracking->notes)
                        <div class="mt-8">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">
                                <i class="fas fa-sticky-note text-yellow-600 mr-2"></i>
                                Observaciones
                            </h4>
                            <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $tracking->notes }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Actions for HR -->
                    @if(auth()->user()->hasRole('jefe_rrhh') && in_array($tracking->tracking_status, ['pending', 'out']))
                        <div class="mt-8 flex space-x-4">
                            @if($tracking->tracking_status === 'pending')
                                <button onclick="registerDeparture({{ $tracking->id }})"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 transition ease-in-out duration-150">
                                    <i class="fas fa-sign-out-alt mr-2"></i>
                                    Registrar Salida
                                </button>
                            @elseif($tracking->tracking_status === 'out')
                                <button onclick="registerReturn({{ $tracking->id }})"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 transition ease-in-out duration-150">
                                    <i class="fas fa-sign-in-alt mr-2"></i>
                                    Registrar Regreso
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Related Permission Request -->
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-link text-purple-600 mr-2"></i>
                        Solicitud de Permiso Relacionada
                    </h4>
                    
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $tracking->permissionRequest->code }}</p>
                            <p class="text-sm text-gray-500">{{ $tracking->permissionRequest->reason }}</p>
                        </div>
                        <a href="{{ route('permissions.show', $tracking->permissionRequest) }}" 
                           class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-external-link-alt mr-2"></i>
                            Ver Solicitud
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CSRF Token for AJAX requests -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if(auth()->user()->hasRole('jefe_rrhh'))
        <script>
        function registerDeparture(trackingId) {
            const notes = prompt('Observaciones (opcional):');
            if (notes === null) return; // User cancelled
            
            fetch('{{ route("tracking.api.register-departure") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ 
                    tracking_id: trackingId,
                    notes: notes || null
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al registrar la salida');
            });
        }
        
        function registerReturn(trackingId) {
            const notes = prompt('Observaciones (opcional):');
            if (notes === null) return; // User cancelled
            
            fetch('{{ route("tracking.api.register-return") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ 
                    tracking_id: trackingId,
                    notes: notes || null
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al registrar el regreso');
            });
        }
        </script>
    @endif
</x-app-layout>