<x-app-layout>
    <style>
    .permission-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .permission-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.6s;
    }
    
    .permission-card:hover::before {
        left: 100%;
    }
    
    .permission-radio:checked + .permission-card {
        border-color: #059669;
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.1), 0 2px 4px -1px rgba(5, 150, 105, 0.06);
        transform: translateY(-2px);
    }
    
    .permission-radio:checked + .permission-card .permission-icon {
        transform: scale(1.1);
        color: #059669;
    }

    .slide-in-left {
        animation: slideInLeft 0.6s ease-out forwards;
        opacity: 0;
        transform: translateX(-30px);
    }

    .slide-in-right {
        animation: slideInRight 0.6s ease-out forwards;
        opacity: 0;
        transform: translateX(30px);
    }

    .fade-in {
        animation: fadeIn 0.8s ease-out forwards;
        opacity: 0;
    }

    @keyframes slideInLeft {
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideInRight {
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes fadeIn {
        to {
            opacity: 1;
        }
    }

    .gradient-text {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .glassmorphism {
        background: rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.18);
    }
    </style>

    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
            </div>
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    Nueva Solicitud de Permiso
                </h2>
                <p class="text-gray-600 text-sm">Complete los datos para solicitar su permiso</p>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Contenedor principal con efecto glassmorphism -->
            <div class="glassmorphism rounded-3xl p-8 shadow-2xl">
                <form action="{{ route('permissions.store') }}" method="POST" enctype="multipart/form-data" 
                      class="space-y-8" id="permission-form">
                    @csrf

                    <!-- Header del formulario -->
                    <div class="text-center fade-in">
                        <div class="w-20 h-20 mx-auto bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mb-6 shadow-lg">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h1 class="text-4xl font-bold gradient-text mb-4">Solicitud de Permiso</h1>
                        <p class="text-gray-600 text-lg leading-relaxed max-w-2xl mx-auto">
                            Complete la información necesaria para su solicitud. Una vez enviada, será procesada por su jefe inmediato y recursos humanos.
                        </p>
                    </div>

                    <!-- Selector de tipo de permiso -->
                    <div class="slide-in-left">
                        <label class="block text-xl font-semibold gradient-text mb-6">
                            🏷️ Tipo de Permiso
                            <span class="text-red-500 ml-1">*</span>
                        </label>
                        
                        <!-- Tipos de permisos principales -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Permisos Principales</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                @foreach($permissionTypes as $type)
                                    @if(in_array(strtolower($type->name), ['por comisión de servicios', 'comisión de servicios', 'comision de servicios', 'por enfermedad', 'enfermedad', 'por asuntos particulares', 'asuntos particulares', 'otros']))
                                        <label class="relative cursor-pointer transform transition-all duration-300 hover:scale-105">
                                            <input type="radio" name="permission_type_id" value="{{ $type->id }}" 
                                                   class="sr-only permission-radio" 
                                                   {{ old('permission_type_id') == $type->id ? 'checked' : '' }}
                                                   data-max-times-month="{{ $type->max_times_per_month }}">
                                            <div class="permission-card p-6 rounded-xl border-2 border-gray-200 bg-white shadow-lg hover:shadow-xl">
                                                <div class="flex items-center justify-center mb-4">
                                                    <div class="permission-icon w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white text-2xl shadow-md transition-transform">
                                                        {{ $type->getIconEmoji() }}
                                                    </div>
                                                </div>
                                                <div class="text-center">
                                                    <div class="font-semibold text-gray-900 text-lg mb-2">{{ $type->name }}</div>
                                                    <div class="text-sm text-gray-600 mb-3">{{ $type->description }}</div>
                                                    @if($type->max_times_per_month)
                                                        <div class="text-xs bg-blue-100 text-blue-800 px-3 py-1 rounded-full font-medium">
                                                            Máximo {{ $type->max_times_per_month }} por mes
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <!-- Otros tipos de permisos en desplegable -->
                        @php
                            $otherTypes = $permissionTypes->filter(function($type) {
                                return !in_array(strtolower($type->name), ['por comisión de servicios', 'comisión de servicios', 'comision de servicios', 'por enfermedad', 'enfermedad', 'por asuntos particulares', 'asuntos particulares']);
                            });
                        @endphp
                        
                        @if($otherTypes->count() > 0)
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Otros Tipos de Permisos</h3>
                                <div class="relative">
                                    <select name="other_permission_type" id="other_permission_type" 
                                            class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-200 focus:border-blue-500 transition-all appearance-none">
                                        <option value="">Seleccione otro tipo de permiso...</option>
                                        @foreach($otherTypes as $type)
                                            <option value="{{ $type->id }}" 
                                                    data-description="{{ $type->description }}"
                                                    data-max-times="{{ $type->max_times_per_month }}"
                                                    {{ old('permission_type_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                
                                <!-- Información del tipo seleccionado -->
                                <div id="other-type-info" class="hidden mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                                    <div class="flex items-start space-x-3">
                                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div id="selected-description" class="text-sm text-gray-700 mb-2"></div>
                                            <div id="selected-max-times" class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full font-medium inline-block"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        @error('permission_type_id')
                            <p class="mt-2 text-red-600 text-sm font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Campo de razón/motivo -->
                    <div class="slide-in-right">
                        <label for="reason" class="block text-xl font-semibold gradient-text mb-4">
                            📝 Motivo del Permiso
                            <span class="text-red-500 ml-1">*</span>
                        </label>
                        <div class="relative">
                            <textarea name="reason" id="reason" rows="6"
                                class="w-full px-6 py-4 bg-white border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-200 focus:border-blue-500 transition-all resize-none shadow-sm"
                                placeholder="Describa detalladamente el motivo de su solicitud de permiso..."
                                maxlength="500"
                                required>{{ old('reason') }}</textarea>
                            <div class="absolute bottom-3 right-3 text-xs text-gray-500">
                                <span id="reason-count">0</span>/500 caracteres
                            </div>
                        </div>
                        @error('reason')
                            <p class="mt-2 text-red-600 text-sm font-medium">{{ $message }}</p>
                        @enderror
                        <div class="mt-3 text-sm text-blue-600 bg-blue-50 p-3 rounded-lg">
                            💡 <strong>Consejo:</strong> Proporcione detalles específicos y relevantes para facilitar la evaluación de su solicitud.
                        </div>
                    </div>

                    <!-- Sección de documentos -->
                    <div class="fade-in">
                        <label class="block text-xl font-semibold gradient-text mb-4">
                            📎 Documentos de Soporte
                            <span class="text-gray-500 text-sm font-normal">(Opcional - puede subir cuando lo requiera)</span>
                        </label>
                        
                        <!-- Información sobre cuando se requieren documentos -->
                        <div class="mb-6 p-4 bg-amber-50 rounded-lg border border-amber-200">
                            <div class="flex items-start space-x-3">
                                <div class="w-6 h-6 bg-amber-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="text-sm text-amber-800">
                                    <p class="font-medium mb-1">Información sobre documentos:</p>
                                    <ul class="list-disc list-inside space-y-1 text-xs">
                                        <li><strong>Por enfermedad:</strong> Certificado médico (opcional si es menos de 3 días)</li>
                                        <li><strong>Comisión de servicio:</strong> Puede requerir documentos justificativos</li>
                                        <li><strong>Otros permisos:</strong> Documentos según el tipo de solicitud</li>
                                    </ul>
                                    <p class="mt-2 text-xs font-medium">Puede subir documentos ahora o posteriormente cuando los tenga disponibles.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div id="documents-container" class="space-y-4">
                            <div class="document-upload-item p-6 bg-white border-2 border-dashed border-gray-300 rounded-xl hover:border-blue-400 transition-colors">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="w-16 h-16 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center mb-4 shadow-lg">
                                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                    </div>
                                    <div class="space-y-2">
                                        <input type="file" name="documents[]" class="hidden document-input" 
                                               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" multiple>
                                        <button type="button" onclick="this.previousElementSibling.click()" 
                                                class="px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 transition-all shadow-md hover:shadow-lg transform hover:-translate-y-1">
                                            📄 Seleccionar Documentos
                                        </button>
                                        <p class="text-sm text-gray-500">PDF, JPG, JPEG, PNG, DOC, DOCX (máx. 5MB c/u)</p>
                                        <p class="text-xs text-blue-600">💡 Puede enviar su solicitud sin documentos y subirlos después</p>
                                    </div>
                                </div>
                                <div class="document-preview mt-4"></div>
                            </div>
                            
                            <!-- Botón para agregar más archivos -->
                            <div class="text-center">
                                <button type="button" id="add-more-documents" class="hidden px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm font-medium">
                                    ➕ Agregar más documentos
                                </button>
                            </div>
                        </div>
                        @error('documents')
                            <p class="mt-2 text-red-600 text-sm font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Botones de acción -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-8 border-t border-gray-200">
                        <button type="submit" 
                                class="flex-1 px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white text-lg font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-4 focus:ring-blue-300 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            <span class="flex items-center justify-center">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Crear Solicitud
                            </span>
                        </button>
                        <a href="{{ route('permissions.index') }}" 
                           class="flex-1 px-8 py-4 bg-gray-100 text-gray-700 text-lg font-semibold rounded-xl hover:bg-gray-200 focus:outline-none focus:ring-4 focus:ring-gray-300 transition-all text-center shadow-md hover:shadow-lg">
                            <span class="flex items-center justify-center">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animaciones secuenciales
        const elements = document.querySelectorAll('.slide-in-left, .slide-in-right, .fade-in');
        elements.forEach((el, index) => {
            el.style.animationDelay = `${index * 0.2}s`;
        });

        // Contador de caracteres para el motivo
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
        updateCharCount(); // Inicializar contador

        // Manejo de archivos
        const documentInputs = document.querySelectorAll('.document-input');
        documentInputs.forEach(input => {
            input.addEventListener('change', function() {
                const preview = this.closest('.document-upload-item').querySelector('.document-preview');
                preview.innerHTML = '';
                
                Array.from(this.files).forEach(file => {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg mt-2';
                    fileItem.innerHTML = `
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">${file.name}</div>
                                <div class="text-xs text-gray-500">${(file.size / 1024 / 1024).toFixed(2)} MB</div>
                            </div>
                        </div>
                        <button type="button" class="text-red-500 hover:text-red-700 p-1" onclick="this.parentElement.remove()">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    `;
                    preview.appendChild(fileItem);
                });
            });
        });

        // Manejo del desplegable de otros tipos de permisos
        const otherPermissionSelect = document.getElementById('other_permission_type');
        const otherTypeInfo = document.getElementById('other-type-info');
        const selectedDescription = document.getElementById('selected-description');
        const selectedMaxTimes = document.getElementById('selected-max-times');
        
        if (otherPermissionSelect) {
            otherPermissionSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                
                if (this.value) {
                    // Deseleccionar radios principales
                    document.querySelectorAll('input[name="permission_type_id"]').forEach(radio => {
                        radio.checked = false;
                    });
                    
                    // Crear un radio button oculto para el valor seleccionado
                    let hiddenRadio = document.getElementById('hidden-other-type');
                    if (!hiddenRadio) {
                        hiddenRadio = document.createElement('input');
                        hiddenRadio.type = 'radio';
                        hiddenRadio.name = 'permission_type_id';
                        hiddenRadio.id = 'hidden-other-type';
                        hiddenRadio.style.display = 'none';
                        this.parentNode.appendChild(hiddenRadio);
                    }
                    hiddenRadio.value = this.value;
                    hiddenRadio.checked = true;
                    
                    // Mostrar información del tipo seleccionado
                    const description = selectedOption.getAttribute('data-description');
                    const maxTimes = selectedOption.getAttribute('data-max-times');
                    
                    selectedDescription.textContent = description || 'No hay descripción disponible';
                    if (maxTimes) {
                        selectedMaxTimes.textContent = `Máximo ${maxTimes} por mes`;
                        selectedMaxTimes.style.display = 'inline-block';
                    } else {
                        selectedMaxTimes.style.display = 'none';
                    }
                    
                    otherTypeInfo.classList.remove('hidden');
                } else {
                    otherTypeInfo.classList.add('hidden');
                    const hiddenRadio = document.getElementById('hidden-other-type');
                    if (hiddenRadio) {
                        hiddenRadio.checked = false;
                    }
                }
            });
        }
        
        // Al seleccionar un radio button principal, limpiar el desplegable
        document.querySelectorAll('input[name="permission_type_id"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked && otherPermissionSelect) {
                    otherPermissionSelect.value = '';
                    otherTypeInfo.classList.add('hidden');
                }
            });
        });

        // Validación del formulario
        document.getElementById('permission-form').addEventListener('submit', function(e) {
            const permissionType = document.querySelector('input[name="permission_type_id"]:checked');
            const otherPermissionValue = otherPermissionSelect ? otherPermissionSelect.value : '';
            const reason = document.getElementById('reason').value.trim();
            
            if (!permissionType && !otherPermissionValue) {
                e.preventDefault();
                alert('Por favor seleccione un tipo de permiso.');
                return;
            }
            
            if (reason.length < 10) {
                e.preventDefault();
                alert('El motivo debe tener al menos 10 caracteres.');
                return;
            }
        });
    });
    </script>
</x-app-layout>