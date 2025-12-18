<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Seguimiento de Permisos') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(auth()->user()->hasRole('jefe_rrhh'))
                <div class="mb-6">
                    <a href="{{ route('tracking.hr-dashboard') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 transition ease-in-out duration-150">
                        <i class="fas fa-tachometer-alt mr-2"></i>
                        Dashboard RRHH
                    </a>
                </div>
            @endif

            <!-- Filtros de Búsqueda -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-filter text-blue-600 mr-2"></i>
                            Filtros de Búsqueda
                        </h3>
                        <button type="button" onclick="toggleFilters()" class="text-sm text-blue-600 hover:text-blue-800">
                            <i class="fas fa-chevron-down" id="filterIcon"></i>
                            <span id="filterText">Mostrar</span>
                        </button>
                    </div>

                    <div id="filterForm" style="display: none;">
                        <form method="GET" action="{{ route('tracking.index') }}" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <!-- Nombre -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Nombre del Empleado
                                    </label>
                                    <input type="text"
                                           name="name"
                                           id="name"
                                           value="{{ request('name') }}"
                                           placeholder="Buscar por nombre"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <!-- DNI -->
                                <div>
                                    <label for="dni" class="block text-sm font-medium text-gray-700 mb-1">
                                        DNI
                                    </label>
                                    <input type="text"
                                           name="dni"
                                           id="dni"
                                           value="{{ request('dni') }}"
                                           placeholder="Buscar por DNI"
                                           maxlength="8"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <!-- Estado -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                        Estado
                                    </label>
                                    <select name="status"
                                            id="status"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Todos los estados</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente de Salida</option>
                                        <option value="out" {{ request('status') == 'out' ? 'selected' : '' }}>Fuera de Oficina</option>
                                        <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Ha Regresado</option>
                                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Retraso en Regreso</option>
                                    </select>
                                </div>

                                <!-- Tipo de Permiso -->
                                <div>
                                    <label for="permission_type" class="block text-sm font-medium text-gray-700 mb-1">
                                        Tipo de Permiso
                                    </label>
                                    <select name="permission_type"
                                            id="permission_type"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Todos los tipos</option>
                                        @foreach($permissionTypes as $type)
                                            <option value="{{ $type->id }}" {{ request('permission_type') == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Código de Permiso -->
                                <div>
                                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                                        Código de Permiso
                                    </label>
                                    <input type="text"
                                           name="code"
                                           id="code"
                                           value="{{ request('code') }}"
                                           placeholder="Ej: PERM-2024-001"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <!-- Solo con Retraso -->
                                <div class="flex items-center pt-6">
                                    <input type="checkbox"
                                           name="overdue"
                                           id="overdue"
                                           value="1"
                                           {{ request('overdue') == '1' ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <label for="overdue" class="ml-2 text-sm font-medium text-gray-700">
                                        Solo mostrar retrasos
                                    </label>
                                </div>
                            </div>

                            <!-- Filtros de Fechas -->
                            <div class="border-t pt-4 mt-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">Filtros por Fecha</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <!-- Fecha Salida Desde -->
                                    <div>
                                        <label for="departure_from" class="block text-sm font-medium text-gray-700 mb-1">
                                            Salida Desde
                                        </label>
                                        <input type="date"
                                               name="departure_from"
                                               id="departure_from"
                                               value="{{ request('departure_from') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>

                                    <!-- Fecha Salida Hasta -->
                                    <div>
                                        <label for="departure_to" class="block text-sm font-medium text-gray-700 mb-1">
                                            Salida Hasta
                                        </label>
                                        <input type="date"
                                               name="departure_to"
                                               id="departure_to"
                                               value="{{ request('departure_to') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>

                                    <!-- Fecha Regreso Desde -->
                                    <div>
                                        <label for="return_from" class="block text-sm font-medium text-gray-700 mb-1">
                                            Regreso Desde
                                        </label>
                                        <input type="date"
                                               name="return_from"
                                               id="return_from"
                                               value="{{ request('return_from') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>

                                    <!-- Fecha Regreso Hasta -->
                                    <div>
                                        <label for="return_to" class="block text-sm font-medium text-gray-700 mb-1">
                                            Regreso Hasta
                                        </label>
                                        <input type="date"
                                               name="return_to"
                                               id="return_to"
                                               value="{{ request('return_to') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="flex flex-col sm:flex-row gap-3 pt-4">
                                <button type="submit"
                                        class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <i class="fas fa-search mr-2"></i>
                                    Buscar
                                </button>
                                <a href="{{ route('tracking.index') }}"
                                   class="inline-flex items-center justify-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <i class="fas fa-times mr-2"></i>
                                    Limpiar Filtros
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Mostrar filtros activos -->
                    @if(request()->hasAny(['name', 'dni', 'status', 'permission_type', 'code', 'departure_from', 'departure_to', 'return_from', 'return_to', 'overdue']))
                        <div class="mt-4 pt-4 border-t">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-medium text-gray-700">Filtros activos:</span>
                                @if(request('name'))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Nombre: {{ request('name') }}
                                    </span>
                                @endif
                                @if(request('dni'))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        DNI: {{ request('dni') }}
                                    </span>
                                @endif
                                @if(request('status'))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Estado: {{ ucfirst(request('status')) }}
                                    </span>
                                @endif
                                @if(request('permission_type'))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Tipo: {{ $permissionTypes->find(request('permission_type'))?->name }}
                                    </span>
                                @endif
                                @if(request('code'))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Código: {{ request('code') }}
                                    </span>
                                @endif
                                @if(request('overdue'))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Con Retraso
                                    </span>
                                @endif
                                @if(request('departure_from') || request('departure_to'))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Salida: {{ request('departure_from') }} - {{ request('departure_to') }}
                                    </span>
                                @endif
                                @if(request('return_from') || request('return_to'))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        Regreso: {{ request('return_from') }} - {{ request('return_to') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Mis Permisos -->
            @if($ownTrackings->isNotEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-4 sm:p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-user text-green-600 mr-2"></i>
                            Mis Permisos
                        </h3>
                    </div>

                <!-- Desktop Table View (Compacta) -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 table-fixed">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/5">
                                    Empleado
                                </th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">
                                    Permiso
                                </th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Salida/Regreso
                                </th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tiempo
                                </th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Registro
                                </th>
                                <th scope="col" class="relative px-3 py-3">
                                    <span class="sr-only">Ver</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($ownTrackings as $tracking)
                                <tr class="hover:bg-gray-50 text-sm">
                                    <td class="px-3 py-3 whitespace-normal">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <span class="text-xs font-medium text-gray-700">
                                                        {{ strtoupper(substr($tracking->permissionRequest->user->name, 0, 2)) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="font-medium text-gray-900 truncate max-w-[120px]" title="{{ $tracking->permissionRequest->user->name }}">
                                                    {{ $tracking->permissionRequest->user->name }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $tracking->employee_dni }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-3 py-3 whitespace-normal">
                                        <div class="text-gray-900 text-xs font-medium">
                                            {{ $tracking->permissionRequest->permissionType->name ?? 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $tracking->permissionRequest->code }}
                                        </div>
                                    </td>
                                    
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium 
                                                       bg-{{ $tracking->getStatusColor() }}-100 text-{{ $tracking->getStatusColor() }}-800">
                                            {{ $tracking->getStatusLabel() }}
                                        </span>
                                        @if($tracking->isOverdue())
                                            <div class="text-xs text-red-600 mt-1 font-bold">
                                                <i class="fas fa-exclamation-triangle"></i> Retraso
                                            </div>
                                        @endif
                                    </td>
                                    
                                    <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-600">
                                        <div class="flex flex-col">
                                            <span class="text-green-700" title="Salida">
                                                <i class="fas fa-arrow-right text-xs"></i> 
                                                @if($tracking->departure_datetime)
                                                    {{ $tracking->departure_datetime->format('d/m H:i') }}
                                                @else -- @endif
                                            </span>
                                            <span class="text-blue-700 mt-1" title="Regreso">
                                                <i class="fas fa-arrow-left text-xs"></i>
                                                @if($tracking->return_datetime)
                                                    {{ $tracking->return_datetime->format('d/m H:i') }}
                                                @else -- @endif
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <td class="px-3 py-3 whitespace-nowrap text-xs">
                                        @if($tracking->actual_hours_used)
                                            @php
                                                $hours = floor($tracking->actual_hours_used);
                                                $minutes = round(($tracking->actual_hours_used - $hours) * 60);
                                            @endphp
                                            <div class="font-bold text-gray-900">
                                                {{ $hours }}h {{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}m
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    
                                    <td class="px-3 py-3 whitespace-normal text-xs text-gray-500">
                                        @if($tracking->registeredByUser)
                                            <div class="truncate max-w-[100px]" title="{{ $tracking->registeredByUser->name }}">
                                                {{ $tracking->registeredByUser->name }}
                                            </div>
                                            <div class="text-[10px] text-gray-400">
                                                {{ $tracking->updated_at->format('d/m H:i') }}
                                            </div>
                                        @else
                                            Sistema
                                        @endif
                                    </td>
                                    
                                    <td class="px-3 py-3 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('tracking.show', $tracking) }}" 
                                           class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        No hay registros.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <!-- Paginación Mis Permisos -->
                    @if(method_exists($ownTrackings, 'links'))
                        <div class="px-4 py-3 border-t border-gray-200">
                            {{ $ownTrackings->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>

                <!-- Mobile View (Actualizado Formato Hora) -->
                <div class="lg:hidden">
                    @forelse($ownTrackings as $tracking)
                        <div class="border-b border-gray-200 p-4 hover:bg-gray-50">
                            <!-- Cabecera Mobile -->
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-600 font-bold text-xs">
                                        {{ strtoupper(substr($tracking->permissionRequest->user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">
                                            {{ $tracking->permissionRequest->user->name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $tracking->permissionRequest->permissionType->name ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold 
                                                   bg-{{ $tracking->getStatusColor() }}-100 text-{{ $tracking->getStatusColor() }}-800">
                                        {{ $tracking->getStatusLabel() }}
                                    </span>
                                </div>
                            </div>

                            <!-- Grid Detalles Mobile -->
                            <div class="grid grid-cols-2 gap-2 text-sm mt-3">
                                <div class="bg-gray-50 p-2 rounded">
                                    <span class="block text-xs text-gray-500">Salida</span>
                                    <span class="font-medium">
                                        {{ $tracking->departure_datetime ? $tracking->departure_datetime->format('d/m H:i') : '--' }}
                                    </span>
                                </div>
                                <div class="bg-gray-50 p-2 rounded">
                                    <span class="block text-xs text-gray-500">Regreso</span>
                                    <span class="font-medium">
                                        {{ $tracking->return_datetime ? $tracking->return_datetime->format('d/m H:i') : '--' }}
                                    </span>
                                </div>
                                <div class="col-span-2 bg-blue-50 p-2 rounded flex justify-between items-center">
                                    <span class="text-xs text-blue-800 font-semibold">Tiempo Utilizado:</span>
                                    <span class="font-bold text-blue-900">
                                        @if($tracking->actual_hours_used)
                                            @php
                                                $hours = floor($tracking->actual_hours_used);
                                                $minutes = round(($tracking->actual_hours_used - $hours) * 60);
                                            @endphp
                                            {{ $hours }}h {{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}m
                                        @else
                                            --
                                        @endif
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mt-3 text-right">
                                <a href="{{ route('tracking.show', $tracking) }}" class="text-xs text-blue-600 font-semibold hover:underline">Ver detalles completos &rarr;</a>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-500">No hay registros</div>
                    @endforelse
                    
                    <!-- Paginación Mobile -->
                    @if(method_exists($ownTrackings, 'links'))
                        <div class="px-4 py-3">
                            {{ $ownTrackings->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>

                </div>
            @endif

            <!-- Permisos del Equipo -->
            @if($teamTrackings->isNotEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-users text-blue-600 mr-2"></i>
                            @if(auth()->user()->hasRole('jefe_inmediato'))
                                Permisos de mi Equipo
                            @else
                                Permisos del Personal
                            @endif
                        </h3>
                    </div>

                    <!-- Desktop Table View (Compacta) -->
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 table-fixed">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/5">Empleado</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Permiso</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Salida/Regreso</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiempo</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registro</th>
                                    <th class="px-3 py-3 relative"><span class="sr-only">Ver</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($teamTrackings as $tracking)
                                    <tr class="hover:bg-gray-50 text-sm">
                                        <td class="px-3 py-3 whitespace-normal">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8">
                                                    <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                        <span class="text-xs font-medium text-gray-700">{{ strtoupper(substr($tracking->permissionRequest->user->name, 0, 2)) }}</span>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="font-medium text-gray-900 truncate max-w-[120px]" title="{{ $tracking->permissionRequest->user->name }}">
                                                        {{ $tracking->permissionRequest->user->name }}
                                                    </div>
                                                    <div class="text-xs text-gray-500">{{ $tracking->employee_dni }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="px-3 py-3 whitespace-normal">
                                            <div class="text-gray-900 text-xs font-medium">{{ $tracking->permissionRequest->permissionType->name ?? 'N/A' }}</div>
                                            <div class="text-xs text-gray-500">{{ $tracking->permissionRequest->code }}</div>
                                        </td>
                                        
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $tracking->getStatusColor() }}-100 text-{{ $tracking->getStatusColor() }}-800">
                                                {{ $tracking->getStatusLabel() }}
                                            </span>
                                            @if($tracking->isOverdue())
                                                <div class="text-xs text-red-600 mt-1 font-bold"><i class="fas fa-exclamation-triangle"></i> Retraso</div>
                                            @endif
                                        </td>
                                        
                                        <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-600">
                                            <div class="flex flex-col">
                                                <span class="text-green-700"><i class="fas fa-arrow-right text-xs"></i> {{ $tracking->departure_datetime ? $tracking->departure_datetime->format('d/m H:i') : '--' }}</span>
                                                <span class="text-blue-700 mt-1"><i class="fas fa-arrow-left text-xs"></i> {{ $tracking->return_datetime ? $tracking->return_datetime->format('d/m H:i') : '--' }}</span>
                                            </div>
                                        </td>
                                        
                                        <td class="px-3 py-3 whitespace-nowrap text-xs">
                                            @if($tracking->actual_hours_used)
                                                @php
                                                    $hours = floor($tracking->actual_hours_used);
                                                    $minutes = round(($tracking->actual_hours_used - $hours) * 60);
                                                @endphp
                                                <div class="font-bold text-gray-900">{{ $hours }}h {{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}m</div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        
                                        <td class="px-3 py-3 whitespace-normal text-xs text-gray-500">
                                            @if($tracking->registeredByUser)
                                                <div class="truncate max-w-[100px]">{{ $tracking->registeredByUser->name }}</div>
                                                <div class="text-[10px] text-gray-400">{{ $tracking->updated_at->format('d/m H:i') }}</div>
                                            @else Sistema @endif
                                        </td>
                                        
                                        <td class="px-3 py-3 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('tracking.show', $tracking) }}" class="text-blue-600 hover:text-blue-900"><i class="fas fa-eye"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No hay seguimientos.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        
                        <!-- Paginación Equipo -->
                        @if(method_exists($teamTrackings, 'links'))
                            <div class="px-4 py-3 border-t border-gray-200">
                                {{ $teamTrackings->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>

                    <!-- Mobile/Tablet Card View (Equipo) -->
                    <div class="lg:hidden">
                        @forelse($teamTrackings as $tracking)
                            <div class="border-b border-gray-200 p-4 hover:bg-gray-50">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-600 font-bold text-xs">
                                            {{ strtoupper(substr($tracking->permissionRequest->user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-900">{{ $tracking->permissionRequest->user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $tracking->permissionRequest->permissionType->name ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-{{ $tracking->getStatusColor() }}-100 text-{{ $tracking->getStatusColor() }}-800">
                                            {{ $tracking->getStatusLabel() }}
                                        </span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-sm mt-3">
                                    <div class="bg-gray-50 p-2 rounded">
                                        <span class="block text-xs text-gray-500">Salida</span>
                                        <span class="font-medium">{{ $tracking->departure_datetime ? $tracking->departure_datetime->format('d/m H:i') : '--' }}</span>
                                    </div>
                                    <div class="bg-gray-50 p-2 rounded">
                                        <span class="block text-xs text-gray-500">Regreso</span>
                                        <span class="font-medium">{{ $tracking->return_datetime ? $tracking->return_datetime->format('d/m H:i') : '--' }}</span>
                                    </div>
                                    <div class="col-span-2 bg-blue-50 p-2 rounded flex justify-between items-center">
                                        <span class="text-xs text-blue-800 font-semibold">Tiempo Utilizado:</span>
                                        <span class="font-bold text-blue-900">
                                            @if($tracking->actual_hours_used)
                                                @php
                                                    $hours = floor($tracking->actual_hours_used);
                                                    $minutes = round(($tracking->actual_hours_used - $hours) * 60);
                                                @endphp
                                                {{ $hours }}h {{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}m
                                            @else -- @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-3 text-right">
                                    <a href="{{ route('tracking.show', $tracking) }}" class="text-xs text-blue-600 font-semibold hover:underline">Ver detalles &rarr;</a>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-gray-500">No hay registros</div>
                        @endforelse

                        <!-- Paginación Mobile Equipo -->
                        @if(method_exists($teamTrackings, 'links'))
                            <div class="px-4 py-3">
                                {{ $teamTrackings->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Statistics Cards -->
            @if(auth()->user()->hasRole('jefe_rrhh'))
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mt-6">
                    <!-- Cards (Igual que antes) -->
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
                                            {{ $ownTrackings->total() + $teamTrackings->total() }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Agrega aquí el resto de las tarjetas si las necesitas, usando count() o total() si es paginado -->
                </div>
            @endif
        </div>
    </div>

    <script>
        function toggleFilters() {
            const filterForm = document.getElementById('filterForm');
            const filterIcon = document.getElementById('filterIcon');
            const filterText = document.getElementById('filterText');

            if (filterForm.style.display === 'none') {
                filterForm.style.display = 'block';
                filterIcon.classList.remove('fa-chevron-down');
                filterIcon.classList.add('fa-chevron-up');
                filterText.textContent = 'Ocultar';
            } else {
                filterForm.style.display = 'none';
                filterIcon.classList.remove('fa-chevron-up');
                filterIcon.classList.add('fa-chevron-down');
                filterText.textContent = 'Mostrar';
            }
        }

        // Auto-abrir filtros si hay filtros activos
        document.addEventListener('DOMContentLoaded', function() {
            const hasFilters = {{ request()->hasAny(['name', 'dni', 'status', 'permission_type', 'code', 'departure_from', 'departure_to', 'return_from', 'return_to', 'overdue']) ? 'true' : 'false' }};
            if (hasFilters) {
                toggleFilters();
            }
        });
    </script>
</x-app-layout>