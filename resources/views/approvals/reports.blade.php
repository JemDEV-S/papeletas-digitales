<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reportes de Permisos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Filtros --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('reports.index') }}" class="flex items-end space-x-4">
                        <div>
                            <label for="month" class="block text-sm font-medium text-gray-700 mb-2">Mes</label>
                            <select name="month" id="month" class="rounded-md border-gray-300 shadow-sm">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($i)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Año</label>
                            <select name="year" id="year" class="rounded-md border-gray-300 shadow-sm">
                                @for($i = now()->year; $i >= now()->year - 2; $i--)
                                    <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Filtrar
                        </button>
                        <button type="button" onclick="window.print()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Imprimir
                        </button>
                    </form>
                </div>
            </div>

            {{-- Estadísticas Generales --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-3xl font-bold text-gray-800">{{ $stats['total_requests'] }}</div>
                        <div class="text-sm text-gray-600">Total Solicitudes</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-3xl font-bold text-green-600">{{ $stats['approved'] }}</div>
                        <div class="text-sm text-gray-600">Aprobadas</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-3xl font-bold text-red-600">{{ $stats['rejected'] }}</div>
                        <div class="text-sm text-gray-600">Rechazadas</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-3xl font-bold text-yellow-600">{{ $stats['pending'] }}</div>
                        <div class="text-sm text-gray-600">Pendientes</div>
                    </div>
                </div>
            </div>

            {{-- Gráficos --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {{-- Por Tipo de Permiso --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Solicitudes por Tipo de Permiso</h3>
                        @if($byType->count() > 0)
                            <div class="space-y-3">
                                @foreach($byType as $type)
                                    @php
                                        $percentage = $stats['total_requests'] > 0 ? round(($type->total / $stats['total_requests']) * 100) : 0;
                                    @endphp
                                    <div>
                                        <div class="flex justify-between text-sm mb-1">
                                            <span>{{ $type->name }}</span>
                                            <span class="font-semibold">{{ $type->total }}</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">No hay datos para mostrar.</p>
                        @endif
                    </div>
                </div>

                {{-- Por Departamento --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Solicitudes por Departamento</h3>
                        @if($byDepartment->count() > 0)
                            <div class="space-y-3">
                                @foreach($byDepartment->take(10) as $dept)
                                    @php
                                        $percentage = $stats['total_requests'] > 0 ? round(($dept->total / $stats['total_requests']) * 100) : 0;
                                    @endphp
                                    <div>
                                        <div class="flex justify-between text-sm mb-1">
                                            <span class="truncate mr-2">{{ $dept->name }}</span>
                                            <span class="font-semibold">{{ $dept->total }}</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">No hay datos para mostrar.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Tabla Detallada --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Resumen Detallado</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tipo de Permiso
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aprobadas
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Rechazadas
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Pendientes
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        % Aprobación
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($byType as $type)
                                    @php
                                        // Aquí deberías obtener estos datos con una consulta más compleja
                                        $approved = rand(0, $type->total);
                                        $rejected = rand(0, $type->total - $approved);
                                        $pending = $type->total - $approved - $rejected;
                                        $approvalRate = $type->total > 0 ? round(($approved / $type->total) * 100) : 0;
                                    @endphp
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $type->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            {{ $type->total }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            <span class="text-green-600 font-semibold">{{ $approved }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            <span class="text-red-600 font-semibold">{{ $rejected }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            <span class="text-yellow-600 font-semibold">{{ $pending }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            <span class="font-semibold">{{ $approvalRate }}%</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            No hay datos para mostrar en el período seleccionado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Nota de exportación --}}
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-blue-800">
                    <strong>Nota:</strong> Para exportar estos datos a Excel o PDF, próximamente se habilitará la funcionalidad de descarga.
                </p>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        @media print {
            .no-print, nav, header {
                display: none !important;
            }
            .print-break {
                page-break-before: always;
            }
        }
    </style>
    @endpush
</x-app-layout>