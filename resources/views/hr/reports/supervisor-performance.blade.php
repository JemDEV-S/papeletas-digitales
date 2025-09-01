<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2 text-sm text-gray-500">
                        <li><a href="{{ route('hr.reports.dashboard') }}" class="hover:text-blue-600">Dashboard</a></li>
                        <li>/</li>
                        <li class="font-medium text-gray-900">Rendimiento de Supervisores</li>
                    </ol>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    👔 Rendimiento de Supervisores
                </h2>
                <p class="mt-1 text-gray-600">Métricas de performance y eficiencia en aprobaciones</p>
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
                            <span class="text-2xl">👔</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Supervisores Activos</p>
                        <p class="text-3xl font-bold text-blue-600">{{ $data->count() }}</p>
                        <p class="text-sm text-gray-500">En el período</p>
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
                        <p class="text-sm font-medium text-gray-600">Total Aprobaciones</p>
                        <p class="text-3xl font-bold text-green-600">{{ $data->sum('approved') }}</p>
                        <p class="text-sm text-gray-500">Solicitudes aprobadas</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-yellow-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">📊</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Tasa Aprobación Promedio</p>
                        <p class="text-3xl font-bold text-yellow-600">{{ $data->count() > 0 ? number_format($data->avg('approval_rate'), 1) : 0 }}%</p>
                        <p class="text-sm text-gray-500">Porcentaje general</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">⏱️</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Tiempo Promedio</p>
                        <p class="text-3xl font-bold text-purple-600">{{ $data->count() > 0 ? number_format($data->avg('avg_approval_time_hours'), 1) : 0 }}h</p>
                        <p class="text-sm text-gray-500">Horas para aprobar</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            
            <!-- Approval Rate Chart -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Tasa de Aprobación por Supervisor</h3>
                <div class="h-80">
                    <canvas id="approvalRateChart"></canvas>
                </div>
            </div>

            <!-- Response Time Chart -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">⏱️ Tiempo Promedio de Respuesta</h3>
                <div class="h-80">
                    <canvas id="responseTimeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Performers Section -->
        @if($data->count() > 0)
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">🏆 Supervisores Destacados</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                @php
                    $bestApprovalRate = $data->sortByDesc('approval_rate')->first();
                    $fastestResponse = $data->where('avg_approval_time_hours', '>', 0)->sortBy('avg_approval_time_hours')->first();
                    $mostActive = $data->sortByDesc('total_approvals')->first();
                @endphp

                @if($bestApprovalRate)
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-200 rounded-full flex items-center justify-center">
                                <span class="text-2xl">🎯</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-sm font-semibold text-green-800">Mayor Tasa de Aprobación</h4>
                            <p class="text-lg font-bold text-gray-900">{{ $bestApprovalRate['name'] }}</p>
                            <p class="text-sm text-gray-600">{{ $bestApprovalRate['department'] }}</p>
                            <p class="text-sm font-medium text-green-700">{{ $bestApprovalRate['approval_rate'] }}% de aprobación</p>
                        </div>
                    </div>
                </div>
                @endif

                @if($fastestResponse)
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-200 rounded-full flex items-center justify-center">
                                <span class="text-2xl">⚡</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-sm font-semibold text-blue-800">Respuesta Más Rápida</h4>
                            <p class="text-lg font-bold text-gray-900">{{ $fastestResponse['name'] }}</p>
                            <p class="text-sm text-gray-600">{{ $fastestResponse['department'] }}</p>
                            <p class="text-sm font-medium text-blue-700">{{ $fastestResponse['avg_approval_time_hours'] }} horas promedio</p>
                        </div>
                    </div>
                </div>
                @endif

                @if($mostActive)
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-purple-200 rounded-full flex items-center justify-center">
                                <span class="text-2xl">🔥</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-sm font-semibold text-purple-800">Más Activo</h4>
                            <p class="text-lg font-bold text-gray-900">{{ $mostActive['name'] }}</p>
                            <p class="text-sm text-gray-600">{{ $mostActive['department'] }}</p>
                            <p class="text-sm font-medium text-purple-700">{{ $mostActive['total_approvals'] }} aprobaciones</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Detailed Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">📋 Métricas Detalladas de Supervisores</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supervisor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aprobadas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rechazadas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendientes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tasa Aprobación</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiempo Promedio</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($data as $supervisor)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-blue-800">{{ substr($supervisor['name'], 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $supervisor['name'] }}</div>
                                        <div class="text-sm text-gray-500">Supervisor</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $supervisor['department'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-bold text-gray-900">{{ $supervisor['total_approvals'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-green-600 font-medium">{{ $supervisor['approved'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-red-600 font-medium">{{ $supervisor['rejected'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-yellow-600 font-medium">{{ $supervisor['pending'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $rate = $supervisor['approval_rate'];
                                    $rateColor = $rate >= 90 ? 'text-green-600' : ($rate >= 70 ? 'text-yellow-600' : 'text-red-600');
                                @endphp
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $rate }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium {{ $rateColor }}">{{ $rate }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $hours = $supervisor['avg_approval_time_hours'];
                                    $timeColor = $hours <= 24 ? 'text-green-600' : ($hours <= 48 ? 'text-yellow-600' : 'text-red-600');
                                @endphp
                                <span class="text-sm font-medium {{ $timeColor }}">{{ $hours }}h</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                No hay supervisores con actividad en el período seleccionado
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Insights -->
        <div class="mt-8 bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">💡 Análisis de Performance</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                @if($data->count() > 0)
                @php
                    $excellentPerformers = $data->where('approval_rate', '>=', 90)->where('avg_approval_time_hours', '<=', 24);
                    
                    // Usar filter() en lugar de orWhere()
                    $needsImprovement = $data->filter(function($supervisor) {
                        return $supervisor['approval_rate'] < 70 || $supervisor['avg_approval_time_hours'] > 72;
                    });
                @endphp

                @if($excellentPerformers->count() > 0)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <span class="text-2xl">⭐</span>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-semibold text-green-800">Excelente Performance</h4>
                            <p class="text-sm text-green-700 mt-1">
                                {{ $excellentPerformers->count() }} supervisor(es) con tasa de aprobación ≥90% y respuesta ≤24h.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                @if($needsImprovement->count() > 0)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <span class="text-2xl">📈</span>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-semibold text-yellow-800">Oportunidades de Mejora</h4>
                            <p class="text-sm text-yellow-700 mt-1">
                                {{ $needsImprovement->count() }} supervisor(es) podrían beneficiarse de capacitación adicional.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <span class="text-2xl">📊</span>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-semibold text-blue-800">Métricas Generales</h4>
                            <p class="text-sm text-blue-700 mt-1">
                                Tasa promedio: {{ number_format($data->avg('approval_rate'), 1) }}% | 
                                Tiempo promedio: {{ number_format($data->avg('avg_approval_time_hours'), 1) }}h
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
const supervisorData = @json($data->values()->toArray());

document.addEventListener('DOMContentLoaded', function() {
    
    // Approval Rate Chart
    const approvalCtx = document.getElementById('approvalRateChart').getContext('2d');
    new Chart(approvalCtx, {
        type: 'horizontalBar',
        data: {
            labels: supervisorData.map(s => s.name.length > 15 ? s.name.substring(0, 15) + '...' : s.name),
            datasets: [{
                label: 'Tasa de Aprobación (%)',
                data: supervisorData.map(s => s.approval_rate),
                backgroundColor: supervisorData.map(s => 
                    s.approval_rate >= 90 ? 'rgba(34, 197, 94, 0.8)' :
                    s.approval_rate >= 70 ? 'rgba(234, 179, 8, 0.8)' : 'rgba(239, 68, 68, 0.8)'
                ),
                borderColor: supervisorData.map(s => 
                    s.approval_rate >= 90 ? '#22c55e' :
                    s.approval_rate >= 70 ? '#eab308' : '#ef4444'
                ),
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
                x: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Porcentaje de Aprobación'
                    }
                }
            }
        }
    });

    // Response Time Chart
    const timeCtx = document.getElementById('responseTimeChart').getContext('2d');
    new Chart(timeCtx, {
        type: 'bar',
        data: {
            labels: supervisorData.map(s => s.name.length > 15 ? s.name.substring(0, 15) + '...' : s.name),
            datasets: [{
                label: 'Tiempo Promedio (Horas)',
                data: supervisorData.map(s => s.avg_approval_time_hours),
                backgroundColor: supervisorData.map(s => 
                    s.avg_approval_time_hours <= 24 ? 'rgba(34, 197, 94, 0.8)' :
                    s.avg_approval_time_hours <= 48 ? 'rgba(234, 179, 8, 0.8)' : 'rgba(239, 68, 68, 0.8)'
                ),
                borderColor: supervisorData.map(s => 
                    s.avg_approval_time_hours <= 24 ? '#22c55e' :
                    s.avg_approval_time_hours <= 48 ? '#eab308' : '#ef4444'
                ),
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
                    title: {
                        display: true,
                        text: 'Horas'
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 0
                    }
                }
            }
        }
    });
});

function exportToExcel() {
    const params = new URLSearchParams(window.location.search);
    params.set('type', 'supervisor_performance');
    window.location.href = '{{ route("hr.reports.export") }}?' + params.toString();
}

function refreshData() {
    location.reload();
}
</script>
@endpush
</x-app-layout>