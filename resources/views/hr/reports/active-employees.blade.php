<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2 text-sm text-gray-500">
                        <li><a href="{{ route('hr.reports.dashboard') }}" class="hover:text-blue-600">Dashboard</a></li>
                        <li>/</li>
                        <li class="font-medium text-gray-900">Empleados Más Activos</li>
                    </ol>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    👥 Empleados Más Activos
                </h2>
                <p class="mt-1 text-gray-600">Ranking de empleados por cantidad de solicitudes</p>
            </div>
            <div class="mt-4 lg:mt-0 flex flex-col sm:flex-row gap-3">
                <button onclick="exportToExcel()" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Exportar Excel
                </button>
                <button onclick="refreshData()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Actualizar
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <!-- Filters -->
        <div class="mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">🔍 Filtros</h3>
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Desde</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Hasta</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-2 flex items-end">
                        <button type="submit" class="w-full md:w-auto px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Aplicar Filtros
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-blue-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">👥</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Empleados Activos</p>
                        <p class="text-3xl font-bold text-blue-600">{{ $data->count() }}</p>
                        <p class="text-sm text-gray-500">En el período</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-green-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">📊</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Solicitudes</p>
                        <p class="text-3xl font-bold text-green-600">{{ $data->sum('permission_requests_count') }}</p>
                        <p class="text-sm text-gray-500">Todas las solicitudes</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">🏆</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Máximo Solicitudes</p>
                        <p class="text-3xl font-bold text-purple-600">{{ $data->first()->permission_requests_count ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Por empleado</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-yellow-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">📈</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Promedio Solicitudes</p>
                        <p class="text-3xl font-bold text-yellow-600">{{ $data->count() > 0 ? number_format($data->avg('permission_requests_count'), 1) : 0 }}</p>
                        <p class="text-sm text-gray-500">Por empleado activo</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            
            <!-- Top 10 Employees Chart -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">🏆 Top 10 Empleados Más Activos</h3>
                <div class="h-80">
                    <canvas id="top10Chart"></canvas>
                </div>
            </div>

            <!-- Distribution by Department -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">🏢 Distribución por Departamento</h3>
                <div class="h-80">
                    <canvas id="departmentChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Performers Highlight -->
        @if($data->count() > 0)
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">🌟 Empleados Destacados</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($data->take(3) as $index => $employee)
                <div class="bg-gradient-to-br from-{{ $index == 0 ? 'yellow' : ($index == 1 ? 'gray' : 'orange') }}-50 to-{{ $index == 0 ? 'yellow' : ($index == 1 ? 'gray' : 'orange') }}-100 rounded-lg p-4 border border-{{ $index == 0 ? 'yellow' : ($index == 1 ? 'gray' : 'orange') }}-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-{{ $index == 0 ? 'yellow' : ($index == 1 ? 'gray' : 'orange') }}-200 rounded-full flex items-center justify-center">
                                <span class="text-2xl">{{ $index == 0 ? '🥇' : ($index == 1 ? '🥈' : '🥉') }}</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-sm font-semibold text-{{ $index == 0 ? 'yellow' : ($index == 1 ? 'gray' : 'orange') }}-800">
                                {{ $index + 1 }}° Lugar
                            </h4>
                            <p class="text-lg font-bold text-gray-900">{{ $employee->full_name }}</p>
                            <p class="text-sm text-gray-600">{{ $employee->department->name ?? 'N/A' }}</p>
                            <p class="text-sm font-medium text-{{ $index == 0 ? 'yellow' : ($index == 1 ? 'gray' : 'orange') }}-700">
                                {{ $employee->permission_requests_count }} solicitudes
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Complete Ranking Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">📋 Ranking Completo de Empleados</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posición</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empleado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Solicitudes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($data as $index => $employee)
                        <tr class="hover:bg-gray-50 {{ $index < 3 ? 'bg-gradient-to-r from-yellow-50 to-transparent' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($index < 3)
                                        <span class="text-xl mr-2">{{ $index == 0 ? '🥇' : ($index == 1 ? '🥈' : '🥉') }}</span>
                                    @else
                                        <span class="text-sm font-medium text-gray-900 bg-gray-100 px-2 py-1 rounded-full">{{ $index + 1 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-blue-800">{{ substr($employee->name, 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $employee->full_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $employee->dni }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900">{{ $employee->department->name ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $employee->email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $employee->permission_requests_count }} solicitudes
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('permissions.index', ['user_id' => $employee->id]) }}" 
                                   class="text-blue-600 hover:text-blue-800 font-medium">
                                    Ver solicitudes →
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No hay empleados con solicitudes en el período seleccionado
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Insights -->
        <div class="mt-8 bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">💡 Insights y Análisis</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                @if($data->count() > 0)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <span class="text-2xl">📊</span>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-semibold text-blue-800">Participación Activa</h4>
                            <p class="text-sm text-blue-700 mt-1">
                                {{ $data->count() }} empleados han realizado solicitudes, con un promedio de {{ number_format($data->avg('permission_requests_count'), 1) }} solicitudes cada uno.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                @if($data->count() > 0 && $data->first()->permission_requests_count > 10)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <span class="text-2xl">⚠️</span>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-semibold text-yellow-800">Alta Actividad</h4>
                            <p class="text-sm text-yellow-700 mt-1">
                                El empleado más activo ({{ $data->first()->full_name }}) tiene {{ $data->first()->permission_requests_count }} solicitudes. Considere revisar los patrones.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                @php
                    $departments = $data->groupBy('department.name')->map->count()->sortDesc();
                @endphp
                @if($departments->count() > 0)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <span class="text-2xl">🏢</span>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-semibold text-green-800">Departamento Líder</h4>
                            <p class="text-sm text-green-700 mt-1">
                                {{ $departments->keys()->first() ?? 'N/A' }} es el departamento con más empleados activos ({{ $departments->first() }}).
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const chartData = @json($chartData);

document.addEventListener('DOMContentLoaded', function() {
    
    // Top 10 Employees Chart
    const top10Ctx = document.getElementById('top10Chart').getContext('2d');
    const top10Data = {
        labels: chartData.labels.slice(0, 10),
        datasets: [{
            label: 'Solicitudes',
            data: chartData.data.slice(0, 10),
            backgroundColor: 'rgba(59, 130, 246, 0.8)',
            borderColor: '#3b82f6',
            borderWidth: 1
        }]
    };

    new Chart(top10Ctx, {
        type: 'horizontalBar',
        data: top10Data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Número de Solicitudes'
                    }
                },
                y: {
                    ticks: {
                        maxRotation: 0,
                        callback: function(value, index) {
                            const label = this.getLabelForValue(value);
                            return label.length > 20 ? label.substring(0, 20) + '...' : label;
                        }
                    }
                }
            }
        }
    });

    // Department Distribution Chart
    const deptCtx = document.getElementById('departmentChart').getContext('2d');
    const employees = @json($data->toArray());
    const departmentData = {};
    
    employees.forEach(emp => {
        const dept = emp.department ? emp.department.name : 'Sin Departamento';
        departmentData[dept] = (departmentData[dept] || 0) + emp.permission_requests_count;
    });

    new Chart(deptCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(departmentData),
            datasets: [{
                data: Object.values(departmentData),
                backgroundColor: [
                    '#3b82f6', '#ef4444', '#22c55e', '#eab308', '#8b5cf6',
                    '#f97316', '#06b6d4', '#ec4899', '#84cc16', '#f59e0b'
                ],
                borderWidth: 0,
                hoverBorderWidth: 2,
                hoverBorderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });
});

function exportToExcel() {
    const params = new URLSearchParams(window.location.search);
    params.set('type', 'active_employees');
    window.location.href = '{{ route("hr.reports.export") }}?' + params.toString();
}

function refreshData() {
    location.reload();
}
</script>
@endpush
</x-app-layout>