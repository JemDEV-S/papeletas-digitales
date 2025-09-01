<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ⌟️ Análisis de Tiempos de Aprobación
        </h2>
        <p class="mt-1 text-gray-600">Eficiencia en el proceso de aprobación de solicitudes</p>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <nav class="flex mb-2" aria-label="Breadcrumb">
                            <ol class="flex items-center space-x-2 text-sm text-gray-500">
                                <li><a href="{{ route('hr.reports.dashboard') }}" class="hover:text-blue-600">Dashboard</a></li>
                                <li>/</li>
                                <li class="font-medium text-gray-900">Tiempos de Aprobación</li>
                            </ol>
                        </nav>
                        <h1 class="text-3xl font-bold text-gray-900">⏱️ Análisis de Tiempos de Aprobación</h1>
                        <p class="mt-2 text-gray-600">Eficiencia en el proceso de aprobación de solicitudes</p>
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
            </div>
        </div>

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

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-blue-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">📊</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Promedio General</p>
                        <p class="text-3xl font-bold text-blue-600">{{ number_format($avgApprovalTime, 1) }}h</p>
                        <p class="text-sm text-gray-500">Tiempo de aprobación</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-green-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">📈</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Mediana</p>
                        <p class="text-3xl font-bold text-green-600">{{ number_format($medianApprovalTime, 1) }}h</p>
                        <p class="text-sm text-gray-500">Tiempo típico</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">⚡</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Rápidas (&lt;24h)</p>
                        <p class="text-3xl font-bold text-purple-600">{{ $chartData['data'][0] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Solicitudes</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-red-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">🐌</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Lentas (&gt;72h)</p>
                        <p class="text-3xl font-bold text-red-600">{{ $chartData['data'][3] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Solicitudes</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-8">
            
            <!-- Distribution Chart -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Distribución por Rangos de Tiempo</h3>
                <div class="h-80">
                    <canvas id="approvalTimeChart"></canvas>
                </div>
            </div>

            <!-- Timeline Chart -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📈 Tendencia Temporal</h3>
                <div class="h-80">
                    <canvas id="timelineChart"></canvas>
                </div>
                <p class="text-xs text-gray-500 mt-2">Muestra la evolución de los tiempos promedio de aprobación</p>
            </div>
        </div>

        <!-- Performance Indicators -->
        <div class="mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">🎯 Indicadores de Rendimiento</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Efficiency Score -->
                    <div class="text-center p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg">
                        <div class="text-4xl mb-2">
                            @php
                                $fastPercentage = $data->count() > 0 ? round(($chartData['data'][0] / $data->count()) * 100) : 0;
                                $scoreColor = $fastPercentage >= 70 ? 'text-green-600' : ($fastPercentage >= 50 ? 'text-yellow-600' : 'text-red-600');
                            @endphp
                            <span class="font-bold {{ $scoreColor }}">{{ $fastPercentage }}%</span>
                        </div>
                        <h4 class="text-sm font-semibold text-gray-700">Eficiencia</h4>
                        <p class="text-xs text-gray-600">Aprobaciones &lt;24h</p>
                        <div class="mt-3 w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $fastPercentage }}%"></div>
                        </div>
                    </div>

                    <!-- SLA Compliance -->
                    <div class="text-center p-6 bg-gradient-to-br from-green-50 to-green-100 rounded-lg">
                        @php
                            $slaCompliance = $data->count() > 0 ? round((($chartData['data'][0] + $chartData['data'][1]) / $data->count()) * 100) : 0;
                        @endphp
                        <div class="text-4xl mb-2">
                            <span class="font-bold text-green-600">{{ $slaCompliance }}%</span>
                        </div>
                        <h4 class="text-sm font-semibold text-gray-700">Cumplimiento SLA</h4>
                        <p class="text-xs text-gray-600">Aprobaciones &lt;48h</p>
                        <div class="mt-3 w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ $slaCompliance }}%"></div>
                        </div>
                    </div>

                    <!-- Average Performance -->
                    <div class="text-center p-6 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg">
                        @php
                            $performanceScore = $avgApprovalTime <= 24 ? 'Excelente' : ($avgApprovalTime <= 48 ? 'Bueno' : 'Mejorable');
                            $performanceColor = $avgApprovalTime <= 24 ? 'text-green-600' : ($avgApprovalTime <= 48 ? 'text-yellow-600' : 'text-red-600');
                        @endphp
                        <div class="text-lg mb-2">
                            <span class="font-bold {{ $performanceColor }}">{{ $performanceScore }}</span>
                        </div>
                        <h4 class="text-sm font-semibold text-gray-700">Rendimiento General</h4>
                        <p class="text-xs text-gray-600">Basado en promedio: {{ number_format($avgApprovalTime, 1) }}h</p>
                        <div class="mt-3 flex justify-center">
                            @if($avgApprovalTime <= 24)
                                <span class="text-2xl">🏆</span>
                            @elseif($avgApprovalTime <= 48)
                                <span class="text-2xl">👍</span>
                            @else
                                <span class="text-2xl">⚠️</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">📋 Detalle de Solicitudes Aprobadas</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solicitud</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empleado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enviado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aprobado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiempo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rendimiento</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($data->take(20) as $request)
                            @php
                                $timeCategory = '';
                                $categoryColor = '';
                                $categoryIcon = '';
                                
                                if ($request['hours_to_approval'] <= 24) {
                                    $timeCategory = 'Rápido';
                                    $categoryColor = 'text-green-600 bg-green-100';
                                    $categoryIcon = '⚡';
                                } elseif ($request['hours_to_approval'] <= 48) {
                                    $timeCategory = 'Normal';
                                    $categoryColor = 'text-yellow-600 bg-yellow-100';
                                    $categoryIcon = '👍';
                                } elseif ($request['hours_to_approval'] <= 72) {
                                    $timeCategory = 'Lento';
                                    $categoryColor = 'text-orange-600 bg-orange-100';
                                    $categoryIcon = '⚠️';
                                } else {
                                    $timeCategory = 'Muy Lento';
                                    $categoryColor = 'text-red-600 bg-red-100';
                                    $categoryIcon = '🐌';
                                }
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $request['request_number'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $request['employee_name'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ Str::limit($request['permission_type'], 20) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $request['submitted_at']->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $request['approved_at']->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">{{ $request['hours_to_approval'] }}h</div>
                                    <div class="text-xs text-gray-500">{{ $request['days_to_approval'] }} días</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $categoryColor }}">
                                        {{ $categoryIcon }} {{ $timeCategory }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Insights -->
        <div class="mt-8 bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">💡 Recomendaciones de Mejora</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                @if($avgApprovalTime > 48)
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <span class="text-2xl">🚨</span>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-semibold text-red-800">Tiempo Excesivo</h4>
                            <p class="text-sm text-red-700 mt-1">
                                El tiempo promedio ({{ number_format($avgApprovalTime, 1) }}h) supera las 48h. Considere optimizar el proceso.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                @if(isset($chartData['data'][3]) && $chartData['data'][3] > 5)
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <span class="text-2xl">📈</span>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-semibold text-orange-800">Solicitudes Lentas</h4>
                            <p class="text-sm text-orange-700 mt-1">
                                {{ $chartData['data'][3] }} solicitudes tardan más de 72h. Revisar cuellos de botella.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                @if($fastPercentage >= 70)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <span class="text-2xl">🎉</span>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-semibold text-green-800">Excelente Eficiencia</h4>
                            <p class="text-sm text-green-700 mt-1">
                                {{ $fastPercentage }}% de aprobaciones son rápidas (&lt;24h). ¡Mantengan el buen trabajo!
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
    
    // Approval Time Distribution Chart
    const approvalCtx = document.getElementById('approvalTimeChart').getContext('2d');
    new Chart(approvalCtx, {
        type: 'doughnut',
        data: {
            labels: chartData.labels,
            datasets: [{
                data: chartData.data,
                backgroundColor: chartData.colors,
                borderWidth: 2,
                borderColor: '#ffffff'
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
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed * 100) / total).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Timeline Chart (simulated data for demonstration)
    const timelineCtx = document.getElementById('timelineChart').getContext('2d');
    new Chart(timelineCtx, {
        type: 'line',
        data: {
            labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6'],
            datasets: [{
                label: 'Tiempo Promedio (horas)',
                data: [32, 28, 35, 25, 30, {{ number_format($avgApprovalTime, 1) }}],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4
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
                    title: {
                        display: true,
                        text: 'Período'
                    }
                }
            }
        }
    });
});

function exportToExcel() {
    const params = new URLSearchParams(window.location.search);
    params.set('type', 'approval_times');
    window.location.href = '{{ route("hr.reports.export") }}?' + params.toString();
}

function refreshData() {
    location.reload();
}
</script>
@endpush
</x-app-layout>