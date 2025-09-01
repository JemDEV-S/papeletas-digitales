<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Seguimiento de Permisos') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-list-ul text-blue-600 mr-2"></i>
                            Historial de Seguimientos
                        </h3>
                        
                        @if(auth()->user()->hasRole('jefe_rrhh'))
                            <a href="{{ route('tracking.hr-dashboard') }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 transition ease-in-out duration-150 w-full sm:w-auto justify-center sm:justify-start">
                                <i class="fas fa-tachometer-alt mr-2"></i>
                                Dashboard RRHH
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Empleado
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tipo de Permiso
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha Salida
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha Regreso
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Horas Utilizadas
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Registrado por
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Acciones</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($trackings as $tracking)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-gray-700">
                                                        {{ strtoupper(substr($tracking->permissionRequest->user->name, 0, 2)) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $tracking->permissionRequest->user->name }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    DNI: {{ $tracking->employee_dni }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $tracking->permissionRequest->permissionType->name ?? 'N/A' }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $tracking->permissionRequest->code }}
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                       bg-{{ $tracking->getStatusColor() }}-100 text-{{ $tracking->getStatusColor() }}-800">
                                            {{ $tracking->getStatusLabel() }}
                                        </span>
                                        @if($tracking->isOverdue())
                                            <div class="text-xs text-red-600 mt-1">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                RETRASADO
                                            </div>
                                        @endif
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($tracking->departure_datetime)
                                            <div>{{ $tracking->departure_datetime->format('d/m/Y H:i') }}</div>
                                        @else
                                            <span class="text-gray-400">Pendiente</span>
                                        @endif
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($tracking->return_datetime)
                                            <div>{{ $tracking->return_datetime->format('d/m/Y H:i') }}</div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($tracking->actual_hours_used)
                                            <div class="font-medium">{{ number_format($tracking->actual_hours_used, 2) }} horas</div>
                                            @if($tracking->departure_datetime && $tracking->return_datetime)
                                                <div class="text-xs text-gray-400">
                                                    Tiempo real utilizado
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($tracking->registeredByUser)
                                            <div>{{ $tracking->registeredByUser->name }}</div>
                                            <div class="text-xs text-gray-400">
                                                {{ $tracking->updated_at->format('d/m/Y H:i') }}
                                            </div>
                                        @else
                                            <span class="text-gray-400">Sistema</span>
                                        @endif
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('tracking.show', $tracking) }}" 
                                           class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye mr-1"></i>
                                            Ver
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        <div class="flex flex-col items-center justify-center py-8">
                                            <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                                            <p class="text-lg font-medium text-gray-400">No hay seguimientos registrados</p>
                                            <p class="text-sm text-gray-400">Los seguimientos aparecerán aquí cuando se registren movimientos</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile/Tablet Card View -->
                <div class="lg:hidden">
                    @forelse($trackings as $tracking)
                        <div class="border-b border-gray-200 p-4 sm:p-6 hover:bg-gray-50">
                            <!-- Header with Employee Info and Status -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-700">
                                                {{ strtoupper(substr($tracking->permissionRequest->user->name, 0, 2)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $tracking->permissionRequest->user->name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            DNI: {{ $tracking->employee_dni }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end space-y-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                   bg-{{ $tracking->getStatusColor() }}-100 text-{{ $tracking->getStatusColor() }}-800">
                                        {{ $tracking->getStatusLabel() }}
                                    </span>
                                    @if($tracking->isOverdue())
                                        <div class="text-xs text-red-600">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            RETRASADO
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Permission Type -->
                            <div class="mb-4">
                                <div class="text-sm text-gray-900 font-medium">
                                    {{ $tracking->permissionRequest->permissionType->name ?? 'N/A' }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $tracking->permissionRequest->code }}
                                </div>
                            </div>

                            <!-- Details Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Fecha Salida</dt>
                                    <dd class="text-sm text-gray-900">
                                        @if($tracking->departure_datetime)
                                            {{ $tracking->departure_datetime->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-gray-400">Pendiente</span>
                                        @endif
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Fecha Regreso</dt>
                                    <dd class="text-sm text-gray-900">
                                        @if($tracking->return_datetime)
                                            {{ $tracking->return_datetime->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Horas Utilizadas</dt>
                                    <dd class="text-sm text-gray-900">
                                        @if($tracking->actual_hours_used)
                                            <div class="font-medium">{{ number_format($tracking->actual_hours_used, 2) }} horas</div>
                                            @if($tracking->departure_datetime && $tracking->return_datetime)
                                                <div class="text-xs text-gray-400">
                                                    Tiempo real utilizado
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Registrado por</dt>
                                    <dd class="text-sm text-gray-900">
                                        @if($tracking->registeredByUser)
                                            <div>{{ $tracking->registeredByUser->name }}</div>
                                            <div class="text-xs text-gray-400">
                                                {{ $tracking->updated_at->format('d/m/Y H:i') }}
                                            </div>
                                        @else
                                            <span class="text-gray-400">Sistema</span>
                                        @endif
                                    </dd>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <div class="flex justify-end">
                                <a href="{{ route('tracking.show', $tracking) }}" 
                                   class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-eye mr-2"></i>
                                    Ver Detalles
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 sm:p-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                                <p class="text-lg font-medium text-gray-400 mb-2">No hay seguimientos registrados</p>
                                <p class="text-sm text-gray-400">Los seguimientos aparecerán aquí cuando se registren movimientos</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if($trackings->hasPages())
                    <div class="px-4 sm:px-6 py-4 border-t border-gray-200">
                        {{ $trackings->links() }}
                    </div>
                @endif
            </div>

            <!-- Statistics Cards -->
            @if(auth()->user()->hasRole('jefe_rrhh'))
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mt-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center h-8 w-8 rounded-md bg-blue-500 text-white">
                                        <i class="fas fa-clock text-sm"></i>
                                    </div>
                                </div>
                                <div class="ml-4 sm:ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Total Seguimientos
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ $trackings->total() }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center h-8 w-8 rounded-md bg-green-500 text-white">
                                        <i class="fas fa-check text-sm"></i>
                                    </div>
                                </div>
                                <div class="ml-4 sm:ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Completados
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ $trackings->where('tracking_status', 'returned')->count() }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center h-8 w-8 rounded-md bg-yellow-500 text-white">
                                        <i class="fas fa-sign-out-alt text-sm"></i>
                                    </div>
                                </div>
                                <div class="ml-4 sm:ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Actualmente Fuera
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ $trackings->whereIn('tracking_status', ['out', 'overdue'])->count() }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center h-8 w-8 rounded-md bg-red-500 text-white">
                                        <i class="fas fa-exclamation-triangle text-sm"></i>
                                    </div>
                                </div>
                                <div class="ml-4 sm:ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Con Retraso
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ $trackings->where('tracking_status', 'overdue')->count() }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>