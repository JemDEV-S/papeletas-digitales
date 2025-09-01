<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                        Editar Solicitud
                    </h2>
                    <p class="text-gray-600 text-sm">
                        Solicitud #{{ $permission->request_number }} • 
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            {{ $permission->getStatusLabel() }}
                        </span>
                    </p>
                </div>
            </div>
            <a href="{{ route('permissions.show', $permission) }}" 
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Errores -->
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Errores en el formulario</h3>
                            <ul class="mt-2 text-sm text-red-700 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>• {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Aviso de edición -->
            <div class="mb-6 bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-lg shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-amber-800">Información importante</h3>
                        <p class="mt-2 text-sm text-amber-700">
                            Solo puede editar solicitudes en estado <strong>Borrador</strong>. Una vez enviada para aprobación, no podrá realizar cambios.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Formulario -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <form action="{{ route('permissions.update', $permission) }}" method="POST" class="space-y-8">
                    @csrf
                    @method('PUT')

                    <!-- Tipo de permiso -->
                    <div>
                        <label class="block text-lg font-semibold text-gray-900 mb-4">
                            🏷️ Tipo de Permiso
                            <span class="text-red-500 ml-1">*</span>
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($permissionTypes as $type)
                                <label class="relative cursor-pointer transform transition-all duration-200 hover:scale-105">
                                    <input type="radio" name="permission_type_id" value="{{ $type->id }}" 
                                           class="sr-only permission-radio" 
                                           {{ old('permission_type_id', $permission->permission_type_id) == $type->id ? 'checked' : '' }}>
                                    <div class="permission-card p-6 rounded-lg border-2 border-gray-200 bg-gray-50 hover:bg-white hover:shadow-md transition-all">
                                        <div class="flex items-center justify-center mb-3">
                                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white text-xl shadow-sm">
                                                {{ $type->getIconEmoji() }}
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <div class="font-semibold text-gray-900 mb-1">{{ $type->name }}</div>
                                            <div class="text-sm text-gray-600 mb-2">{{ $type->description }}</div>
                                            @if($type->max_times_per_month)
                                                <div class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full font-medium">
                                                    Máximo {{ $type->max_times_per_month }} por mes
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('permission_type_id')
                            <p class="mt-2 text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Motivo -->
                    <div>
                        <label for="reason" class="block text-lg font-semibold text-gray-900 mb-4">
                            📝 Motivo del Permiso
                            <span class="text-red-500 ml-1">*</span>
                        </label>
                        <div class="relative">
                            <textarea name="reason" id="reason" rows="6"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
                                placeholder="Describa detalladamente el motivo de su solicitud de permiso..."
                                maxlength="500"
                                required>{{ old('reason', $permission->reason) }}</textarea>
                            <div class="absolute bottom-2 right-2 text-xs text-gray-500">
                                <span id="reason-count">{{ strlen($permission->reason ?? '') }}</span>/500 caracteres
                            </div>
                        </div>
                        @error('reason')
                            <p class="mt-2 text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Documentos actuales -->
                    @if($permission->documents->count() > 0)
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">📎 Documentos Actuales</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($permission->documents as $document)
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg border">
                                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-3 flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $document->original_name }}</p>
                                            <p class="text-xs text-gray-500">{{ number_format($document->file_size / 1024, 1) }} KB</p>
                                        </div>
                                        <a href="{{ Storage::url($document->file_path) }}" 
                                           target="_blank"
                                           class="ml-3 text-blue-600 hover:text-blue-800 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Botones -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200">
                        <button type="submit" 
                                class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                            <span class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Actualizar Solicitud
                            </span>
                        </button>
                        <a href="{{ route('permissions.show', $permission) }}" 
                           class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all text-center shadow-md hover:shadow-lg">
                            <span class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancelar
                            </span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
    .permission-radio:checked + .permission-card {
        border-color: #3b82f6;
        background-color: #eff6ff;
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.1), 0 2px 4px -1px rgba(59, 130, 246, 0.06);
        transform: translateY(-2px);
    }
    
    .permission-radio:checked + .permission-card .w-12 {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        transform: scale(1.1);
    }
    
    .permission-card {
        transition: all 0.2s ease-in-out;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Contador de caracteres
        const reasonTextarea = document.getElementById('reason');
        const reasonCount = document.getElementById('reason-count');
        
        function updateCharCount() {
            const count = reasonTextarea.value.length;
            reasonCount.textContent = count;
            
            if (count > 450) {
                reasonCount.className = 'text-red-500 font-semibold';
            } else if (count > 400) {
                reasonCount.className = 'text-yellow-500 font-semibold';
            } else {
                reasonCount.className = 'text-gray-500';
            }
        }
        
        reasonTextarea.addEventListener('input', updateCharCount);
        updateCharCount();
    });
    </script>
</x-app-layout>