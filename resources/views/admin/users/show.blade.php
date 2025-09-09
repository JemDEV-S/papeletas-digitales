<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalles del Usuario: {{ $user->full_name }}
            </h2>
            <div class="flex space-x-2">
                @if($user->is_active)
                    <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-md text-sm font-medium" 
                                onclick="return confirm('¿Está seguro de restablecer la contraseña de este usuario?')">
                            Resetear Contraseña
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.users.edit', $user) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Editar
                </a>
                <a href="{{ route('admin.users.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
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

            <!-- Información Personal -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Información Personal</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Nombres</label>
                            <p class="text-sm text-gray-900">{{ $user->first_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Apellidos</label>
                            <p class="text-sm text-gray-900">{{ $user->last_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">DNI</label>
                            <p class="text-sm text-gray-900">{{ $user->dni }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email</label>
                            <p class="text-sm text-gray-900">{{ $user->email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Estado</label>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Fecha de Registro</label>
                            <p class="text-sm text-gray-900">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información Organizacional -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Información Organizacional</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Rol</label>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $user->role->name === 'admin' ? 'bg-red-100 text-red-800' : 
                                   ($user->role->name === 'jefe_rrhh' ? 'bg-purple-100 text-purple-800' : 
                                   ($user->role->name === 'jefe_inmediato' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst(str_replace('_', ' ', $user->role->name ?? 'Sin rol')) }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Departamento</label>
                            <p class="text-sm text-gray-900">{{ $user->department->name ?? 'Sin departamento' }}</p>
                        </div>
                        @if($user->department)
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Jerarquía del Departamento</label>
                                <p class="text-sm text-gray-900">{{ $user->department->getHierarchyPath() }}</p>
                            </div>
                        @endif
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Supervisor Inmediato</label>
                            @if($user->immediateSupervisor)
                                <div class="flex items-center space-x-2">
                                    <p class="text-sm text-gray-900">{{ $user->immediateSupervisor->full_name }}</p>
                                    <a href="{{ route('admin.users.show', $user->immediateSupervisor) }}" class="text-blue-600 hover:text-blue-900 text-xs">(Ver perfil)</a>
                                </div>
                                <p class="text-xs text-gray-500">{{ $user->immediateSupervisor->role->name ?? 'Sin rol' }} - {{ $user->immediateSupervisor->email }}</p>
                            @else
                                <p class="text-sm text-gray-500">Sin supervisor asignado</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subordinados -->
            @if($user->subordinates->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Subordinados Directos ({{ $user->subordinates->count() }})
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($user->subordinates as $subordinate)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $subordinate->full_name }}</p>
                                            <p class="text-xs text-gray-500">{{ $subordinate->email }}</p>
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $subordinate->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $subordinate->is_active ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </div>
                                        <a href="{{ route('admin.users.show', $subordinate) }}" class="text-blue-600 hover:text-blue-900">
                                            Ver
                                        </a>
                                    </div>
                                    <div class="mt-2">
                                        <p class="text-xs text-gray-500">
                                            {{ ucfirst(str_replace('_', ' ', $subordinate->role->name ?? 'Sin rol')) }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $subordinate->department->name ?? 'Sin departamento' }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Acciones -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Acciones</h3>
                    <div class="flex flex-wrap gap-3">
                        @if($user->is_active)
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium" 
                                        onclick="return confirm('¿Está seguro de desactivar este usuario? Esta acción afectará su capacidad de acceder al sistema.')">
                                    Desactivar Usuario
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.users.activate', $user) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                    Activar Usuario
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>