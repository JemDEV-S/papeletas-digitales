<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalles del Departamento: {{ $department->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.departments.edit', $department) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Editar
                </a>
                <a href="{{ route('admin.departments.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Información Básica -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Información Básica</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Nombre</label>
                            <p class="text-sm text-gray-900 font-semibold">{{ $department->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Código</label>
                            <span class="inline-flex px-2 py-1 text-xs font-mono bg-gray-100 text-gray-800 rounded">
                                {{ $department->code }}
                            </span>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500">Descripción</label>
                            <p class="text-sm text-gray-900">{{ $department->description ?: 'Sin descripción' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Estado</label>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $department->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $department->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Fecha de Creación</label>
                            <p class="text-sm text-gray-900">{{ $department->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información Jerárquica -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Estructura Jerárquica</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Ruta Jerárquica Completa</label>
                            <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded border">{{ $department->getHierarchyPath() }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Departamento Padre</label>
                            @if($department->parentDepartment)
                                <div class="flex items-center space-x-2">
                                    <p class="text-sm text-gray-900">{{ $department->parentDepartment->name }}</p>
                                    <a href="{{ route('admin.departments.show', $department->parentDepartment) }}" class="text-blue-600 hover:text-blue-900 text-xs">(Ver detalles)</a>
                                </div>
                            @else
                                <p class="text-sm text-gray-500">Departamento raíz (sin padre)</p>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Gerente del Departamento</label>
                            @if($department->manager)
                                <div class="flex items-center space-x-2">
                                    <p class="text-sm text-gray-900">{{ $department->manager->full_name }}</p>
                                    <a href="{{ route('admin.users.show', $department->manager) }}" class="text-blue-600 hover:text-blue-900 text-xs">(Ver perfil)</a>
                                </div>
                                <p class="text-xs text-gray-500">{{ $department->manager->email }} - {{ ucfirst(str_replace('_', ' ', $department->manager->role->name ?? 'Sin rol')) }}</p>
                            @else
                                <p class="text-sm text-gray-500">Sin gerente asignado</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subdepartamentos -->
            @if($department->childDepartments->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Subdepartamentos ({{ $department->childDepartments->count() }})
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($department->childDepartments as $childDepartment)
                                <div class="border border-gray-200 rounded-lg p-4 {{ $childDepartment->is_active ? '' : 'bg-gray-50 border-dashed' }}">
                                    <div class="flex items-center justify-between mb-2">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $childDepartment->name }}</p>
                                            <span class="inline-flex px-2 py-1 text-xs font-mono bg-gray-100 text-gray-800 rounded">
                                                {{ $childDepartment->code }}
                                            </span>
                                        </div>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $childDepartment->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $childDepartment->is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 mb-2">
                                        <p>Empleados: {{ $childDepartment->users->count() }}</p>
                                        @if($childDepartment->manager)
                                            <p>Gerente: {{ $childDepartment->manager->full_name }}</p>
                                        @endif
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.departments.show', $childDepartment) }}" class="text-blue-600 hover:text-blue-900 text-xs">
                                            Ver
                                        </a>
                                        <a href="{{ route('admin.departments.edit', $childDepartment) }}" class="text-green-600 hover:text-green-900 text-xs">
                                            Editar
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Empleados del Departamento -->
            @if($department->users->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Empleados del Departamento ({{ $department->users->count() }})
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Empleado</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Rol</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($department->users->sortBy('role.name') as $user)
                                        <tr class="{{ $user->is_active ? '' : 'bg-gray-50' }}">
                                            <td class="px-4 py-2">
                                                <div class="flex items-center">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">{{ $user->full_name }}</div>
                                                        <div class="text-sm text-gray-500">DNI: {{ $user->dni }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $user->email }}</td>
                                            <td class="px-4 py-2">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                    {{ $user->role->name === 'admin' ? 'bg-red-100 text-red-800' : 
                                                       ($user->role->name === 'jefe_rrhh' ? 'bg-purple-100 text-purple-800' : 
                                                       ($user->role->name === 'jefe_inmediato' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) }}">
                                                    {{ ucfirst(str_replace('_', ' ', $user->role->name ?? 'Sin rol')) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-sm font-medium space-x-2">
                                                <a href="{{ route('admin.users.show', $user) }}" class="text-indigo-600 hover:text-indigo-900">Ver</a>
                                                <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-900">Editar</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Acciones -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Acciones</h3>
                    <div class="flex flex-wrap gap-3">
                        @if($department->is_active)
                            <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium" 
                                        onclick="return confirm('¿Está seguro de desactivar este departamento? Esta acción puede afectar la estructura organizacional.')">
                                    Desactivar Departamento
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.departments.activate', $department) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                    Activar Departamento
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>