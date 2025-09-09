<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Departamento: {{ $department->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.departments.show', $department) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Ver Detalles
                </a>
                <a href="{{ route('admin.departments.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.departments.update', $department) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Información Básica -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nombre del Departamento *</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $department->name) }}" required maxlength="255"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 @error('name') border-red-500 @enderror">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700">Código *</label>
                                <input type="text" name="code" id="code" value="{{ old('code', $department->code) }}" required maxlength="10"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 @error('code') border-red-500 @enderror">
                                @error('code')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">Máximo 10 caracteres. Ejemplo: TI, RRHH, VENTAS</p>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
                            <textarea name="description" id="description" rows="3" maxlength="500"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 @error('description') border-red-500 @enderror">{{ old('description', $department->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Máximo 500 caracteres</p>
                        </div>

                        <!-- Información Jerárquica -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="parent_department_id" class="block text-sm font-medium text-gray-700">Departamento Padre</label>
                                <select name="parent_department_id" id="parent_department_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 @error('parent_department_id') border-red-500 @enderror">
                                    <option value="">Departamento raíz (sin padre)</option>
                                    @foreach($parentDepartments as $parentDepartment)
                                        <option value="{{ $parentDepartment->id }}" {{ old('parent_department_id', $department->parent_department_id) == $parentDepartment->id ? 'selected' : '' }}>
                                            {{ $parentDepartment->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('parent_department_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">
                                    @if($department->parentDepartment)
                                        Actual: {{ $department->parentDepartment->name }}
                                    @else
                                        Actualmente es un departamento raíz
                                    @endif
                                </p>
                            </div>

                            <div>
                                <label for="manager_id" class="block text-sm font-medium text-gray-700">Gerente del Departamento</label>
                                <select name="manager_id" id="manager_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 @error('manager_id') border-red-500 @enderror">
                                    <option value="">Sin gerente asignado</option>
                                    @foreach($managers as $manager)
                                        <option value="{{ $manager->id }}" {{ old('manager_id', $department->manager_id) == $manager->id ? 'selected' : '' }}>
                                            {{ $manager->full_name }} - {{ ucfirst(str_replace('_', ' ', $manager->role->name ?? '')) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('manager_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">
                                    @if($department->manager)
                                        Actual: {{ $department->manager->full_name }}
                                    @else
                                        Sin gerente asignado actualmente
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div>
                            <label for="is_active" class="block text-sm font-medium text-gray-700">Estado</label>
                            <select name="is_active" id="is_active"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200">
                                <option value="1" {{ old('is_active', $department->is_active) ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('is_active', $department->is_active) == false ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>

                        <!-- Alertas de cambio -->
                        @if($department->childDepartments->count() > 0)
                            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800">
                                            Advertencia: Este departamento tiene subdepartamentos
                                        </h3>
                                        <p class="mt-2 text-sm text-yellow-700">
                                            Este departamento tiene {{ $department->childDepartments->count() }} subdepartamento(s). 
                                            Los cambios estructurales pueden afectar la jerarquía organizacional.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($department->users->count() > 0)
                            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-blue-800">
                                            Información: Departamento con empleados
                                        </h3>
                                        <p class="mt-2 text-sm text-blue-700">
                                            Este departamento tiene {{ $department->users->count() }} empleado(s) asignado(s).
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('admin.departments.show', $department) }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-md text-sm font-medium">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-medium">
                                Actualizar Departamento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>