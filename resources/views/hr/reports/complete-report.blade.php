<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Reporte Completo de Permisos con Seguimiento
                </h2>
                <p class="mt-1 text-gray-600">Información detallada de todas las solicitudes y su seguimiento</p>
            </div>
            <div class="mt-4 lg:mt-0">
                <a href="{{ route('hr.reports.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Volver al Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-4 border border-blue-100">
                    <div class="text-sm text-gray-600">Total Solicitudes</div>
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($summary['total']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 border border-green-100">
                    <div class="text-sm text-gray-600">Aprobadas</div>
                    <div class="text-2xl font-bold text-green-600">{{ number_format($summary['approved']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 border border-yellow-100">
                    <div class="text-sm text-gray-600">Pendientes</div>
                    <div class="text-2xl font-bold text-yellow-600">{{ number_format($summary['pending']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 border border-red-100">
                    <div class="text-sm text-gray-600">Rechazadas</div>
                    <div class="text-2xl font-bold text-red-600">{{ number_format($summary['rejected']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 border border-purple-100">
                    <div class="text-sm text-gray-600">Con Seguimiento</div>
                    <div class="text-2xl font-bold text-purple-600">{{ number_format($summary['with_tracking']) }}</div>
                </div>
            </div>

            <!-- Filters and Export -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <form method="GET" action="{{ route('hr.reports.complete-report') }}" id="filterForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <!-- Date From -->
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                            <input type="date"
                                   name="date_from"
                                   id="date_from"
                                   value="{{ $dateFrom }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <!-- Date To -->
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                            <input type="date"
                                   name="date_to"
                                   id="date_to"
                                   value="{{ $dateTo }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select name="status"
                                    id="status"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}" {{ $status == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Department Filter -->
                        <div>
                            <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
                            <select name="department"
                                    id="department"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="all">Todos</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ $department == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Permission Type Filter -->
                        <div>
                            <label for="permission_type" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Permiso</label>
                            <select name="permission_type"
                                    id="permission_type"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="all">Todos</option>
                                @foreach($permissionTypes as $type)
                                    <option value="{{ $type->id }}" {{ $permissionType == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-3">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            Filtrar
                        </button>

                        <button type="button"
                                onclick="exportToExcel()"
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Exportar a Excel
                        </button>

                        <a href="{{ route('hr.reports.complete-report') }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Limpiar Filtros
                        </a>
                    </div>
                </form>
            </div>

            <!-- Results Table -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solicitud</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empleado</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aprobaciones</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seguimiento</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($data as $request)
                                <tr class="hover:bg-gray-50">
                                    <!-- Request Info -->
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $request->request_number }}</div>
                                        <div class="text-xs text-gray-500">{{ $request->created_at->format('d/m/Y H:i') }}</div>
                                    </td>

                                    <!-- Employee Info -->
                                    <td class="px-4 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $request->user->full_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $request->user->department->name ?? 'N/A' }}</div>
                                    </td>

                                    <!-- Permission Type -->
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $request->permissionType->name }}</div>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($request->status == 'approved') bg-green-100 text-green-800
                                            @elseif($request->status == 'rejected') bg-red-100 text-red-800
                                            @elseif(str_contains($request->status, 'pending')) bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $request->getStatusLabel() }}
                                        </span>
                                    </td>

                                    <!-- Approvals -->
                                    <td class="px-4 py-4">
                                        <div class="text-xs space-y-1">
                                            @php
                                                $level1 = $request->getApprovalByLevel(1);
                                                $level2 = $request->getApprovalByLevel(2);
                                            @endphp

                                            @if($level1)
                                                <div class="flex items-center">
                                                    <span class="w-2 h-2 rounded-full mr-2
                                                        {{ $level1->status == 'approved' ? 'bg-green-500' : ($level1->status == 'rejected' ? 'bg-red-500' : 'bg-yellow-500') }}">
                                                    </span>
                                                    <span class="text-gray-600">Nivel 1: {{ $level1->getStatusLabel() }}</span>
                                                </div>
                                            @endif

                                            @if($level2)
                                                <div class="flex items-center">
                                                    <span class="w-2 h-2 rounded-full mr-2
                                                        {{ $level2->status == 'approved' ? 'bg-green-500' : ($level2->status == 'rejected' ? 'bg-red-500' : 'bg-yellow-500') }}">
                                                    </span>
                                                    <span class="text-gray-600">Nivel 2: {{ $level2->getStatusLabel() }}</span>
                                                </div>
                                            @endif

                                            @if(!$level1 && !$level2)
                                                <span class="text-gray-400">Sin aprobaciones</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Tracking -->
                                    <td class="px-4 py-4">
                                        @if($request->tracking)
                                            <div class="text-xs space-y-1">
                                                <div>
                                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                                        @if($request->tracking->tracking_status == 'returned') bg-green-100 text-green-800
                                                        @elseif($request->tracking->tracking_status == 'out') bg-blue-100 text-blue-800
                                                        @elseif($request->tracking->tracking_status == 'overdue') bg-red-100 text-red-800
                                                        @else bg-gray-100 text-gray-800
                                                        @endif">
                                                        {{ $request->tracking->getStatusLabel() }}
                                                    </span>
                                                </div>
                                                @if($request->tracking->actual_hours_used)
                                                    <div class="text-gray-600">{{ $request->tracking->actual_hours_used }}h utilizadas</div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">Sin seguimiento</span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('permissions.show', $request) }}"
                                           class="text-blue-600 hover:text-blue-900 font-medium">
                                            Ver Detalles
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                        No se encontraron solicitudes con los filtros aplicados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($data->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200">
                        {{ $data->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>

            <!-- Info Footer -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="text-sm text-blue-800">
                        <p class="font-medium mb-1">Acerca de este reporte</p>
                        <p>Este reporte muestra información completa de todas las solicitudes de permisos, incluyendo datos del empleado, estado de aprobaciones en todos los niveles, información de seguimiento físico y métricas de tiempo. Puede filtrar por fecha, estado, departamento y tipo de permiso, y exportar los resultados a Excel para análisis adicional.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function exportToExcel() {
            const params = new URLSearchParams(window.location.search);
            params.set('type', 'complete_report');

            window.location.href = '{{ route("hr.reports.export") }}?' + params.toString();
        }
    </script>
    @endpush
</x-app-layout>
