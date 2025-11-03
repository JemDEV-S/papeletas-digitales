<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    📊 Dashboard de Reportes RRHH
                </h2>
                <p class="mt-1 text-gray-600">Análisis completo del sistema de papeletas digitales</p>
            </div>
            <div class="mt-4 lg:mt-0 flex space-x-3">
                <button id="refreshData" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
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

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-blue-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">📝</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Solicitudes del Mes</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_requests_month']) }}</p>
                        <p class="text-sm text-blue-600">Total del período</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-yellow-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">⏳</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pendientes</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['pending_requests']) }}</p>
                        <p class="text-sm text-yellow-600">Requieren atención</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-green-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">✅</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Aprobadas</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['approved_requests_month']) }}</p>
                        <p class="text-sm text-green-600">Este mes</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">🏢</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Fuera de Oficina</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['currently_out']) }}</p>
                        <p class="text-sm text-purple-600">Ahora mismo</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Cards -->
        @if($stats['overdue_returns'] > 0)
        <div class="mb-8">
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
                            Atención Requerida: {{ $stats['overdue_returns'] }} empleado(s) con retraso en el regreso
                        </h3>
                        <p class="text-sm text-red-700 mt-1">
                            <a href="{{ route('hr.reports.real-time-tracking') }}" class="underline font-medium">Ver detalles →</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Quick Reports Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            
            <!-- Top Permission Types Chart -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">🎯 Tipos de Permisos Más Solicitados</h3>
                    <a href="{{ route('hr.reports.requests-by-type') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Ver detalle →</a>
                </div>
                <div class="h-64">
                    <canvas id="topPermissionTypesChart"></canvas>
                </div>
            </div>

            <!-- Department Activity Chart -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">🏢 Actividad por Departamento</h3>
                    <a href="{{ route('hr.reports.requests-by-department') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Ver detalle →</a>
                </div>
                <div class="h-64">
                    <canvas id="departmentActivityChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Featured Report - Complete Report -->
        <div class="mb-8">
            <a href="{{ route('hr.reports.complete-report') }}" class="block bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-lg p-6 hover:shadow-xl transition-all transform hover:scale-[1.02]">
                <div class="flex items-center justify-between text-white">
                    <div>
                        <div class="flex items-center mb-2">
                            <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="text-xl font-bold">Reporte Completo con Seguimiento</h3>
                        </div>
                        <p class="text-blue-100 mb-3">Vista detallada de todas las solicitudes con información de aprobaciones y seguimiento físico</p>
                        <div class="flex items-center space-x-4 text-sm">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                Filtros avanzados
                            </span>
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Exportar a Excel
                            </span>
                        </div>
                    </div>
                    <div>
                        <svg class="w-16 h-16 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </div>
                </div>
            </a>
        </div>

        <!-- Reports Navigation Grid -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">📊 Acceso Rápido a Reportes</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">

                <a href="{{ route('hr.reports.requests-by-status') }}" class="block p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg hover:from-blue-100 hover:to-blue-200 transition-all transform hover:scale-105">
                    <div class="text-center">
                        <div class="text-3xl mb-2">📊</div>
                        <h4 class="text-sm font-semibold text-gray-800">Por Estado</h4>
                        <p class="text-xs text-gray-600 mt-1">Estados de solicitudes</p>
                    </div>
                </a>

                <a href="{{ route('hr.reports.requests-by-type') }}" class="block p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-lg hover:from-green-100 hover:to-green-200 transition-all transform hover:scale-105">
                    <div class="text-center">
                        <div class="text-3xl mb-2">📝</div>
                        <h4 class="text-sm font-semibold text-gray-800">Por Tipo</h4>
                        <p class="text-xs text-gray-600 mt-1">Tipos de permisos</p>
                    </div>
                </a>

                <a href="{{ route('hr.reports.requests-by-department') }}" class="block p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg hover:from-purple-100 hover:to-purple-200 transition-all transform hover:scale-105">
                    <div class="text-center">
                        <div class="text-3xl mb-2">🏢</div>
                        <h4 class="text-sm font-semibold text-gray-800">Por Departamento</h4>
                        <p class="text-xs text-gray-600 mt-1">Actividad departamental</p>
                    </div>
                </a>

                <a href="{{ route('hr.reports.approval-times') }}" class="block p-4 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg hover:from-yellow-100 hover:to-yellow-200 transition-all transform hover:scale-105">
                    <div class="text-center">
                        <div class="text-3xl mb-2">⏱️</div>
                        <h4 class="text-sm font-semibold text-gray-800">Tiempos</h4>
                        <p class="text-xs text-gray-600 mt-1">Tiempos de aprobación</p>
                    </div>
                </a>

                <a href="{{ route('hr.reports.absenteeism') }}" class="block p-4 bg-gradient-to-br from-red-50 to-red-100 rounded-lg hover:from-red-100 hover:to-red-200 transition-all transform hover:scale-105">
                    <div class="text-center">
                        <div class="text-3xl mb-2">📅</div>
                        <h4 class="text-sm font-semibold text-gray-800">Ausentismo</h4>
                        <p class="text-xs text-gray-600 mt-1">Horas utilizadas</p>
                    </div>
                </a>

                <a href="{{ route('hr.reports.active-employees') }}" class="block p-4 bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg hover:from-indigo-100 hover:to-indigo-200 transition-all transform hover:scale-105">
                    <div class="text-center">
                        <div class="text-3xl mb-2">👥</div>
                        <h4 class="text-sm font-semibold text-gray-800">Empleados</h4>
                        <p class="text-xs text-gray-600 mt-1">Más activos</p>
                    </div>
                </a>

                <a href="{{ route('hr.reports.supervisor-performance') }}" class="block p-4 bg-gradient-to-br from-pink-50 to-pink-100 rounded-lg hover:from-pink-100 hover:to-pink-200 transition-all transform hover:scale-105">
                    <div class="text-center">
                        <div class="text-3xl mb-2">👔</div>
                        <h4 class="text-sm font-semibold text-gray-800">Supervisores</h4>
                        <p class="text-xs text-gray-600 mt-1">Rendimiento</p>
                    </div>
                </a>

                <a href="{{ route('hr.reports.real-time-tracking') }}" class="block p-4 bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg hover:from-orange-100 hover:to-orange-200 transition-all transform hover:scale-105">
                    <div class="text-center">
                        <div class="text-3xl mb-2">📍</div>
                        <h4 class="text-sm font-semibold text-gray-800">Tiempo Real</h4>
                        <p class="text-xs text-gray-600 mt-1">Seguimiento actual</p>
                    </div>
                </a>

                <a href="{{ route('hr.reports.temporal-trends') }}" class="block p-4 bg-gradient-to-br from-teal-50 to-teal-100 rounded-lg hover:from-teal-100 hover:to-teal-200 transition-all transform hover:scale-105">
                    <div class="text-center">
                        <div class="text-3xl mb-2">📈</div>
                        <h4 class="text-sm font-semibold text-gray-800">Tendencias</h4>
                        <p class="text-xs text-gray-600 mt-1">Análisis temporal</p>
                    </div>
                </a>

                <a href="{{ route('hr.reports.compliance') }}" class="block p-4 bg-gradient-to-br from-cyan-50 to-cyan-100 rounded-lg hover:from-cyan-100 hover:to-cyan-200 transition-all transform hover:scale-105">
                    <div class="text-center">
                        <div class="text-3xl mb-2">✓</div>
                        <h4 class="text-sm font-semibold text-gray-800">Cumplimiento</h4>
                        <p class="text-xs text-gray-600 mt-1">Reglas y límites</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Summary Info -->
        <div class="mt-8 bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">📋 Resumen del Período</h3>
                    <div class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Tiempo promedio de aprobación:</span>
                            <span class="font-semibold text-gray-900 ml-1">
                                {{ $stats['avg_approval_time'] ? round($stats['avg_approval_time'], 1) . ' horas' : 'N/A' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-600">Tasa de aprobación:</span>
                            <span class="font-semibold text-green-600 ml-1">
                                @php
                                    $total = $stats['approved_requests_month'] + $stats['rejected_requests_month'];
                                    $rate = $total > 0 ? round(($stats['approved_requests_month'] / $total) * 100, 1) : 0;
                                @endphp
                                {{ $rate }}%
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-600">Empleados con permisos activos:</span>
                            <span class="font-semibold text-purple-600 ml-1">{{ $stats['currently_out'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500">Última actualización:</p>
                    <p class="text-sm font-medium text-gray-700">{{ now()->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Top Permission Types Chart
    const topPermissionTypesCtx = document.getElementById('topPermissionTypesChart').getContext('2d');
    new Chart(topPermissionTypesCtx, {
        type: 'doughnut',
        data: {
            labels: @json($stats['top_permission_types']->pluck('name')),
            datasets: [{
                data: @json($stats['top_permission_types']->pluck('total')),
                backgroundColor: [
                    '#3B82F6', '#EF4444', '#22C55E', '#EAB308', '#8B5CF6',
                    '#F97316', '#06B6D4', '#EC4899', '#84CC16', '#F59E0B'
                ],
                borderWidth: 0
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
                        usePointStyle: true
                    }
                }
            }
        }
    });

    // Department Activity Chart
    const departmentActivityCtx = document.getElementById('departmentActivityChart').getContext('2d');
    new Chart(departmentActivityCtx, {
        type: 'bar',
        data: {
            labels: @json($stats['department_activity']->pluck('name')),
            datasets: [{
                label: 'Solicitudes',
                data: @json($stats['department_activity']->pluck('requests_count')),
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                borderColor: '#3B82F6',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Refresh functionality
    document.getElementById('refreshData').addEventListener('click', function() {
        location.reload();
    });

    // Period filter functionality
    document.getElementById('periodFilter').addEventListener('change', function() {
        const period = this.value;
        if (period === 'custom') {
            // Show custom date picker (could be implemented with a modal)
            alert('Funcionalidad de filtro personalizado en desarrollo');
        } else {
            // Reload with period parameter
            const url = new URL(window.location.href);
            url.searchParams.set('period', period);
            window.location.href = url.toString();
        }
    });
});
</script>
@endpush
</x-app-layout>