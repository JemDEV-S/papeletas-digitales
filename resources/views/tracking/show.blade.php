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
                    @if(auth()->user()->hasRole('jefe_rrhh'))
                        <div class="mt-8 flex flex-wrap gap-4">
                            @if($tracking->tracking_status === 'pending')
                                <button onclick="openRegisterDepartureModal()"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 transition ease-in-out duration-150">
                                    <i class="fas fa-sign-out-alt mr-2"></i>
                                    Registrar Salida
                                </button>
                            @elseif(in_array($tracking->tracking_status, ['out', 'overdue']))
                                <button onclick="openRegisterReturnModal()"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 transition ease-in-out duration-150">
                                    <i class="fas fa-sign-in-alt mr-2"></i>
                                    Registrar Regreso
                                </button>
                            @endif

                            @if($tracking->departure_datetime)
                                <button onclick="openEditDepartureModal()"
                                        class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:border-yellow-900 focus:ring ring-yellow-300 transition ease-in-out duration-150">
                                    <i class="fas fa-edit mr-2"></i>
                                    Editar Salida
                                </button>
                            @endif

                            @if($tracking->return_datetime)
                                <button onclick="openEditReturnModal()"
                                        class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 active:bg-purple-900 focus:outline-none focus:border-purple-900 focus:ring ring-purple-300 transition ease-in-out duration-150">
                                    <i class="fas fa-edit mr-2"></i>
                                    Editar Regreso
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
        <!-- Modal para Registrar Salida -->
        <div id="registerDepartureModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal('registerDepartureModal')"></div>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            <i class="fas fa-sign-out-alt text-blue-600 mr-2"></i>
                            Registrar Salida
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha y Hora de Salida</label>
                                <input type="datetime-local" id="departure_datetime"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       max="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones (opcional)</label>
                                <textarea id="departure_notes" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                          placeholder="Ingrese observaciones si es necesario"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" onclick="submitRegisterDeparture()"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Registrar
                        </button>
                        <button type="button" onclick="closeModal('registerDepartureModal')"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para Registrar Regreso -->
        <div id="registerReturnModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal('registerReturnModal')"></div>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            <i class="fas fa-sign-in-alt text-green-600 mr-2"></i>
                            Registrar Regreso
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha y Hora de Regreso</label>
                                <input type="datetime-local" id="return_datetime"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                       max="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones (opcional)</label>
                                <textarea id="return_notes" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                          placeholder="Ingrese observaciones si es necesario"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" onclick="submitRegisterReturn()"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Registrar
                        </button>
                        <button type="button" onclick="closeModal('registerReturnModal')"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para Editar Salida -->
        <div id="editDepartureModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal('editDepartureModal')"></div>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            <i class="fas fa-edit text-yellow-600 mr-2"></i>
                            Editar Salida
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha y Hora de Salida</label>
                                <input type="datetime-local" id="edit_departure_datetime"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500"
                                       max="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                                <textarea id="edit_departure_notes" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500"
                                          placeholder="Ingrese observaciones"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" onclick="submitEditDeparture()"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Actualizar
                        </button>
                        <button type="button" onclick="closeModal('editDepartureModal')"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para Editar Regreso -->
        <div id="editReturnModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal('editReturnModal')"></div>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            <i class="fas fa-edit text-purple-600 mr-2"></i>
                            Editar Regreso
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha y Hora de Regreso</label>
                                <input type="datetime-local" id="edit_return_datetime"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                                       max="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                                <textarea id="edit_return_notes" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                                          placeholder="Ingrese observaciones"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" onclick="submitEditReturn()"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Actualizar
                        </button>
                        <button type="button" onclick="closeModal('editReturnModal')"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
        const trackingId = {{ $tracking->id }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Función auxiliar para convertir fecha local a formato datetime-local
        function toLocalDatetimeString(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            // Ajustar a zona horaria local
            const offset = date.getTimezoneOffset() * 60000;
            const localDate = new Date(date.getTime() - offset);
            return localDate.toISOString().slice(0, 16);
        }

        // Función auxiliar para convertir datetime-local a formato ISO
        // Función auxiliar para convertir datetime-local a formato Laravel
        function toISOString(datetimeLocal) {
            if (!datetimeLocal) return '';
            // Reemplazar la T con espacio para que coincida con el formato Y-m-d H:i:s
            return datetimeLocal.replace('T', ' ') + ':00';
        }

        // Funciones para abrir modales
        function openRegisterDepartureModal() {
            const now = new Date();
            const offset = now.getTimezoneOffset() * 60000;
            const localNow = new Date(now.getTime() - offset);
            document.getElementById('departure_datetime').value = localNow.toISOString().slice(0, 16);
            document.getElementById('departure_notes').value = '';
            document.getElementById('registerDepartureModal').classList.remove('hidden');
        }

        function openRegisterReturnModal() {
            const now = new Date();
            const offset = now.getTimezoneOffset() * 60000;
            const localNow = new Date(now.getTime() - offset);
            document.getElementById('return_datetime').value = localNow.toISOString().slice(0, 16);
            document.getElementById('return_notes').value = '';
            document.getElementById('registerReturnModal').classList.remove('hidden');
        }

        function openEditDepartureModal() {
            @if($tracking->departure_datetime)
                // Usar el formato Y-m-d\TH:i que ya está en hora peruana
                document.getElementById('edit_departure_datetime').value = '{{ $tracking->departure_datetime->format('Y-m-d\TH:i') }}';
                document.getElementById('edit_departure_notes').value = '{{ addslashes($tracking->notes ?? '') }}';
            @endif
            document.getElementById('editDepartureModal').classList.remove('hidden');
        }

        function openEditReturnModal() {
            @if($tracking->return_datetime)
                // Usar el formato Y-m-d\TH:i que ya está en hora peruana
                document.getElementById('edit_return_datetime').value = '{{ $tracking->return_datetime->format('Y-m-d\TH:i') }}';
                document.getElementById('edit_return_notes').value = '{{ addslashes($tracking->notes ?? '') }}';
            @endif
            document.getElementById('editReturnModal').classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Funciones para registrar
        function submitRegisterDeparture() {
            const datetime = document.getElementById('departure_datetime').value;
            const notes = document.getElementById('departure_notes').value;

            if (!datetime) {
                alert('Por favor seleccione una fecha y hora');
                return;
            }

            // Convertir a formato ISO con segundos
            const isoDatetime = toISOString(datetime);

            fetch('{{ route("tracking.api.register-departure") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    tracking_id: trackingId,
                    departure_datetime: isoDatetime,
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

        function submitRegisterReturn() {
            const datetime = document.getElementById('return_datetime').value;
            const notes = document.getElementById('return_notes').value;

            if (!datetime) {
                alert('Por favor seleccione una fecha y hora');
                return;
            }

            // Convertir a formato ISO con segundos
            const isoDatetime = toISOString(datetime);

            fetch('{{ route("tracking.api.register-return") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    tracking_id: trackingId,
                    return_datetime: isoDatetime,
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

        // Funciones para editar
        function submitEditDeparture() {
            const datetime = document.getElementById('edit_departure_datetime').value;
            const notes = document.getElementById('edit_departure_notes').value;

            if (!datetime) {
                alert('Por favor seleccione una fecha y hora');
                return;
            }

            if (!confirm('¿Está seguro de actualizar la fecha de salida?')) {
                return;
            }

            // Convertir a formato ISO con segundos
            const isoDatetime = toISOString(datetime);

            fetch('{{ route("tracking.api.update-departure") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    tracking_id: trackingId,
                    departure_datetime: isoDatetime,
                    notes: notes
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
                alert('Error al actualizar la salida');
            });
        }

        function submitEditReturn() {
            const datetime = document.getElementById('edit_return_datetime').value;
            const notes = document.getElementById('edit_return_notes').value;

            if (!datetime) {
                alert('Por favor seleccione una fecha y hora');
                return;
            }

            if (!confirm('¿Está seguro de actualizar la fecha de regreso?')) {
                return;
            }

            // Convertir a formato ISO con segundos
            const isoDatetime = toISOString(datetime);

            fetch('{{ route("tracking.api.update-return") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    tracking_id: trackingId,
                    return_datetime: isoDatetime,
                    notes: notes
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
                alert('Error al actualizar el regreso');
            });
        }
        </script>
    @endif
</x-app-layout>