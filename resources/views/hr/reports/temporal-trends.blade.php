<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2 text-sm text-gray-500">
                        <li><a href="{{ route('hr.reports.dashboard') }}" class="hover:text-blue-600">Dashboard</a></li>
                        <li>/</li>
                        <li class="font-medium text-gray-900">Tendencias Temporales</li>
                    </ol>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    📈 Tendencias Temporales
                </h2>
                <p class="mt-1 text-gray-600">Análisis de patrones y evolución de solicitudes en el tiempo</p>
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Año</label>
                        <select name="year" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Período</label>
                        <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="monthly" {{ $type == 'monthly' ? 'selected' : '' }}>Mensual</option>
                            <option value="weekly" {{ $type == 'weekly' ? 'selected' : '' }}>Semanal</option>
                        </select>
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
                            <span class="text-2xl">📊</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total del Año</p>
                        <p class="text-3xl font-bold text-blue-600">{{ $data->sum('total') }}</p>
                        <p class="text-sm text-gray-500">Solicitudes en {{ $year }}</p>
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
                        <p class="text-3xl font-bold text-green-600">{{ $data->sum('approved') }}</p>
                        <p class="text-sm text-gray-500">{{ $data->sum('total') > 0 ? number_format(($data->sum('approved') / $data->sum('total')) * 100, 1) : 0 }}% del total</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-red-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">❌</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Rechazadas</p>
                        <p class="text-3xl font-bold text-red-600">{{ $data->sum('rejected') }}</p>
                        <p class="text-sm text-gray-500">{{ $data->sum('total') > 0 ? number_format(($data->sum('rejected') / $data->sum('total')) * 100, 1) : 0 }}% del total</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">📈</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Promedio {{ $type == 'monthly' ? 'Mensual' : 'Semanal' }}</p>
                        <p class="text-3xl font-bold text-purple-600">{{ $data->count() > 0 ? number_format($data->avg('total'), 1) : 0 }}</p>
                        <p class="text-sm text-gray-500">Solicitudes por período</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Trends Chart -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📈 Evolución Temporal de Solicitudes</h3>
            <div class="h-96">
                <canvas id="trendsChart"></canvas>
            </div>
        </div>

        <!-- Peak Analysis -->
        @if($data->count() > 0)
        @php
            $maxPeriod = $data->sortByDesc('total')->first();
            $minPeriod = $data->sortBy('total')->where('total', '>', 0)->first();
            $avgTotal = $data->avg('total');
        @endphp
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            
            <!-- Peak Period -->
            @if($maxPeriod)
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">🏔️ Período Pico</h3>
                <div class="text-center">
                    <div class="text-4xl mb-2">🔥</div>
                    <div class="text-2xl font-bold text-red-600">{{ $maxPeriod->total }}</div>
                    <div class="text-sm text-gray-600 mb-2">solicitudes</div>
                    <div class="text-lg font-semibold text-gray-900">{{ $maxPeriod->period_label }}</div>
                    <div class="mt-2 text-sm text-gray-500">
                        {{ number_format((($maxPeriod->total - $avgTotal) / $avgTotal) * 100, 1) }}% sobre el promedio
                    </div>
                </div>
            </div>
            @endif

            <!-- Low Period -->
            @if($minPeriod)
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📉 Período Mínimo</h3>
                <div class="text-center">
                    <div class="text-4xl mb-2">📊</div>
                    <div class="text-2xl font-bold text-blue-600">{{ $minPeriod->total }}</div>
                    <div class="text-sm text-gray-600 mb-2">solicitudes</div>
                    <div class="text-lg font-semibold text-gray-900">{{ $minPeriod->period_label }}</div>
                    <div class="mt-2 text-sm text-gray-500">
                        {{ number_format((($avgTotal - $minPeriod->total) / $avgTotal) * 100, 1) }}% bajo el promedio
                    </div>
                </div>
            </div>
            @endif

            <!-- Trend Analysis -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Análisis de Tendencia</h3>
                <div class="text-center">
                    @php
                        $firstHalf = $data->take(ceil($data->count() / 2));
                        $secondHalf = $data->skip(ceil($data->count() / 2));
                        $trend = $secondHalf->avg('total') > $firstHalf->avg('total') ? 'up' : 'down';
                        $trendPercent = abs((($secondHalf->avg('total') - $firstHalf->avg('total')) / $firstHalf->avg('total')) * 100);
                    @endphp
                    <div class="text-4xl mb-2">{{ $trend == 'up' ? '📈' : '📉' }}</div>
                    <div class="text-2xl font-bold {{ $trend == 'up' ? 'text-green-600' : 'text-red-600' }}">
                        {{ $trend == 'up' ? '+' : '-' }}{{ number_format($trendPercent, 1) }}%
                    </div>
                    <div class="text-sm text-gray-600 mb-2">cambio</div>
                    <div class="text-lg font-semibold text-gray-900">
                        Tendencia {{ $trend == 'up' ? 'Creciente' : 'Decreciente' }}
                    </div>
                    <div class="mt-2 text-sm text-gray-500">
                        Comparando primera vs segunda mitad del año
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Detailed Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">📋 Detalle {{ $type == 'monthly' ? 'Mensual' : 'Semanal' }} - {{ $year }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Solicitudes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aprobadas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rechazadas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tasa Aprobación</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">vs Promedio</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($data as $period)
                        @php
                            $approvalRate = $period->total > 0 ? round(($period->approved / $period->total) * 100, 1) : 0;
                            $vsAverage = $avgTotal > 0 ? round((($period->total - $avgTotal) / $avgTotal) * 100, 1) : 0;
                        @endphp
                        <tr class="hover:bg-gray-50 {{ $period->total == $maxPeriod->total ? 'bg-red-50' : ($period->total == $minPeriod->total && $period->total > 0 ? 'bg-blue-50' : '') }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($period->total == $maxPeriod->total)
                                        <span class="text-xl mr-2">🔥</span>
                                    @elseif($period->total == $minPeriod->total && $period->total > 0)
                                        <span class="text-xl mr-2">📊</span>
                                    @endif
                                    <div class="text-sm font-medium text-gray-900">{{ $period->period_label }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-lg font-bold text-gray-900">{{ $period->total }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-green-600 font-medium">{{ $period->approved }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-red-600 font-medium">{{ $period->rejected }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $approvalRate }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ $approvalRate }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $vsAverage > 0 ? 'bg-red-100 text-red-800' : ($vsAverage < 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ $vsAverage > 0 ? '+' : '' }}{{ $vsAverage }}%
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No hay datos para el año {{ $year }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Insights -->
        <div class="mt-8 bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">💡 Insights y Proyecciones</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                @if($data->count() > 0)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <span class="text-2xl">📊</span>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-semibold text-blue-800">Patrón Anual</h4>
                            <p class="text-sm text-blue-700 mt-1">
                                Promedio de {{ number_format($data->avg('total'), 1) }} solicitudes por {{ $type == 'monthly' ? 'mes' : 'semana' }}.
                                Variación: {{ number_format((($maxPeriod->total - $minPeriod->total) / $avgTotal) * 100, 1) }}%
                            </p>
                        </div>
                    </div>
                </div>

                @if($data->sum('total') > 0)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <span class="text-2xl">✅</span>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-semibold text-green-800">Tasa de Éxito</h4>
                            <p class="text-sm text-green-700 mt-1">
                                {{ number_format(($data->sum('approved') / $data->sum('total')) * 100, 1) }}% de solicitudes aprobadas en {{ $year }}.
                                {{ $data->sum('approved') }} de {{ $data->sum('total') }} solicitudes.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                @if(isset($trend))
                <div class="bg-{{ $trend == 'up' ? 'yellow' : 'purple' }}-50 border border-{{ $trend == 'up' ? 'yellow' : 'purple' }}-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <span class="text-2xl">{{ $trend == 'up' ? '📈' : '📉' }}</span>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-semibold text-{{ $trend == 'up' ? 'yellow' : 'purple' }}-800">Proyección</h4>
                            <p class="text-sm text-{{ $trend == 'up' ? 'yellow' : 'purple' }}-700 mt-1">
                                La tendencia {{ $trend == 'up' ? 'creciente' : 'decreciente' }} sugiere 
                                {{ $trend == 'up' ? 'mayor' : 'menor' }} actividad en próximos períodos.
                            </p>
                        </div>
                    </div>
                </div>
                @endif
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const chartData = @json($chartData);

document.addEventListener('DOMContentLoaded', function() {
    
    // Temporal Trends Chart
    const trendsCtx = document.getElementById('trendsChart').getContext('2d');
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: chartData.datasets.map(dataset => ({
                ...dataset,
                fill: true,
                tension: 0.3,
                pointRadius: 6,
                pointHoverRadius: 8
            }))
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            return context[0].label;
                        },
                        afterLabel: function(context) {
                            const dataIndex = context.dataIndex;
                            const total = chartData.datasets.reduce((sum, dataset) => 
                                sum + (dataset.data[dataIndex] || 0), 0);
                            return `Total: ${total} solicitudes`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Número de Solicitudes'
                    },
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: '{{ $type == "monthly" ? "Meses" : "Semanas" }} de {{ $year }}'
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});

function exportToExcel() {
    const params = new URLSearchParams(window.location.search);
    params.set('type', 'temporal_trends');
    window.location.href = '{{ route("hr.reports.export") }}?' + params.toString();
}

function refreshData() {
    location.reload();
}
</script>
@endpush
</x-app-layout>