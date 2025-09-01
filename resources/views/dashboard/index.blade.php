<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <i class="fas fa-tachometer-alt mr-2 text-blue-600"></i>
                    Dashboard
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Bienvenido al sistema de papeletas digitales
                </p>
            </div>
            <div class="text-left sm:text-right">
                <p class="text-sm text-gray-500">
                    {{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Información del usuario --}}
        <div class="bg-gradient-to-r from-blue-600 via-blue-600 to-blue-700 rounded-xl shadow-lg overflow-hidden mb-6 sm:mb-8 transform hover:scale-[1.02] transition-all duration-300">
            <div class="px-4 py-6 sm:px-6 sm:py-8 text-white">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center space-x-3 sm:space-x-4 w-full sm:w-auto">
                        <div class="w-12 h-12 sm:w-16 sm:h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center flex-shrink-0 animate-pulse">
                            <span class="text-lg sm:text-2xl font-bold">
                                {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                            </span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h1 class="text-xl sm:text-2xl font-bold truncate">¡Hola, {{ $user->first_name }}!</h1>
                            <p class="text-blue-100 mt-1 text-sm sm:text-base flex items-center">
                                <i class="fas fa-building mr-2 flex-shrink-0"></i>
                                <span class="truncate">{{ $user->department->name ?? 'Sin departamento asignado' }}</span>
                            </p>
                            <p class="text-blue-100 text-sm sm:text-base flex items-center">
                                <i class="fas fa-user-tag mr-2 flex-shrink-0"></i>
                                <span class="truncate">{{ $user->role->description ?? 'Sin rol asignado' }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="w-full sm:w-auto">
                        <div class="bg-white bg-opacity-10 rounded-lg p-3 sm:p-4 text-center sm:text-right backdrop-blur-sm">
                            <p class="text-xs sm:text-sm text-blue-100">ID Empleado</p>
                            <p class="text-lg sm:text-xl font-bold">{{ $user->employee_id ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Estadísticas principales --}}
        <div id="dashboard-stats" class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 mb-6 sm:mb-8">
            {{-- Total de solicitudes --}}
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden transform hover:-translate-y-1 group">
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs sm:text-sm font-medium text-gray-600 truncate">Total Solicitudes</p>
                            <p class="text-2xl sm:text-3xl font-bold text-gray-900 transition-all duration-300 group-hover:scale-110" data-stat="total">{{ $myStats['total'] }}</p>
                        </div>
                        <div class="bg-blue-100 rounded-full p-2 sm:p-3 self-center group-hover:bg-blue-200 transition-colors duration-300">
                            <i class="fas fa-file-alt text-lg sm:text-2xl text-blue-600"></i>
                        </div>
                    </div>
                    <div class="mt-3 sm:mt-4">
                        <div class="flex items-center text-xs sm:text-sm">
                            <span class="text-gray-500 truncate">Todas tus solicitudes</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Solicitudes pendientes --}}
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden transform hover:-translate-y-1 group">
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs sm:text-sm font-medium text-gray-600 truncate">Pendientes</p>
                            <p class="text-2xl sm:text-3xl font-bold text-yellow-600 transition-all duration-300 group-hover:scale-110" data-stat="pending">{{ $myStats['pending'] }}</p>
                        </div>
                        <div class="bg-yellow-100 rounded-full p-2 sm:p-3 self-center group-hover:bg-yellow-200 transition-colors duration-300">
                            <i class="fas fa-clock text-lg sm:text-2xl text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="mt-3 sm:mt-4">
                        <div class="flex items-center text-xs sm:text-sm">
                            @if($myStats['pending'] > 0)
                                <span class="text-yellow-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1 animate-pulse"></i>
                                    <span class="truncate">Requieren atención</span>
                                </span>
                            @else
                                <span class="text-gray-500 truncate">Sin pendientes</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Solicitudes aprobadas --}}
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden transform hover:-translate-y-1 group">
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs sm:text-sm font-medium text-gray-600 truncate">Aprobadas</p>
                            <p class="text-2xl sm:text-3xl font-bold text-green-600 transition-all duration-300 group-hover:scale-110" data-stat="approved">{{ $myStats['approved'] }}</p>
                        </div>
                        <div class="bg-green-100 rounded-full p-2 sm:p-3 self-center group-hover:bg-green-200 transition-colors duration-300">
                            <i class="fas fa-check-circle text-lg sm:text-2xl text-green-600"></i>
                        </div>
                    </div>
                    <div class="mt-3 sm:mt-4">
                        <div class="flex items-center text-xs sm:text-sm">
                            @if($myStats['total'] > 0)
                                <span class="text-green-600 truncate" data-stat="approved-percentage">
                                    {{ round(($myStats['approved'] / $myStats['total']) * 100) }}% del total
                                </span>
                            @else
                                <span class="text-gray-500 truncate">Sin solicitudes</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Solicitudes rechazadas --}}
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden transform hover:-translate-y-1 group">
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs sm:text-sm font-medium text-gray-600 truncate">Rechazadas</p>
                            <p class="text-2xl sm:text-3xl font-bold text-red-600 transition-all duration-300 group-hover:scale-110" data-stat="rejected">{{ $myStats['rejected'] }}</p>
                        </div>
                        <div class="bg-red-100 rounded-full p-2 sm:p-3 self-center group-hover:bg-red-200 transition-colors duration-300">
                            <i class="fas fa-times-circle text-lg sm:text-2xl text-red-600"></i>
                        </div>
                    </div>
                    <div class="mt-3 sm:mt-4">
                        <div class="flex items-center text-xs sm:text-sm">
                            @if($myStats['total'] > 0)
                                <span class="text-red-600 truncate" data-stat="rejected-percentage">
                                    {{ round(($myStats['rejected'] / $myStats['total']) * 100) }}% del total
                                </span>
                            @else
                                <span class="text-gray-500 truncate">Sin rechazadas</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Acciones rápidas --}}
        <div class="bg-white rounded-xl shadow-lg mb-6 sm:mb-8 overflow-hidden">
            <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-gray-200">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-bolt mr-2 text-yellow-500 animate-pulse"></i>
                    Acciones Rápidas
                </h3>
            </div>
            <div class="p-4 sm:p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    {{-- Nueva solicitud --}}
                    <a href="{{ route('permissions.create') }}" class="group bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg p-3 sm:p-4 transition-all duration-200 transform hover:scale-105 hover:shadow-lg">
                        <div class="flex items-center space-x-2 sm:space-x-3">
                            <div class="bg-white bg-opacity-20 rounded-lg p-1.5 sm:p-2 group-hover:bg-opacity-30 transition-all duration-200">
                                <i class="fas fa-plus text-sm sm:text-lg"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-sm sm:text-base truncate">Nueva Solicitud</p>
                                <p class="text-xs text-blue-100 truncate">Crear papeleta</p>
                            </div>
                        </div>
                    </a>

                    {{-- Ver mis solicitudes --}}
                    <a href="{{ route('permissions.index') }}" class="group bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white rounded-lg p-3 sm:p-4 transition-all duration-200 transform hover:scale-105 hover:shadow-lg">
                        <div class="flex items-center space-x-2 sm:space-x-3">
                            <div class="bg-white bg-opacity-20 rounded-lg p-1.5 sm:p-2 group-hover:bg-opacity-30 transition-all duration-200">
                                <i class="fas fa-file-alt text-sm sm:text-lg"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-sm sm:text-base truncate">Mis Solicitudes</p>
                                <p class="text-xs text-gray-100 truncate">{{ $myStats['total'] }} total</p>
                            </div>
                        </div>
                    </a>

                    {{-- Aprobaciones (si aplica) --}}
                    @if($user->canApprove(new \App\Models\PermissionRequest()))
                        <a href="{{ route('approvals.index') }}" class="group bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-lg p-3 sm:p-4 transition-all duration-200 transform hover:scale-105 hover:shadow-lg relative">
                            <div class="flex items-center space-x-2 sm:space-x-3">
                                <div class="bg-white bg-opacity-20 rounded-lg p-1.5 sm:p-2 group-hover:bg-opacity-30 transition-all duration-200">
                                    <i class="fas fa-check-circle text-sm sm:text-lg"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-sm sm:text-base truncate">Aprobaciones</p>
                                    <p class="text-xs text-green-100 truncate">
                                        @if(isset($pendingApprovals) && $pendingApprovals->total() > 0)
                                            {{ $pendingApprovals->total() }} pendientes
                                        @else
                                            Sin pendientes
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @if(isset($pendingApprovals) && $pendingApprovals->total() > 0)
                                <div class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 sm:w-6 sm:h-6 flex items-center justify-center text-xs font-bold animate-bounce">
                                    {{ $pendingApprovals->total() }}
                                </div>
                            @endif
                        </a>
                    @endif

                    {{-- Reportes (si aplica) --}}
                    @if($user->hasRole('jefe_rrhh') || $user->hasRole('admin'))
                        <a href="{{ route('reports.index') }}" class="group bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white rounded-lg p-3 sm:p-4 transition-all duration-200 transform hover:scale-105 hover:shadow-lg">
                            <div class="flex items-center space-x-2 sm:space-x-3">
                                <div class="bg-white bg-opacity-20 rounded-lg p-1.5 sm:p-2 group-hover:bg-opacity-30 transition-all duration-200">
                                    <i class="fas fa-chart-bar text-sm sm:text-lg"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-sm sm:text-base truncate">Reportes</p>
                                    <p class="text-xs text-purple-100 truncate">Ver estadísticas</p>
                                </div>
                            </div>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Mis solicitudes recientes --}}
        <div id="my-requests-section" class="bg-white rounded-xl shadow-lg mb-6 sm:mb-8 overflow-hidden">
            <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-history mr-2 text-blue-500"></i>
                        Mis Solicitudes Recientes
                    </h3>
                    <a href="{{ route('permissions.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors duration-150 hover:underline">
                        Ver todas <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            
            @if($myRequests->count() > 0)
                {{-- Vista móvil --}}
                <div class="block sm:hidden">
                    @foreach($myRequests as $request)
                        <div class="p-4 border-b border-gray-100 last:border-b-0 hover:bg-gray-50 transition-colors duration-150">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex items-center min-w-0 flex-1">
                                    <div class="bg-blue-100 rounded-full p-2 mr-3 flex-shrink-0">
                                        <i class="fas fa-file-alt text-blue-600 text-xs"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-medium text-gray-900 text-sm truncate">{{ $request->request_number }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ $request->permissionType->name }}</p>
                                    </div>
                                </div>
                                @php
                                    $statusConfig = [
                                        'pending' => ['bg-yellow-100', 'text-yellow-800', 'fas fa-clock'],
                                        'approved' => ['bg-green-100', 'text-green-800', 'fas fa-check-circle'],
                                        'rejected' => ['bg-red-100', 'text-red-800', 'fas fa-times-circle'],
                                        'draft' => ['bg-gray-100', 'text-gray-800', 'fas fa-edit']
                                    ];
                                    $config = $statusConfig[$request->status] ?? ['bg-gray-100', 'text-gray-800', 'fas fa-question'];
                                @endphp
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $config[0] }} {{ $config[1] }} flex-shrink-0">
                                    <i class="{{ $config[2] }} mr-1"></i>
                                    {{ $request->getStatusLabel() }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="text-xs text-gray-500">
                                    {{ $request->created_at->format('d/m/Y H:i') }}
                                </div>
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('permissions.show', $request) }}" 
                                       class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded text-xs transition-colors duration-150">
                                        <i class="fas fa-eye mr-1"></i>Ver
                                    </a>
                                    @if($request->status === 'draft')
                                        <a href="{{ route('permissions.edit', $request) }}" 
                                           class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 px-2 py-1 rounded text-xs transition-colors duration-150">
                                            <i class="fas fa-edit mr-1"></i>Editar
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Vista desktop --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <i class="fas fa-hashtag mr-1"></i>
                                    Número
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <i class="fas fa-tag mr-1"></i>
                                    Tipo
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <i class="fas fa-calendar mr-1"></i>
                                    Fecha/Hora
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <i class="fas fa-cog mr-1"></i>
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($myRequests as $request)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $request->request_number }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="bg-blue-100 rounded-full p-2 mr-3">
                                                <i class="fas fa-file-alt text-blue-600 text-xs"></i>
                                            </div>
                                            <div class="text-sm text-gray-900">
                                                {{ $request->permissionType->name }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $request->created_at->format('d/m/Y') }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $request->created_at->format('H:i') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusConfig = [
                                                'pending' => ['bg-yellow-100', 'text-yellow-800', 'fas fa-clock'],
                                                'approved' => ['bg-green-100', 'text-green-800', 'fas fa-check-circle'],
                                                'rejected' => ['bg-red-100', 'text-red-800', 'fas fa-times-circle'],
                                                'draft' => ['bg-gray-100', 'text-gray-800', 'fas fa-edit']
                                            ];
                                            $config = $statusConfig[$request->status] ?? ['bg-gray-100', 'text-gray-800', 'fas fa-question'];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $config[0] }} {{ $config[1] }}">
                                            <i class="{{ $config[2] }} mr-1"></i>
                                            {{ $request->getStatusLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('permissions.show', $request) }}" 
                                               class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded-md transition-colors duration-150">
                                                <i class="fas fa-eye mr-1"></i>
                                                Ver
                                            </a>
                                            @if($request->status === 'draft')
                                                <a href="{{ route('permissions.edit', $request) }}" 
                                                   class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 px-3 py-1 rounded-md transition-colors duration-150">
                                                    <i class="fas fa-edit mr-1"></i>
                                                    Editar
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 sm:p-12 text-center">
                    <div class="mx-auto w-16 h-16 sm:w-24 sm:h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4 animate-pulse">
                        <i class="fas fa-file-alt text-2xl sm:text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-2">No hay solicitudes</h3>
                    <p class="text-gray-500 mb-4 sm:mb-6 text-sm sm:text-base">Aún no has creado ninguna solicitud de papeleta.</p>
                    <a href="{{ route('permissions.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-150 transform hover:scale-105">
                        <i class="fas fa-plus mr-2"></i>
                        Crear primera solicitud
                    </a>
                </div>
            @endif
        </div>

        {{-- Sección para supervisores --}}
        @if($user->isSupervisor() && isset($subordinateRequests))
            <div id="team-requests-section" class="bg-white rounded-xl shadow-lg overflow-hidden mb-6 sm:mb-8">
                <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-users mr-2 text-green-500"></i>
                            Solicitudes de mi Equipo
                        </h3>
                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full self-start sm:self-center">
                            {{ $subordinateRequests->count() }} solicitudes
                        </span>
                    </div>
                </div>
                
                @if($subordinateRequests->count() > 0)
                    {{-- Vista móvil --}}
                    <div class="block sm:hidden">
                        @foreach($subordinateRequests as $request)
                            <div class="p-4 border-b border-gray-100 last:border-b-0 hover:bg-gray-50 transition-colors duration-150">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center min-w-0 flex-1">
                                        <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                            <span class="text-xs font-medium text-gray-600">
                                                {{ substr($request->user->first_name, 0, 1) }}{{ substr($request->user->last_name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium text-gray-900 text-sm truncate">{{ $request->user->full_name }}</p>
                                            <p class="text-xs text-gray-500 truncate">{{ $request->user->employee_id ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    @php
                                        $statusConfig = [
                                            'pending' => ['bg-yellow-100', 'text-yellow-800', 'fas fa-clock'],
                                            'approved' => ['bg-green-100', 'text-green-800', 'fas fa-check-circle'],
                                            'rejected' => ['bg-red-100', 'text-red-800', 'fas fa-times-circle'],
                                            'draft' => ['bg-gray-100', 'text-gray-800', 'fas fa-edit']
                                        ];
                                        $config = $statusConfig[$request->status] ?? ['bg-gray-100', 'text-gray-800', 'fas fa-question'];
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $config[0] }} {{ $config[1] }} flex-shrink-0">
                                        <i class="{{ $config[2] }} mr-1"></i>
                                        {{ $request->getStatusLabel() }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs text-gray-600 truncate">{{ $request->permissionType->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $request->created_at->format('d/m/Y') }}</p>
                                    </div>
                                    <div class="flex-shrink-0 ml-2">
                                        @if($user->canApprove($request))
                                            <a href="{{ route('approvals.show', $request) }}" 
                                               class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 px-2 py-1 rounded text-xs transition-colors duration-150">
                                                <i class="fas fa-check mr-1"></i>Revisar
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-xs">Sin acciones</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Vista desktop --}}
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <i class="fas fa-user mr-1"></i>
                                        Empleado
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <i class="fas fa-tag mr-1"></i>
                                        Tipo
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <i class="fas fa-calendar mr-1"></i>
                                        Fecha
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Estado
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <i class="fas fa-cog mr-1"></i>
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($subordinateRequests as $request)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                                    <span class="text-xs font-medium text-gray-600">
                                                        {{ substr($request->user->first_name, 0, 1) }}{{ substr($request->user->last_name, 0, 1) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $request->user->full_name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $request->user->employee_id ?? 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $request->permissionType->name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $request->created_at->format('d/m/Y') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusConfig = [
                                                    'pending' => ['bg-yellow-100', 'text-yellow-800', 'fas fa-clock'],
                                                    'approved' => ['bg-green-100', 'text-green-800', 'fas fa-check-circle'],
                                                    'rejected' => ['bg-red-100', 'text-red-800', 'fas fa-times-circle'],
                                                    'draft' => ['bg-gray-100', 'text-gray-800', 'fas fa-edit']
                                                ];
                                                $config = $statusConfig[$request->status] ?? ['bg-gray-100', 'text-gray-800', 'fas fa-question'];
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $config[0] }} {{ $config[1] }}">
                                                <i class="{{ $config[2] }} mr-1"></i>
                                                {{ $request->getStatusLabel() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            @if($user->canApprove($request))
                                                <a href="{{ route('approvals.show', $request) }}" 
                                                   class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 px-3 py-1 rounded-md transition-colors duration-150">
                                                    <i class="fas fa-check mr-1"></i>
                                                    Revisar
                                                </a>
                                            @else
                                                <span class="text-gray-400 text-sm">Sin acciones</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-8 sm:p-12 text-center">
                        <div class="mx-auto w-16 h-16 sm:w-24 sm:h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4 animate-pulse">
                            <i class="fas fa-users text-2xl sm:text-3xl text-gray-400"></i>
                        </div>
                        <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-2">Sin solicitudes del equipo</h3>
                        <p class="text-gray-500 text-sm sm:text-base">No hay solicitudes pendientes de su equipo.</p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Información adicional del footer --}}
        <div class="mt-6 sm:mt-8 bg-gradient-to-r from-gray-50 via-gray-100 to-gray-50 rounded-xl p-4 sm:p-6 transform hover:scale-[1.02] transition-all duration-300">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 text-center">
                <div class="group hover:bg-white hover:shadow-md rounded-lg p-3 sm:p-4 transition-all duration-300">
                    <div class="text-xl sm:text-2xl font-bold text-blue-600 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4 class="font-semibold text-gray-900 mt-2 text-sm sm:text-base">Seguro y Confiable</h4>
                    <p class="text-xs sm:text-sm text-gray-600 mt-1">Sistema con firma digital integrada</p>
                </div>
                <div class="group hover:bg-white hover:shadow-md rounded-lg p-3 sm:p-4 transition-all duration-300">
                    <div class="text-xl sm:text-2xl font-bold text-green-600 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h4 class="font-semibold text-gray-900 mt-2 text-sm sm:text-base">Disponible 24/7</h4>
                    <p class="text-xs sm:text-sm text-gray-600 mt-1">Acceso desde cualquier lugar</p>
                </div>
                <div class="group hover:bg-white hover:shadow-md rounded-lg p-3 sm:p-4 transition-all duration-300">
                    <div class="text-xl sm:text-2xl font-bold text-purple-600 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h4 class="font-semibold text-gray-900 mt-2 text-sm sm:text-base">Soporte Técnico</h4>
                    <p class="text-xs sm:text-sm text-gray-600 mt-1">Asistencia cuando la necesites</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Script para efectos adicionales --}}
    <script>
        // Agregar efectos de carga progresiva
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.group, .transform');
            
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);
            
            cards.forEach(function(card) {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
                observer.observe(card);
            });
            
            // Actualizar hora cada minuto
            function updateTime() {
                const now = new Date();
                const timeElements = document.querySelectorAll('[data-time]');
                timeElements.forEach(function(element) {
                    element.textContent = now.toLocaleString('es-PE', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                });
            }
            
            setInterval(updateTime, 60000);
        });
    </script>
</x-app-layout>