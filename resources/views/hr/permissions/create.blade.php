<x-app-layout>
<style>
    #user_results {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .user-result-item:last-child {
        border-bottom: none;
    }
</style>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Crear Permiso Aprobado</h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Crear un permiso directamente aprobado con tracking completo (Solo para casos especiales)
                    </p>
                </div>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
            </div>
        </div>

        <!-- Alert de advertencia -->
        <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>Importante:</strong> Esta función crea permisos que omiten el flujo normal de aprobaciones.
                        Úsela solo para casos excepcionales como permisos retroactivos o situaciones de emergencia.
                    </p>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-times-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Formulario -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <form action="{{ route('hr.permissions.store') }}" method="POST" id="hrPermissionForm">
                @csrf

                <div class="p-6 space-y-6">
                    <!-- Sección 1: Información del Empleado -->
                    <div class="border-b border-gray-200 pb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">1. Información del Empleado</h2>

                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="user_search" class="block text-sm font-medium text-gray-700">
                                    Buscar Empleado <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" id="user_search"
                                        placeholder="Buscar por nombre o DNI..."
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        autocomplete="off">
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                        <i class="fas fa-search"></i>
                                    </div>

                                    <!-- Dropdown de resultados -->
                                    <div id="user_results" class="hidden absolute z-10 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                        <!-- Resultados se llenan dinámicamente -->
                                    </div>
                                </div>

                                <!-- Input hidden para el user_id real -->
                                <input type="hidden" name="user_id" id="user_id" required>
                                @error('user_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Preview de empleado seleccionado -->
                            <div id="userPreview" class="hidden bg-blue-50 border border-blue-200 rounded-md p-4">
                                <h3 class="text-sm font-semibold text-blue-900 mb-2">Empleado Seleccionado:</h3>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div><strong>Nombre:</strong> <span id="previewName"></span></div>
                                    <div><strong>DNI:</strong> <span id="previewDni"></span></div>
                                    <div><strong>Email:</strong> <span id="previewEmail"></span></div>
                                    <div><strong>Departamento:</strong> <span id="previewDepartment"></span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección 2: Datos del Permiso -->
                    <div class="border-b border-gray-200 pb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">2. Datos del Permiso</h2>

                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="permission_type_id" class="block text-sm font-medium text-gray-700">
                                    Tipo de Permiso <span class="text-red-500">*</span>
                                </label>
                                <select name="permission_type_id" id="permission_type_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('permission_type_id') border-red-500 @enderror">
                                    <option value="">Seleccione un tipo...</option>
                                    @foreach($permissionTypes as $type)
                                        <option value="{{ $type->id }}" {{ old('permission_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('permission_type_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="reason" class="block text-sm font-medium text-gray-700">
                                    Motivo del Permiso <span class="text-red-500">*</span>
                                </label>
                                <textarea name="reason" id="reason" rows="3" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('reason') border-red-500 @enderror"
                                    placeholder="Ingrese el motivo del permiso...">{{ old('reason') }}</textarea>
                                @error('reason')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Sección 3: Tracking Real (Salida y Regreso) -->
                    <div class="border-b border-gray-200 pb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">3. Tracking Real (Salida y Regreso)</h2>
                        <p class="text-sm text-gray-600 mb-4">
                            Registre las horas reales en que el empleado salió y regresó
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="departure_datetime" class="block text-sm font-medium text-gray-700">
                                    Hora de Salida Real <span class="text-red-500">*</span>
                                </label>
                                <input type="datetime-local" name="departure_datetime" id="departure_datetime" required
                                    value="{{ old('departure_datetime') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('departure_datetime') border-red-500 @enderror">
                                @error('departure_datetime')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="return_datetime" class="block text-sm font-medium text-gray-700">
                                    Hora de Regreso Real <span class="text-red-500">*</span>
                                </label>
                                <input type="datetime-local" name="return_datetime" id="return_datetime" required
                                    value="{{ old('return_datetime') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('return_datetime') border-red-500 @enderror">
                                @error('return_datetime')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-2 text-sm text-gray-600">
                            <strong>Horas reales usadas:</strong> <span id="actualHours">0</span> horas
                        </div>
                    </div>

                    <!-- Sección 4: Notas Adicionales -->
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">4. Notas Adicionales</h2>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">
                                Notas / Justificación de Creación Directa
                            </label>
                            <textarea name="notes" id="notes" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Ej: Permiso retroactivo por emergencia médica, se registra después del hecho...">{{ old('notes') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                Estas notas se guardarán en el tracking para referencia futura
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer con botones -->
                <div class="bg-gray-50 px-6 py-4 flex items-center justify-between">
                    <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </a>

                    <button type="submit"
                        class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <i class="fas fa-check mr-2"></i>
                        Crear Permiso Aprobado
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const userSearchInput = document.getElementById('user_search');
    const userResults = document.getElementById('user_results');
    const userIdInput = document.getElementById('user_id');
    const userPreview = document.getElementById('userPreview');
    const departureInput = document.getElementById('departure_datetime');
    const returnInput = document.getElementById('return_datetime');
    const actualHoursSpan = document.getElementById('actualHours');

    // Lista de usuarios (pasada desde el servidor)
    const users = @json($users);

    // Búsqueda de usuarios
    let searchTimeout;
    userSearchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.toLowerCase().trim();

        if (query.length < 2) {
            userResults.classList.add('hidden');
            return;
        }

        searchTimeout = setTimeout(() => {
            const filtered = users.filter(user => {
                return user.name.toLowerCase().includes(query) ||
                       user.dni.includes(query) ||
                       (user.email && user.email.toLowerCase().includes(query));
            });

            if (filtered.length === 0) {
                userResults.innerHTML = '<div class="px-4 py-3 text-sm text-gray-500">No se encontraron resultados</div>';
                userResults.classList.remove('hidden');
                return;
            }

            userResults.innerHTML = filtered.map(user => `
                <div class="px-4 py-3 hover:bg-gray-100 cursor-pointer border-b border-gray-100 user-result-item"
                     data-user-id="${user.id}"
                     data-user-name="${user.name}"
                     data-user-dni="${user.dni}"
                     data-user-email="${user.email || 'N/A'}">
                    <div class="font-medium text-gray-900">${user.name}</div>
                    <div class="text-sm text-gray-600">DNI: ${user.dni} ${user.email ? '• ' + user.email : ''}</div>
                </div>
            `).join('');

            userResults.classList.remove('hidden');

            // Agregar event listeners a los resultados
            document.querySelectorAll('.user-result-item').forEach(item => {
                item.addEventListener('click', function() {
                    selectUser(
                        this.dataset.userId,
                        this.dataset.userName,
                        this.dataset.userDni,
                        this.dataset.userEmail
                    );
                });
            });
        }, 300);
    });

    // Función para seleccionar un usuario
    function selectUser(userId, name, dni, email) {
        userIdInput.value = userId;
        userSearchInput.value = `${name} - DNI: ${dni}`;
        userResults.classList.add('hidden');

        // Mostrar preview
        document.getElementById('previewName').textContent = name;
        document.getElementById('previewDni').textContent = dni;
        document.getElementById('previewEmail').textContent = 'Cargando...';
        document.getElementById('previewDepartment').textContent = 'Cargando...';

        userPreview.classList.remove('hidden');

        // Cargar datos adicionales via AJAX
        fetch(`/hr/permissions/user-info/${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('previewEmail').textContent = data.user.email;
                    document.getElementById('previewDepartment').textContent = data.user.department;
                }
            })
            .catch(err => {
                console.error('Error al cargar info del usuario:', err);
                document.getElementById('previewEmail').textContent = email;
                document.getElementById('previewDepartment').textContent = 'N/A';
            });
    }

    // Cerrar dropdown al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!userSearchInput.contains(e.target) && !userResults.contains(e.target)) {
            userResults.classList.add('hidden');
        }
    });

    // Calcular horas reales
    function calculateActualHours() {
        const departure = new Date(departureInput.value);
        const returnTime = new Date(returnInput.value);

        if (departure && returnTime && returnTime > departure) {
            const diffMs = returnTime - departure;
            const hours = (diffMs / 1000 / 60 / 60).toFixed(2);
            actualHoursSpan.textContent = hours;
        } else {
            actualHoursSpan.textContent = '0';
        }
    }

    departureInput.addEventListener('change', calculateActualHours);
    returnInput.addEventListener('change', calculateActualHours);

    // Validación al enviar
    document.getElementById('hrPermissionForm').addEventListener('submit', function(e) {
        const departure = new Date(departureInput.value);
        const returnTime = new Date(returnInput.value);

        if (returnTime <= departure) {
            e.preventDefault();
            alert('La hora de regreso debe ser posterior a la hora de salida');
            return false;
        }

        if (!confirm('¿Está seguro de crear este permiso directamente aprobado con tracking completo?')) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endpush
</x-app-layout>
