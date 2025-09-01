<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2 text-sm text-gray-500">
                        <li><a href="{{ route('hr.reports.dashboard') }}" class="hover:text-blue-600">Dashboard</a></li>
                        <li>/</li>
                        <li class="font-medium text-gray-900">Cumplimiento de Reglas</li>
                    </ol>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    ✓ Análisis de Cumplimiento
                </h2>
                <p class="mt-1 text-gray-600">Monitoreo de reglas y límites por tipo de permiso</p>
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
                            <span class="text-2xl">📋</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Tipos de Permisos</p>
                        <p class="text-3xl font-bold text-blue-600">{{ $data->count() }}</p>
                        <p class="text-sm text-gray-500">Analizados</p>
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
                        <p class="text-sm font-medium text-gray-600">Total Solicitudes</p>
                        <p class="text-3xl font-bold text-green-600">{{ $data->sum('total_requests') }}</p>
                        <p class="text-sm text-gray-500">En el período</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-red-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">⚠️</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Violaciones</p>
                        <p class="text-3xl font-bold text-red-600">{{ $data->sum('violations') }}</p>
                        <p class="text-sm text-gray-500">Reglas incumplidas</p>
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
                        <p class="text-sm font-medium text-gray-600">Cumplimiento Promedio</p>
                        <p class="text-3xl font-bold text-yellow-600">{{ $data->count() > 0 ? number_format($data->avg('compliance_rate'), 1) : 0 }}%</p>
                        <p class="text-sm text-gray-500">Tasa general</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Critical Alerts -->
        @php
            $criticalViolations = $data->filter(function($item) {
                return $item['violations'] > 0 && $item['compliance_rate'] < 80;
            });
        @endphp
        
        @if($criticalViolations->count() > 0)
        <div class="mb-8">
            <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <span class="text-3xl">🚨</span>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-semibold text-red-800">Alertas Críticas de Cumplimiento</h3>
                        <p class="text-red-700">Se han detectado {{ $criticalViolations->count() }} tipo(s) de permisos con violaciones significativas</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($criticalViolations->take(6) as $violation)
                    <div class="bg-white border border-red-300 rounded-lg p-4">
                        <h4 class="font-semibold text-red-800">{{ $violation['permission_type'] }}</h4>
                        <p class="text-sm text-red-600">{{ $violation['violations'] }} violaciones detectadas</p>
                        <p class="text-sm text-red-600">{{ $violation['compliance_rate'] }}% cumplimiento</p>
                        @if(count($violation['violation_details']) > 0)
                        <p class="text-xs text-red-500 mt-1">
                            Ver detalles abajo ↓
                        </p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Compliance Rate Chart -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Tasa de Cumplimiento por Tipo de Permiso</h3>
            <div class="h-80">
                <canvas id="complianceChart"></canvas>
            </div>
        </div>

        <!-- Permission Types Analysis -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">📋 Análisis por Tipo de Permiso</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo de Permiso</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Solicitudes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Violaciones</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tasa Cumplimiento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($data->sortByDesc('violations') as $item)
                        <tr class="hover:bg-gray-50 {{ $item['violations'] > 0 && $item['compliance_rate'] < 80 ? 'bg-red-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($item['violations'] > 0 && $item['compliance_rate'] < 80)
                                        <span class="text-xl mr-2">🚨</span>
                                    @elseif($item['violations'] > 0)
                                        <span class="text-xl mr-2">⚠️</span>
                                    @else
                                        <span class="text-xl mr-2">✅</span>
                                    @endif
                                    <div class="text-sm font-medium text-gray-900">{{ $item['permission_type'] }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-bold text-gray-900">{{ $item['total_requests'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item['violations'] > 0)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        {{ $item['violations'] }} violaciones
                                    </span>
                                @else
                                    <span class="text-sm text-green-600">Sin violaciones</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $rate = $item['compliance_rate'];
                                    $rateColor = $rate >= 95 ? 'text-green-600' : ($rate >= 80 ? 'text-yellow-600' : 'text-red-600');
                                @endphp
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-{{ $rate >= 95 ? 'green' : ($rate >= 80 ? 'yellow' : 'red') }}-600 h-2 rounded-full" style="width: {{ $rate }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium {{ $rateColor }}">{{ $rate }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item['violations'] > 0 && $item['compliance_rate'] < 80)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        Crítico
                                    </span>
                                @elseif($item['violations'] > 0)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Atención
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Óptimo
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if(count($item['violation_details']) > 0)
                                    <button onclick="showViolationDetails('{{ $item['permission_type'] }}', @js($item['violation_details']))" 
                                            class="text-red-600 hover:text-red-800 font-medium">
                                        Ver detalles →
                                    </button>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No hay tipos de permisos para analizar en el período seleccionado
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Violation Details Modal (Hidden by default) -->
        <div id="violationModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Detalles de Violaciones</h3>
                        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div id="modalContent" class="max-h-96 overflow-y-auto">
                        <!-- Content will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Recommendations -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">💡 Recomendaciones de Mejora</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                @if($criticalViolations->count() > 0)
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <span class="text-2xl">🚨</span>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-semibold text-red-800">Acción Inmediata</h4>
                            <p class="text-sm text-red-700 mt-1">
                                Revisar {{ $criticalViolations->count() }} tipo(s) de permisos con violaciones críticas. 
                                Considere ajustar límites o implementar controles adicionales.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                @php
                    $goodCompliance = $data->where('compliance_rate', '>=', 95)->count();
                @endphp
                @if($goodCompliance > 0)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <span class="text-2xl">✅</span>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-semibold text-green-800">Buenas Prácticas</h4>
                            <p class="text-sm text-green-700 mt-1">
                                {{ $goodCompliance }} tipo(s) de permisos mantienen excelente cumplimiento (≥95%). 
                                Analizar sus configuraciones como modelo.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                @if($data->sum('violations') > 0)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <span class="text-2xl">📊</span>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-semibold text-blue-800">Análisis General</h4>
                            <p class="text-sm text-blue-700 mt-1">
                                {{ number_format(($data->sum('violations') / $data->sum('total_requests')) * 100, 2) }}% de violaciones sobre el total. 
                                Monitorear tendencias mensualmente.
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
const complianceData = @json($data->values()->toArray());

document.addEventListener('DOMContentLoaded', function() {
    
    // Compliance Rate Chart
    const complianceCtx = document.getElementById('complianceChart').getContext('2d');
    new Chart(complianceCtx, {
        type: 'horizontalBar',
        data: {
            labels: complianceData.map(item => 
                item.permission_type.length > 20 ? item.permission_type.substring(0, 20) + '...' : item.permission_type
            ),
            datasets: [{
                label: 'Tasa de Cumplimiento (%)',
                data: complianceData.map(item => item.compliance_rate),
                backgroundColor: complianceData.map(item => 
                    item.compliance_rate >= 95 ? 'rgba(34, 197, 94, 0.8)' :
                    item.compliance_rate >= 80 ? 'rgba(234, 179, 8, 0.8)' : 'rgba(239, 68, 68, 0.8)'
                ),
                borderColor: complianceData.map(item => 
                    item.compliance_rate >= 95 ? '#22c55e' :
                    item.compliance_rate >= 80 ? '#eab308' : '#ef4444'
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
                },
                tooltip: {
                    callbacks: {
                        afterLabel: function(context) {
                            const item = complianceData[context.dataIndex];
                            return [
                                `Total: ${item.total_requests} solicitudes`,
                                `Violaciones: ${item.violations}`
                            ];
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Porcentaje de Cumplimiento'
                    }
                }
            }
        }
    });
});

function showViolationDetails(permissionType, violations) {
    document.getElementById('modalTitle').textContent = `Violaciones - ${permissionType}`;
    
    let content = '<div class="space-y-4">';
    violations.forEach((violation, index) => {
        content += `
            <div class="border border-red-200 rounded-lg p-4 bg-red-50">
                <div class="font-semibold text-red-800">${violation.employee}</div>
                <div class="text-sm text-red-600">${violation.type}</div>
                <div class="text-xs text-red-500 mt-1">
                    Límite: ${violation.limit} | Actual: ${violation.actual}
                </div>
            </div>
        `;
    });
    content += '</div>';
    
    document.getElementById('modalContent').innerHTML = content;
    document.getElementById('violationModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('violationModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('violationModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

function exportToExcel() {
    const params = new URLSearchParams(window.location.search);
    params.set('type', 'compliance');
    window.location.href = '{{ route("hr.reports.export") }}?' + params.toString();
}

function refreshData() {
    location.reload();
}
</script>
@endpush
</x-app-layout>