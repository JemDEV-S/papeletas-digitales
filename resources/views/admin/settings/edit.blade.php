<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Configuración del Sistema
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 rounded-md p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900">Seguimiento de Permisos</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Hora en la que el sistema registra automáticamente el regreso de los empleados
                        que salieron con permiso y aún no han marcado su llegada.
                    </p>

                    <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-6 space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="max-w-xs">
                            <label for="auto_return_time" class="block text-sm font-medium text-gray-700">
                                Hora de cierre (registro automático de regreso)
                            </label>
                            <input type="time" name="auto_return_time" id="auto_return_time"
                                   value="{{ old('auto_return_time', $autoReturnTime) }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 @error('auto_return_time') border-red-500 @enderror">
                            @error('auto_return_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">
                                Solo aplica de lunes a viernes. El cambio se aplica de inmediato, sin necesidad de reiniciar el servidor.
                            </p>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-medium">
                                Guardar Configuración
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
