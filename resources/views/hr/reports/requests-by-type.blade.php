<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2 text-sm text-gray-500">
                        <li><a href="{{ route('hr.reports.dashboard') }}" class="hover:text-blue-600">Dashboard</a></li>
                        <li>/</li>
                        <li class="font-medium text-gray-900">Reporte por Tipo</li>
                    </ol>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    📝 Solicitudes por Tipo de Permiso
                </h2>
                <p class="mt-1 text-gray-600">Análisis de los tipos de permisos más solicitados</p>
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
                $totalRequests = $data->sum('total');
                $topType = $data->first();
            @endphp
            
            <div class="bg-white rounded-xl shadow-sm p-6 border border-blue-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">📊</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Solicitudes</p>
                        <p class="text-3xl font-bold text-blue-600">{{ number_format($totalRequests) }}</p>
                        <p class="text-sm text-gray-500">En el período</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-green-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">🎯</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Tipos Diferentes</p>
                        <p class="text-3xl font-bold text-green-600">{{ $data->count() }}</p>
                        <p class="text-sm text-gray-500">Tipos utilizados</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">👑</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Tipo Más Popular</p>
                        <p class="text-lg font-bold text-purple-600">{{ $topType ? Str::limit($topType->name, 15) : 'N/A' }}</p>
                        <p class="text-sm text-gray-500">{{ $topType ? number_format($topType->total) : 0 }} solicitudes</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-orange-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">📈</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Promedio por Tipo</p>
                        <p class="text-3xl font-bold text-orange-600">{{ $data->count() > 0 ? number_format($totalRequests / $data->count(), 1) : 0 }}</p>
                        <p class="text-sm text-gray-500">Solicitudes promedio</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-8">
            
            <!-- Pie Chart -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">🥧 Distribución Porcentual</h3>
                <div class="h-80">
                    <canvas id="typePieChart"></canvas>
                </div>
            </div>

            <!-- Horizontal Bar Chart -->
            <div class="xl:col-span-2 bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Ranking de Tipos</h3>
                <div class="h-80">
                    <canvas id="typeBarChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Detailed Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">📋 Detalle Completo por Tipo</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ranking</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo de Permiso</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Porcentaje</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tendencia</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($data->take(20) as $index => $item)
                            @php
                                $percentage = $totalRequests > 0 ? round(($item->total / $totalRequests) * 100, 1) : 0;
                                $rankingIcons = ['🥇', '🥈', '🥉'];
                                $rankingIcon = $rankingIcons[$index] ?? ($index + 1);
                                
                                // Get emoji for permission type
                                $typeEmojis = [
                                    'enfermedad' => '🏥',
                                    'gravidez' => '🤱',
                                    'capacitacion' => '📚',
                                    'citacion' => '⚖️',
                                    'funcion_edil' => '🏛️',
                                    'vacacional' => '🏖️',
                                    'representacion' => '🎭',
                                    'docencia' => '👨‍🏫',
                                    'estudios' => '🎓',
                                    'sindical' => '🤝',
                                    'lactancia' => '🍼',
                                    'comision' => '📋',
                                    'asuntos_particulares' => '👤',
                                ];
                                $typeEmoji = $typeEmojis[$item->code] ?? '📄';
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-2xl">{{ $rankingIcon }}</span>
                                        <span class="text-xs text-gray-500 font-medium">#{{ $index + 1 }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-2xl mr-3">{{ $typeEmoji }}</span>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $item->name }}</div>
                                            <div class="text-xs text-gray-500">{{ Str::limit($item->name, 30) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ strtoupper($item->code) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-2xl font-bold text-blue-600">{{ number_format($item->total) }}</span>
                                    <div class="text-xs text-gray-500">solicitudes</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-gradient-to-r from-blue-400 to-blue-600 h-2 rounded-full" style="width: {{ min($percentage, 100) }}%"></div>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ $percentage }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($index < 3)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            📈 Popular
                                        </span>
                                    @elseif($percentage < 5)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            📊 Moderado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            ⭐ Frecuente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('permissions.index', ['type' => $item->code]) }}" 
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
            <h3 class="text-lg font-semibold text-gray-900 mb-4">💡 Análisis e Insights</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                @if($data->isNotEmpty())
                    @php
                        $topThree = $data->take(3);
                        $topThreeTotal = $topThree->sum('total');
                        $topThreePercentage = $totalRequests > 0 ? round(($topThreeTotal / $totalRequests) * 100, 1) : 0;
                    @endphp
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <span class="text-2xl">📊</span>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-semibold text-blue-800">Concentración Top 3</h4>
                                <p class="text-sm text-blue-700 mt-1">
                                    Los 3 tipos más populares representan el {{ $topThreePercentage }}% de todas las solicitudes.
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($data->count() > 5)
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <span class="text-2xl">🌟</span>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-semibold text-green-800">Diversidad de Tipos</h4>
                                <p class="text-sm text-green-700 mt-1">
                                    Se utilizan {{ $data->count() }} tipos diferentes de permisos, mostrando buena diversidad.
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @php
                        $leastUsed = $data->where('total', '<', 5)->count();
                    @endphp
                    
                    @if($leastUsed > 0)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <span class="text-2xl">⚠️</span>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-semibold text-yellow-800">Tipos Subutilizados</h4>
                                <p class="text-sm text-yellow-700 mt-1">
                                    {{ $leastUsed }} tipo(s) tienen menos de 5 solicitudes. Considerar comunicación o capacitación.
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
    
    // Pie Chart
    const pieCtx = document.getElementById('typePieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: chartData.labels,
            datasets: [{
                data: chartData.data,
                backgroundColor: chartData.colors,
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverBorderWidth: 3,
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
                        padding: 15,
                        usePointStyle: true,
                        font: {
                            size: 11
                        },
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map((label, index) => {
                                    const value = data.datasets[0].data[index];
                                    return {
                                        text: label.length > 20 ? label.substring(0, 20) + '...' : label,
                                        fillStyle: data.datasets[0].backgroundColor[index],
                                        hidden: false,
                                        index: index
                                    };
                                });
                            }
                            return [];
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

    // Horizontal Bar Chart
    const barCtx = document.getElementById('typeBarChart').getContext('2d');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: chartData.labels.slice(0, 10), // Show only top 10
            datasets: [{
                label: 'Solicitudes',
                data: chartData.data.slice(0, 10),
                backgroundColor: chartData.colors.slice(0, 10).map(color => color + '90'),
                borderColor: chartData.colors.slice(0, 10),
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                },
                y: {
                    ticks: {
                        callback: function(value, index) {
                            const label = this.getLabelForValue(value);
                            return label.length > 25 ? label.substring(0, 25) + '...' : label;
                        }
                    }
                }
            }
        }
    });
});

function exportToExcel() {
    const params = new URLSearchParams(window.location.search);
    params.set('type', 'requests_by_type');
    window.location.href = '{{ route("hr.reports.export") }}?' + params.toString();
}

function refreshData() {
    location.reload();
}
</script>
@endpush
</x-app-layout>