<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <x-slot name="header">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ __('Mis Solicitudes') }}
                    </h2>
                </x-slot>
            <!-- Header -->
            <div class="mb-8">
                <div class="sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="mt-1 text-base text-gray-600">Gestione sus solicitudes de permisos</p>
                    </div>
                    <div class="mt-4 sm:mt-0">
                        <a href="{{ route('permissions.create') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Nueva Solicitud
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

            <!-- Estadísticas rápidas -->
            @if(isset($stats) && count($stats) > 0)
            <div class="mb-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @php
                    $totalRequests = collect($stats)->sum('total_requests');
                    $totalHours = collect($stats)->sum('total_hours');
                    $remainingHours = collect($stats)->where('remaining_hours', '!=', null)->sum('remaining_hours');
                    $activeTypes = collect($stats)->where('total_requests', '>', 0)->count();
                @endphp
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-semibold text-gray-900">{{ $totalRequests }}</p>
                            <p class="text-sm text-gray-600">Solicitudes este mes</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-semibold text-gray-900">{{ number_format($totalHours, 1) }}</p>
                            <p class="text-sm text-gray-600">Horas solicitadas</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-semibold text-gray-900">{{ number_format($remainingHours, 1) }}</p>
                            <p class="text-sm text-gray-600">Horas disponibles</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-semibold text-gray-900">{{ $activeTypes }}</p>
                            <p class="text-sm text-gray-600">Tipos utilizados</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Contenido principal -->
            @if($requests->count() > 0)
                <!-- Lista desktop -->
                <div class="hidden lg:block bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Historial de Solicitudes</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Solicitud
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tipo
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Creada
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Seguimiento
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($requests as $request)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $request->request_number }}</div>
                                                <div class="text-xs text-gray-500">{{ $request->created_at->format('d/m/Y') }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900 max-w-xs truncate">{{ $request->permissionType->name }}</div>
                                            @if($request->permissionType->with_pay)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                    Con goce
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                    Sin goce
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div>{{ $request->created_at->format('d/m/Y H:i') }}</div>
                                            <div class="text-xs text-gray-400">Solicitado</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($request->tracking)
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $request->tracking->getStatusLabel() }}
                                                </div>
                                                @if($request->tracking->actual_hours_used)
                                                    <div class="text-xs text-gray-500">{{ $request->tracking->actual_hours_used }}h usadas</div>
                                                @endif
                                            @else
                                                <div class="text-sm text-gray-500">Pendiente de seguimiento</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusColors = [
                                                    'draft' => 'gray',
                                                    'pending_immediate_boss' => 'yellow',
                                                    'pending_hr' => 'yellow',
                                                    'approved' => 'green',
                                                    'rejected' => 'red',
                                                    'cancelled' => 'gray'
                                                ];
                                                $color = $statusColors[$request->status] ?? 'gray';
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                                {{ $request->getStatusLabel() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <a href="{{ route('permissions.show', $request) }}" 
                                                   class="text-blue-600 hover:text-blue-500 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </a>
                                                @if($request->isEditable())
                                                    <a href="{{ route('permissions.edit', $request) }}" 
                                                       class="text-green-600 hover:text-green-500 transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </a>
                                                @endif
                                                @if($request->status === 'draft' && $request->canBeSubmitted())
                                                    <form action="{{ route('permissions.submit', $request) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="text-purple-600 hover:text-purple-500 transition-colors"
                                                                onclick="return confirm('¿Está seguro de enviar esta solicitud para aprobación?')">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Lista móvil -->
                <div class="lg:hidden space-y-4">
                    @foreach($requests as $request)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center">
                                        <h3 class="text-sm font-medium text-gray-900">{{ $request->request_number }}</h3>
                                        @php
                                            $statusColors = [
                                                'draft' => 'gray',
                                                'pending_immediate_boss' => 'yellow',
                                                'pending_hr' => 'yellow',
                                                'approved' => 'green',
                                                'rejected' => 'red',
                                                'cancelled' => 'gray'
                                            ];
                                            $color = $statusColors[$request->status] ?? 'gray';
                                        @endphp
                                        <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                            {{ $request->getStatusLabel() }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $request->created_at->format('d/m/Y') }}
                                    </div>
                                </div>

                                <div class="space-y-2 mb-4">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500">Tipo:</span>
                                        <span class="text-gray-900 font-medium truncate ml-2">{{ $request->permissionType->name }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500">Solicitado:</span>
                                        <span class="text-gray-900">{{ $request->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    @if($request->tracking)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-500">Seguimiento:</span>
                                            <span class="text-gray-900">{{ $request->tracking->getStatusLabel() }}</span>
                                        </div>
                                        @if($request->tracking->actual_hours_used)
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-500">Tiempo usado:</span>
                                                <span class="text-gray-900 font-medium">{{ $request->tracking->actual_hours_used }}h</span>
                                            </div>
                                        @endif
                                    @else
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-500">Seguimiento:</span>
                                            <span class="text-gray-500">No iniciado</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                                    <div class="flex items-center space-x-3">
                                        <a href="{{ route('permissions.show', $request) }}" 
                                           class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                            Ver
                                        </a>
                                        @if($request->isEditable())
                                            <a href="{{ route('permissions.edit', $request) }}" 
                                               class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors">
                                                Editar
                                            </a>
                                        @endif
                                    </div>
                                    @if($request->status === 'draft' && $request->canBeSubmitted())
                                        <form action="{{ route('permissions.submit', $request) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors"
                                                    onclick="return confirm('¿Está seguro de enviar esta solicitud para aprobación?')">
                                                Enviar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Paginación -->
                <div class="mt-6">
                    {{ $requests->links() }}
                </div>

            @else
                <!-- Estado vacío -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-200 text-center py-12">
                    <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No tiene solicitudes</h3>
                    <p class="text-gray-500 mb-6 max-w-sm mx-auto">
                        Aún no ha creado ninguna solicitud de permiso. Comience creando su primera solicitud.
                    </p>
                    <a href="{{ route('permissions.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Crear Primera Solicitud
                    </a>
                </div>
            @endif

            <!-- Leyenda de estados -->
            <div class="mt-8 bg-white shadow-sm rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Guía de Estados</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 mr-3">
                                Borrador
                            </span>
                            <span class="text-sm text-gray-600">Puede editar y enviar</span>
                        </div>
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mr-3">
                                Pendiente
                            </span>
                            <span class="text-sm text-gray-600">En proceso de aprobación</span>
                        </div>
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-3">
                                Aprobado
                            </span>
                            <span class="text-sm text-gray-600">Permiso autorizado</span>
                        </div>
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mr-3">
                                Rechazado
                            </span>
                            <span class="text-sm text-gray-600">Solicitud denegada</span>
                        </div>
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 mr-3">
                                Cancelado
                            </span>
                            <span class="text-sm text-gray-600">Cancelado por el usuario</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>