<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2 text-sm text-gray-500">
                        <li><a href="{{ route('hr.reports.dashboard') }}" class="hover:text-blue-600">Dashboard</a></li>
                        <li>/</li>
                        <li class="font-medium text-gray-900">Reporte por Departamento</li>
                    </ol>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    🏢 Solicitudes por Departamento
                </h2>
                <p class="mt-1 text-gray-600">Análisis de actividad por área organizacional</p>
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

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            @php
                $totalRequests = $data->sum('total_requests');
                $totalApproved = $data->sum('approved');
                $totalRejected = $data->sum('rejected');
                $totalPending = $data->sum('pending');
            @endphp
            
            <div class="bg-white rounded-xl shadow-sm p-6 border border-blue-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">🏢</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Solicitudes</p>
                        <p class="text-3xl font-bold text-blue-600">{{ number_format($totalRequests) }}</p>
                        <p class="text-sm text-gray-500">Todos los departamentos</p>
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
                        <p class="text-3xl font-bold text-green-600">{{ number_format($totalApproved) }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $totalRequests > 0 ? round(($totalApproved / $totalRequests) * 100, 1) : 0 }}% del total
                        </p>
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
                        <p class="text-3xl font-bold text-yellow-600">{{ number_format($totalPending) }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $totalRequests > 0 ? round(($totalPending / $totalRequests) * 100, 1) : 0 }}% del total
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">🎯</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Departamentos Activos</p>
                        <p class="text-3xl font-bold text-purple-600">{{ $data->count() }}</p>
                        <p class="text-sm text-gray-500">Con solicitudes</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-8">
            
            <!-- Department Activity Chart -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Actividad por Departamento</h3>
                <div class="h-80">
                    <canvas id="departmentChart"></canvas>
                </div>
            </div>

            <!-- Approval Rate Chart -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📈 Tasa de Aprobación</h3>
                <div class="h-80">
                    <canvas id="approvalRateChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Department Performance Cards -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">🏆 Rendimiento Departamental</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($data->take(6) as $dept)
                    @php
                        $approvalRate = $dept->total_requests > 0 ? round(($dept->approved / $dept->total_requests) * 100, 1) : 0;
                        $cardColor = $approvalRate >= 80 ? 'green' : ($approvalRate >= 60 ? 'yellow' : 'red');
                        $colors = [
                            'green' => ['bg-green-50', 'border-green-200', 'text-green-600'],
                            'yellow' => ['bg-yellow-50', 'border-yellow-200', 'text-yellow-600'],
                            'red' => ['bg-red-50', 'border-red-200', 'text-red-600']
                        ];
                    @endphp
                    <div class="bg-white rounded-lg shadow-sm border {{ $colors[$cardColor][1] }} p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-semibold text-gray-900">{{ Str::limit($dept->name, 20) }}</h4>
                            <span class="text-2xl">🏢</span>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total:</span>
                                <span class="font-semibold text-blue-600">{{ number_format($dept->total_requests) }}</span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Aprobadas:</span>
                                <span class="font-semibold text-green-600">{{ number_format($dept->approved) }}</span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Pendientes:</span>
                                <span class="font-semibold text-yellow-600">{{ number_format($dept->pending) }}</span>
                            </div>
                            
                            <div class="pt-2 border-t border-gray-200">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-medium text-gray-700">Tasa de Aprobación:</span>
                                    <span class="font-bold {{ $colors[$cardColor][2] }}">{{ $approvalRate }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-blue-400 to-blue-600 h-2 rounded-full" style="width: {{ $approvalRate }}%"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-3 border-t border-gray-200">
                            <a href="{{ route('permissions.index', ['department' => $dept->code]) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Ver solicitudes del departamento →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Detailed Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">📋 Detalle Completo por Departamento</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aprobadas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rechazadas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendientes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tasa Aprobación</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($data->sortByDesc('total_requests') as $dept)
                            @php
                                $approvalRate = $dept->total_requests > 0 ? round(($dept->approved / $dept->total_requests) * 100, 1) : 0;
                                $rateColor = $approvalRate >= 80 ? 'text-green-600' : ($approvalRate >= 60 ? 'text-yellow-600' : 'text-red-600');
                                $rateBg = $approvalRate >= 80 ? 'bg-green-100' : ($approvalRate >= 60 ? 'bg-yellow-100' : 'bg-red-100');
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-xl mr-2">🏢</span>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $dept->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $dept->code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ strtoupper($dept->code) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-xl font-bold text-blue-600">{{ number_format($dept->total_requests) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-lg font-semibold text-green-600">{{ number_format($dept->approved) }}</span>
                                        <span class="ml-2 text-xs text-gray-500">
                                            ({{ $dept->total_requests > 0 ? round(($dept->approved / $dept->total_requests) * 100, 1) : 0 }}%)
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-lg font-semibold text-red-600">{{ number_format($dept->rejected) }}</span>
                                        <span class="ml-2 text-xs text-gray-500">
                                            ({{ $dept->total_requests > 0 ? round(($dept->rejected / $dept->total_requests) * 100, 1) : 0 }}%)
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-lg font-semibold text-yellow-600">{{ number_format($dept->pending) }}</span>
                                        <span class="ml-2 text-xs text-gray-500">
                                            ({{ $dept->total_requests > 0 ? round(($dept->pending / $dept->total_requests) * 100, 1) : 0 }}%)
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium {{ $rateBg }} {{ $rateColor }}">
                                            {{ $approvalRate }}%
                                        </span>
                                        <div class="ml-2 w-16 bg-gray-200 rounded-full h-2">
                                            <div class="bg-gradient-to-r from-blue-400 to-blue-600 h-2 rounded-full" style="width: {{ $approvalRate }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('permissions.index', ['department' => $dept->code]) }}" 
                                           class="text-blue-600 hover:text-blue-800 font-medium">
                                            Ver →
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Insights -->
        <div class="mt-8 bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">💡 Análisis Departamental</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                @if($data->isNotEmpty())
                    @php
                        $topDept = $data->first();
                        $highApprovalDepts = $data->filter(function($dept) {
                            return $dept->total_requests > 0 && (($dept->approved / $dept->total_requests) * 100) >= 90;
                        })->count();
                        $avgApprovalRate = $data->filter(fn($d) => $d->total_requests > 0)
                                              ->avg(fn($d) => ($d->approved / $d->total_requests) * 100);
                    @endphp
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <span class="text-2xl">🏆</span>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-semibold text-blue-800">Departamento Más Activo</h4>
                                <p class="text-sm text-blue-700 mt-1">
                                    <strong>{{ $topDept->name }}</strong> con {{ number_format($topDept->total_requests) }} solicitudes.
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($highApprovalDepts > 0)
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <span class="text-2xl">⭐</span>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-semibold text-green-800">Excelencia Operativa</h4>
                                <p class="text-sm text-green-700 mt-1">
                                    {{ $highApprovalDepts }} departamento(s) tienen tasa de aprobación ≥90%.
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <span class="text-2xl">📊</span>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-semibold text-purple-800">Promedio General</h4>
                                <p class="text-sm text-purple-700 mt-1">
                                    Tasa de aprobación promedio: <strong>{{ number_format($avgApprovalRate, 1) }}%</strong>
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
    
    // Department Activity Chart
    const deptCtx = document.getElementById('departmentChart').getContext('2d');
    new Chart(deptCtx, {
        type: 'bar',
        data: {
            labels: chartData.labels.slice(0, 10),
            datasets: [{
                label: 'Solicitudes Totales',
                data: chartData.data.slice(0, 10),
                backgroundColor: chartData.colors.slice(0, 10).map(color => color + '90'),
                borderColor: chartData.colors.slice(0, 10),
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
                        minRotation: 0,
                        callback: function(value, index) {
                            const label = this.getLabelForValue(value);
                            return label.length > 15 ? label.substring(0, 15) + '...' : label;
                        }
                    }
                }
            }
        }
    });

    // Approval Rate Chart
    const data = @json($data);
    const approvalData = data.map(dept => {
        return dept.total_requests > 0 ? ((dept.approved / dept.total_requests) * 100).toFixed(1) : 0;
    }).slice(0, 10);
    
    const approvalCtx = document.getElementById('approvalRateChart').getContext('2d');
    new Chart(approvalCtx, {
        type: 'horizontalBar',
        data: {
            labels: chartData.labels.slice(0, 10).map(label => 
                label.length > 20 ? label.substring(0, 20) + '...' : label
            ),
            datasets: [{
                label: 'Tasa de Aprobación (%)',
                data: approvalData,
                backgroundColor: approvalData.map(rate => 
                    rate >= 80 ? 'rgba(34, 197, 94, 0.8)' : 
                    rate >= 60 ? 'rgba(234, 179, 8, 0.8)' : 
                    'rgba(239, 68, 68, 0.8)'
                ),
                borderColor: approvalData.map(rate => 
                    rate >= 80 ? '#22c55e' : 
                    rate >= 60 ? '#eab308' : 
                    '#ef4444'
                ),
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Tasa de Aprobación: ' + context.parsed.x + '%';
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
});

function exportToExcel() {
    const params = new URLSearchParams(window.location.search);
    params.set('type', 'requests_by_department');
    window.location.href = '{{ route("hr.reports.export") }}?' + params.toString();
}

function refreshData() {
    location.reload();
}
</script>
@endpush
</x-app-layout>