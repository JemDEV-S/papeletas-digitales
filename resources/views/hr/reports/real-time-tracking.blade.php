<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            📍 Seguimiento en Tiempo Real
        </h2>
        <p class="mt-1 text-gray-600">Control de empleados actualmente fuera de oficina</p>
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
                                <li class="font-medium text-gray-900">Seguimiento en Tiempo Real</li>
                            </ol>
                        </nav>
                        <h1 class="text-3xl font-bold text-gray-900">📍 Seguimiento en Tiempo Real</h1>
                        <p class="mt-2 text-gray-600">Control de empleados actualmente fuera de oficina</p>
                    </div>
                    <div class="mt-4 lg:mt-0 flex flex-col sm:flex-row gap-3">
                        <button onclick="refreshData()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Actualizar
                        </button>
                        <button onclick="startAutoRefresh()" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors" id="autoRefreshBtn">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Auto-Actualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert for Overdue -->
        @if($overdue->count() > 0)
        <div class="mb-8">
            <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-red-800">¡Atención Urgente!</h3>
                        <p class="text-sm text-red-700 mt-2">
                            {{ $overdue->count() }} empleado(s) tienen retraso en el regreso. Es necesario contactarlos o verificar su situación.
                        </p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach($overdue->take(3) as $employee)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    {{ $employee['employee_name'] }} - {{ $employee['hours_out'] }}h fuera
                                </span>
                            @endforeach
                            @if($overdue->count() > 3)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    +{{ $overdue->count() - 3 }} más
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-blue-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">👥</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Fuera</p>
                        <p class="text-3xl font-bold text-blue-600">{{ $currentlyOut->count() }}</p>
                        <p class="text-sm text-gray-500">Empleados</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-red-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">⏰</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Con Retraso</p>
                        <p class="text-3xl font-bold text-red-600">{{ $overdue->count() }}</p>
                        <p class="text-sm text-gray-500">Empleados</p>
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
                        <p class="text-sm font-medium text-gray-600">En Tiempo</p>
                        <p class="text-3xl font-bold text-green-600">{{ $currentlyOut->count() - $overdue->count() }}</p>
                        <p class="text-sm text-gray-500">Empleados</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">📊</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Promedio Horas</p>
                        <p class="text-3xl font-bold text-purple-600">{{ $currentlyOut->count() > 0 ? number_format($currentlyOut->avg('hours_out'), 1) : '0' }}</p>
                        <p class="text-sm text-gray-500">Horas fuera</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-Time Status Cards -->
        @if($currentlyOut->count() > 0)
        <div class="mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">🔴 Empleados Actualmente Fuera</h3>
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <div class="w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse"></div>
                            En Vivo
                        </span>
                        <span class="text-sm text-gray-500" id="lastUpdated">
                            Actualizado: {{ now()->format('H:i:s') }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($currentlyOut as $employee)
                        @php
                            $isOverdue = $employee['is_overdue'];
                            $cardColor = $isOverdue ? 'border-red-200 bg-red-50' : 'border-blue-200 bg-blue-50';
                            $statusColor = $isOverdue ? 'text-red-600' : 'text-blue-600';
                            $statusBg = $isOverdue ? 'bg-red-100' : 'bg-blue-100';
                        @endphp
                        <div class="bg-white border {{ $cardColor }} rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-gray-900">{{ $employee['employee_name'] }}</h4>
                                    <p class="text-xs text-gray-500">DNI: {{ $employee['dni'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $employee['department'] }}</p>
                                </div>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusBg }} {{ $statusColor }}">
                                    {{ $isOverdue ? '⚠️ Retraso' : '✅ Normal' }}
                                </span>
                            </div>

                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600">Tipo de Permiso:</span>
                                    <span class="font-medium text-gray-900">{{ Str::limit($employee['permission_type'], 20) }}</span>
                                </div>
                                
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600">Salió a las:</span>
                                    <span class="font-medium text-gray-900">
                                        {{ $employee['departure_time'] ? $employee['departure_time']->format('H:i') : 'N/A' }}
                                    </span>
                                </div>
                                
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600">Tiempo fuera:</span>
                                    <span class="font-bold {{ $statusColor }}">
                                        {{ $employee['hours_out'] }}h ({{ $employee['minutes_out'] }}min)
                                    </span>
                                </div>
                            </div>

                            <!-- Progress Bar for Time -->
                            <div class="mb-3">
                                <div class="flex justify-between text-xs text-gray-600 mb-1">
                                    <span>Tiempo de permiso</span>
                                    <span>{{ $employee['hours_out'] }}h / 8h aprox.</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    @php
                                        $percentage = min(($employee['hours_out'] / 8) * 100, 100);
                                        $barColor = $percentage > 100 ? 'bg-red-500' : ($percentage > 80 ? 'bg-yellow-500' : 'bg-blue-500');
                                    @endphp
                                    <div class="{{ $barColor }} h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex space-x-2 pt-3 border-t border-gray-200">
                                <button onclick="contactEmployee('{{ $employee['dni'] }}', '{{ $employee['employee_name'] }}')" 
                                        class="flex-1 inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 transition-colors">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    Contactar
                                </button>
                                
                                @if($isOverdue)
                                <button onclick="markAsUrgent('{{ $employee['dni'] }}', '{{ $employee['employee_name'] }}')" 
                                        class="flex-1 inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 transition-colors">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    Urgente
                                </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @else
        <div class="mb-8">
            <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-4xl">✅</span>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">¡Todos en Oficina!</h3>
                <p class="text-gray-600">No hay empleados fuera de oficina en este momento.</p>
                <p class="text-sm text-gray-500 mt-2">Última actualización: {{ now()->format('d/m/Y H:i:s') }}</p>
            </div>
        </div>
        @endif

        <!-- Overdue Employees Detail -->
        @if($overdue->count() > 0)
        <div class="mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-red-700 mb-4">🚨 Empleados con Retraso - Acción Inmediata</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-red-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Empleado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Departamento</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Tipo Permiso</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Salida</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Tiempo Fuera</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($overdue as $employee)
                            <tr class="hover:bg-red-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                            <span class="text-red-600 font-bold">{{ substr($employee['employee_name'], 0, 1) }}</span>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $employee['employee_name'] }}</div>
                                            <div class="text-sm text-gray-500">{{ $employee['dni'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $employee['department'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $employee['permission_type'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $employee['departure_time'] ? $employee['departure_time']->format('d/m H:i') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-lg font-bold text-red-600">{{ $employee['hours_out'] }}h</span>
                                        <span class="ml-2 text-sm text-gray-500">({{ $employee['minutes_out'] }}min)</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button onclick="contactEmployee('{{ $employee['dni'] }}', '{{ $employee['employee_name'] }}')" 
                                                class="text-blue-600 hover:text-blue-900">Contactar</button>
                                        <button onclick="escalateIssue('{{ $employee['dni'] }}', '{{ $employee['employee_name'] }}')" 
                                                class="text-red-600 hover:text-red-900">Escalar</button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Auto-refresh Indicator -->
        <div id="refreshIndicator" class="fixed bottom-4 right-4 hidden">
            <div class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow-lg flex items-center">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                <span class="text-sm">Actualizando...</span>
            </div>
        </div>
    </div>

    @push('scripts')
<script>
let autoRefreshInterval = null;
let isAutoRefreshing = false;

document.addEventListener('DOMContentLoaded', function() {
    // Update last updated time
    updateLastUpdatedTime();
});

function refreshData() {
    showRefreshIndicator();
    
    // Simulate refresh delay
    setTimeout(() => {
        location.reload();
    }, 1000);
}

function startAutoRefresh() {
    const btn = document.getElementById('autoRefreshBtn');
    
    if (!isAutoRefreshing) {
        // Start auto-refresh
        isAutoRefreshing = true;
        autoRefreshInterval = setInterval(() => {
            refreshData();
        }, 30000); // Refresh every 30 seconds
        
        btn.innerHTML = `
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path>
            </svg>
            Detener Auto-Actualizar
        `;
        btn.classList.remove('bg-green-600', 'hover:bg-green-700');
        btn.classList.add('bg-red-600', 'hover:bg-red-700');
        
        // Show notification
        showNotification('Auto-actualización activada (cada 30s)', 'success');
    } else {
        // Stop auto-refresh
        stopAutoRefresh();
    }
}

function stopAutoRefresh() {
    const btn = document.getElementById('autoRefreshBtn');
    
    isAutoRefreshing = false;
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
    
    btn.innerHTML = `
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Auto-Actualizar
    `;
    btn.classList.remove('bg-red-600', 'hover:bg-red-700');
    btn.classList.add('bg-green-600', 'hover:bg-green-700');
    
    showNotification('Auto-actualización desactivada', 'info');
}

function showRefreshIndicator() {
    const indicator = document.getElementById('refreshIndicator');
    indicator.classList.remove('hidden');
    
    setTimeout(() => {
        indicator.classList.add('hidden');
    }, 2000);
}

function updateLastUpdatedTime() {
    const elements = document.querySelectorAll('#lastUpdated');
    const now = new Date();
    const timeString = now.toLocaleTimeString('es-ES');
    
    elements.forEach(el => {
        el.textContent = `Actualizado: ${timeString}`;
    });
}

function contactEmployee(dni, name) {
    if (confirm(`¿Desea contactar a ${name} (${dni})?`)) {
        // Here you could implement actual contact functionality
        // For now, just show a notification
        showNotification(`Contactando a ${name}...`, 'info');
        
        // Simulate contact action
        setTimeout(() => {
            showNotification(`Llamada iniciada a ${name}`, 'success');
        }, 2000);
    }
}

function markAsUrgent(dni, name) {
    if (confirm(`¿Marcar a ${name} como URGENTE? Esto enviará notificaciones a supervisores.`)) {
        showNotification(`${name} marcado como URGENTE. Notificando supervisores...`, 'warning');
        
        // Here you could implement API call to mark as urgent
        setTimeout(() => {
            showNotification(`Supervisores notificados sobre ${name}`, 'success');
        }, 2000);
    }
}

function escalateIssue(dni, name) {
    if (confirm(`¿Escalar el caso de ${name} a la administración?`)) {
        showNotification(`Escalando caso de ${name} a administración...`, 'warning');
        
        // Here you could implement escalation logic
        setTimeout(() => {
            showNotification(`Caso de ${name} escalado exitosamente`, 'success');
        }, 2000);
    }
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    };
    
    notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Slide in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Slide out and remove
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 4000);
}

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
});
</script>
@endpush
</x-app-layout>