<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2 text-sm text-gray-500">
                        <li><a href="{{ route('hr.reports.dashboard') }}" class="hover:text-blue-600">Dashboard</a></li>
                        <li>/</li>
                        <li class="font-medium text-gray-900">Reporte por Estado</li>
                    </ol>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    📊 Solicitudes por Estado
                </h2>
                <p class="mt-1 text-gray-600">Análisis de distribución de estados de las papeletas digitales</p>
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
            @php
                $totalRequests = $data->sum('total');
                $statusColors = [
                    'approved' => ['bg-green-50', 'text-green-600', 'border-green-200', '✅'],
                    'rejected' => ['bg-red-50', 'text-red-600', 'border-red-200', '❌'],
                    'pending_immediate_boss' => ['bg-yellow-50', 'text-yellow-600', 'border-yellow-200', '⏳'],
                    'pending_hr' => ['bg-orange-50', 'text-orange-600', 'border-orange-200', '🔄'],
                    'draft' => ['bg-gray-50', 'text-gray-600', 'border-gray-200', '📝'],
                    'cancelled' => ['bg-purple-50', 'text-purple-600', 'border-purple-200', '🚫']
                ];
            @endphp
            
            @foreach($data as $item)
                @php
                    $colorConfig = $statusColors[$item->status] ?? ['bg-gray-50', 'text-gray-600', 'border-gray-200', '📄'];
                    $percentage = $totalRequests > 0 ? round(($item->total / $totalRequests) * 100, 1) : 0;
                @endphp
                <div class="bg-white rounded-xl shadow-sm p-6 border {{ $colorConfig[2] }}">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 {{ $colorConfig[0] }} rounded-lg flex items-center justify-center">
                                <span class="text-2xl">{{ $colorConfig[3] }}</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">{{ ucfirst(str_replace('_', ' ', $item->status)) }}</p>
                            <p class="text-3xl font-bold {{ $colorConfig[1] }}">{{ number_format($item->total) }}</p>
                            <p class="text-sm text-gray-500">{{ $percentage }}% del total</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            
            <!-- Pie Chart -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Distribución por Estado</h3>
                <div class="h-80">
                    <canvas id="statusPieChart"></canvas>
                </div>
            </div>

            <!-- Bar Chart -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📈 Cantidad por Estado</h3>
                <div class="h-80">
                    <canvas id="statusBarChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Detailed Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">📋 Detalle por Estado</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Porcentaje</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($data->sortByDesc('total') as $item)
                            @php
                                $colorConfig = $statusColors[$item->status] ?? ['bg-gray-50', 'text-gray-600', 'border-gray-200', '📄'];
                                $percentage = $totalRequests > 0 ? round(($item->total / $totalRequests) * 100, 1) : 0;
                                $descriptions = [
                                    'approved' => 'Solicitudes completamente aprobadas y procesadas',
                                    'rejected' => 'Solicitudes rechazadas por algún motivo',
                                    'pending_immediate_boss' => 'Esperando aprobación del supervisor inmediato',
                                    'pending_hr' => 'Esperando aprobación de Recursos Humanos',
                                    'draft' => 'Borradores sin enviar',
                                    'cancelled' => 'Solicitudes canceladas por el usuario'
                                ];
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-xl mr-2">{{ $colorConfig[3] }}</span>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                            </div>
                                            <div class="text-xs {{ $colorConfig[1] }}">
                                                {{ $item->status }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-2xl font-bold {{ $colorConfig[1] }}">{{ number_format($item->total) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ $percentage }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $descriptions[$item->status] ?? 'Estado del sistema' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('permissions.index', ['status' => $item->status]) }}" 
                                       class="text-blue-600 hover:text-blue-800 font-medium">
                                        Ver solicitudes →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Insights -->
        <div class="mt-8 bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">💡 Insights y Recomendaciones</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                @php
                    $pendingTotal = $data->where('status', 'pending_immediate_boss')->first()->total ?? 0;
                    $hrPendingTotal = $data->where('status', 'pending_hr')->first()->total ?? 0;
                    $approvedTotal = $data->where('status', 'approved')->first()->total ?? 0;
                    $rejectedTotal = $data->where('status', 'rejected')->first()->total ?? 0;
                @endphp

                @if($pendingTotal > 0)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <span class="text-2xl">⚠️</span>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-semibold text-yellow-800">Atención Supervisores</h4>
                            <p class="text-sm text-yellow-700 mt-1">
                                Hay {{ $pendingTotal }} solicitudes pendientes de aprobación por supervisores inmediatos.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                @if($hrPendingTotal > 0)
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <span class="text-2xl">🔄</span>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-semibold text-orange-800">Pendiente RRHH</h4>
                            <p class="text-sm text-orange-700 mt-1">
                                {{ $hrPendingTotal }} solicitudes esperan aprobación de Recursos Humanos.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                @if($totalRequests > 0)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <span class="text-2xl">📊</span>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-semibold text-blue-800">Tasa de Aprobación</h4>
                            <p class="text-sm text-blue-700 mt-1">
                                @php $approvalRate = $totalRequests > 0 ? round((($approvedTotal) / $totalRequests) * 100, 1) : 0; @endphp
                                La tasa de aprobación actual es del {{ $approvalRate }}%.
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
    
    // Pie Chart
    const pieCtx = document.getElementById('statusPieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: chartData.labels,
            datasets: [{
                data: chartData.data,
                backgroundColor: chartData.colors,
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

    // Bar Chart
    const barCtx = document.getElementById('statusBarChart').getContext('2d');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Solicitudes',
                data: chartData.data,
                backgroundColor: chartData.colors.map(color => color + '80'), // Add transparency
                borderColor: chartData.colors,
                borderWidth: 2
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
    params.set('type', 'requests_by_status');
    window.location.href = '{{ route("hr.reports.export") }}?' + params.toString();
}

function refreshData() {
    location.reload();
}
</script>
@endpush
</x-app-layout>