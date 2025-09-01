<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard de Seguimiento Físico - RRHH') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Scanner Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-qrcode text-blue-600 mr-2"></i>
                        Escáner de DNI
                    </h3>
                    
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="flex-1">
                            <input type="text" 
                                   id="dni-scanner" 
                                   placeholder="Escanee o ingrese el DNI del empleado..." 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg font-mono"
                                   maxlength="8"
                                   pattern="[0-9]{8}">
                        </div>
                        <button id="manual-scan" 
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-search mr-2"></i>
                            Buscar Manual
                        </button>
                    </div>
                    
                    <div id="scan-result" class="hidden">
                        <div class="bg-gray-50 rounded-lg p-4 border">
                            <div id="employee-info" class="mb-4"></div>
                            <div id="tracking-info" class="mb-4"></div>
                            <div id="action-buttons" class="flex space-x-3"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Real-time Status Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                                    <i class="fas fa-clock text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Pendientes de Salida
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900" id="pending-count">
                                        {{ $pendingDepartures->count() }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-yellow-500 text-white">
                                    <i class="fas fa-sign-out-alt text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Fuera de Oficina
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900" id="out-count">
                                        {{ $currentlyOut->count() }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-red-500 text-white">
                                    <i class="fas fa-exclamation-triangle text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Con Retraso
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900" id="overdue-count">
                                        {{ $overdue->count() }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Trackings Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-list text-blue-600 mr-2"></i>
                            Seguimientos Activos
                        </h3>
                        <button id="refresh-table" 
                                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-sync-alt mr-2"></i>
                            Actualizar
                        </button>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="trackings-table">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Empleado
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    DNI
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tipo Permiso
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Salida
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Horas Usadas
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="trackings-tbody">
                            <!-- Populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- CSRF Token for AJAX requests -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const dniScanner = document.getElementById('dni-scanner');
    const manualScan = document.getElementById('manual-scan');
    const scanResult = document.getElementById('scan-result');
    const refreshTable = document.getElementById('refresh-table');
    
    // Auto-focus the scanner input
    dniScanner.focus();
    
    // Configure CSRF token for AJAX requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Scanner functionality
    dniScanner.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleDniScan();
        }
    });
    
    manualScan.addEventListener('click', handleDniScan);
    
    function handleDniScan() {
        const dni = dniScanner.value.trim();
        
        if (dni.length !== 8 || !/^\d+$/.test(dni)) {
            showAlert('Por favor ingrese un DNI válido de 8 dígitos', 'error');
            return;
        }
        
        scanDni(dni);
    }
    
    function scanDni(dni) {
        fetch('{{ route("tracking.api.scan-dni") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ dni: dni })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayScanResult(data);
            } else {
                showAlert(data.message, 'error');
                hideScanResult();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error al procesar el escaneo', 'error');
        });
    }
    
    function displayScanResult(data) {
        const employeeInfo = document.getElementById('employee-info');
        const trackingInfo = document.getElementById('tracking-info');
        const actionButtons = document.getElementById('action-buttons');
        
        employeeInfo.innerHTML = `
            <h4 class="font-medium text-gray-900">Empleado: ${data.employee.name}</h4>
            <p class="text-sm text-gray-600">DNI: ${data.employee.dni}</p>
        `;
        
        trackingInfo.innerHTML = `
            <div class="flex items-center space-x-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${data.tracking.status_color}-100 text-${data.tracking.status_color}-800">
                    ${data.tracking.status_label}
                </span>
                <span class="text-sm text-gray-600">Tipo: ${data.tracking.permission_type}</span>
                ${data.tracking.departure_datetime ? `<span class="text-sm text-gray-600">Salió: ${formatDateTime(data.tracking.departure_datetime)}</span>` : ''}
            </div>
        `;
        
        let buttonClass, buttonText, actionHandler;
        if (data.action_needed === 'departure') {
            buttonClass = 'bg-blue-600 hover:bg-blue-700';
            buttonText = '<i class="fas fa-sign-out-alt mr-2"></i>Registrar Salida';
            actionHandler = `registerDeparture(${data.tracking.id})`;
        } else {
            buttonClass = 'bg-green-600 hover:bg-green-700';
            buttonText = '<i class="fas fa-sign-in-alt mr-2"></i>Registrar Regreso';
            actionHandler = `registerReturn(${data.tracking.id})`;
        }
        
        actionButtons.innerHTML = `
            <button onclick="${actionHandler}" class="px-4 py-2 ${buttonClass} text-white rounded-lg transition-colors">
                ${buttonText}
            </button>
            <button onclick="hideScanResult()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                Cancelar
            </button>
        `;
        
        scanResult.classList.remove('hidden');
    }
    
    window.registerDeparture = function(trackingId) {
        const notes = prompt('Observaciones (opcional):');
        
        fetch('{{ route("tracking.api.register-departure") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ 
                tracking_id: trackingId,
                notes: notes 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                hideScanResult();
                clearScanner();
                refreshTrackingsTable();
                updateCounts();
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error al registrar la salida', 'error');
        });
    };
    
    window.registerReturn = function(trackingId) {
        const notes = prompt('Observaciones (opcional):');
        
        fetch('{{ route("tracking.api.register-return") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ 
                tracking_id: trackingId,
                notes: notes 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                hideScanResult();
                clearScanner();
                refreshTrackingsTable();
                updateCounts();
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error al registrar el regreso', 'error');
        });
    };
    
    window.hideScanResult = function() {
        scanResult.classList.add('hidden');
    };
    
    function clearScanner() {
        dniScanner.value = '';
        dniScanner.focus();
    }
    
    function showAlert(message, type) {
        const alertClass = type === 'success' ? 'bg-green-100 text-green-800 border-green-200' : 'bg-red-100 text-red-800 border-red-200';
        const alertHtml = `
            <div class="${alertClass} border px-4 py-3 rounded-lg mb-4" role="alert">
                ${message}
            </div>
        `;
        
        const existingAlert = document.querySelector('[role="alert"]');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        scanResult.insertAdjacentHTML('beforebegin', alertHtml);
        
        setTimeout(() => {
            const alert = document.querySelector('[role="alert"]');
            if (alert) alert.remove();
        }, 5000);
    }
    
    function formatDateTime(dateTime) {
        return new Date(dateTime).toLocaleString('es-PE');
    }
    
    // Table management
    refreshTable.addEventListener('click', refreshTrackingsTable);
    
    function refreshTrackingsTable() {
        fetch('{{ route("tracking.api.active-trackings") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateTrackingsTable(data.trackings);
                }
            })
            .catch(error => console.error('Error refreshing table:', error));
    }
    
    function updateTrackingsTable(trackings) {
        const tbody = document.getElementById('trackings-tbody');
        tbody.innerHTML = '';
        
        // URL base para los enlaces - generada desde PHP
        const trackingShowBaseUrl = '{{ route("tracking.show", ":id") }}';
        
        trackings.forEach(tracking => {
            const row = document.createElement('tr');
            // Reemplazar :id con el ID real del tracking
            const trackingShowUrl = trackingShowBaseUrl.replace(':id', tracking.id);
            
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    ${tracking.employee_name}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${tracking.employee_dni}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${tracking.permission_type}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${tracking.status_color}-100 text-${tracking.status_color}-800">
                        ${tracking.status_label}
                    </span>
                    ${tracking.is_overdue ? '<span class="ml-2 text-red-600 text-xs">RETRASADO</span>' : ''}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${tracking.departure_datetime ? formatDateTime(tracking.departure_datetime) : '-'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${tracking.actual_hours_used || '0'} horas
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <a href="${trackingShowUrl}" 
                       class="text-blue-600 hover:text-blue-900">Ver Detalles</a>
                </td>
            `;
            tbody.appendChild(row);
        });
    }
    
    function updateCounts() {
        // This would ideally be done with a separate API call
        // For now, we'll refresh the page counts when the table refreshes
        refreshTrackingsTable();
    }
    
    // Auto-refresh every 30 seconds
    setInterval(refreshTrackingsTable, 30000);
    
    // Initial load
    refreshTrackingsTable();
});
</script>

    <style>
    /* Additional styles for the dashboard */
    #dni-scanner:focus {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: .5;
        }
    }
    </style>
</x-app-layout>