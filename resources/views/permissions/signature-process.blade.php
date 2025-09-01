<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Firma Digital de Solicitud') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Solicitud N° {{ $permission->request_number }}
                </p>
            </div>
            <div class="text-right">
                @if($permission->status === 'firmado')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Firmado Digitalmente
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        Pendiente de Firma
                    </span>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Mensajes de alerta --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Columna Principal: Proceso de Firma -->
                <div class="lg:col-span-2">
                    @if(!$hasSignature)
                        <!-- Paso 1: Instrucciones y Descarga -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6 text-gray-900">
                                <div class="flex items-center mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                                            <span class="text-white font-bold text-sm">1</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h2 class="text-lg font-semibold text-gray-900">Descargar Solicitud para Firmar</h2>
                                        <p class="text-gray-600">Descargue el PDF de su solicitud para firmarlo con ONPE</p>
                                    </div>
                                </div>

                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-blue-800">Instrucciones</h3>
                                            <div class="mt-2 text-sm text-blue-700">
                                                <ol class="list-decimal pl-5 space-y-1">
                                                    <li>Descargue el PDF de su solicitud haciendo clic en el botón inferior</li>
                                                    <li>Abra el Firmador Digital ONPE en su computadora</li>
                                                    <li>Firme el PDF descargado con su certificado digital</li>
                                                    <li>Guarde el PDF firmado en su computadora</li>
                                                    <li>Regrese aquí y suba el PDF firmado</li>
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center">
                                    <a href="{{ route('permissions.download-unsigned-pdf', $permission) }}" 
                                       class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Descargar PDF para Firmar
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Paso 2: Subir PDF Firmado -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900">
                                <div class="flex items-center mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                            <span class="text-gray-600 font-bold text-sm">2</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h2 class="text-lg font-semibold text-gray-900">Subir PDF Firmado</h2>
                                        <p class="text-gray-600">Suba el PDF después de firmarlo con ONPE</p>
                                    </div>
                                </div>

                                <form id="uploadSignedForm" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    
                                    <!-- Campo de archivo -->
                                    <div>
                                        <label for="signed_pdf" class="block text-sm font-medium text-gray-700 mb-2">
                                            PDF Firmado Digitalmente *
                                        </label>
                                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors duration-200">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="mt-4">
                                                <label for="signed_pdf" class="cursor-pointer">
                                                    <span class="mt-2 block text-sm font-medium text-gray-900">
                                                        Clic para seleccionar PDF firmado
                                                    </span>
                                                    <input type="file" id="signed_pdf" name="signed_pdf" accept=".pdf" required class="hidden">
                                                </label>
                                                <p class="mt-1 text-xs text-gray-500">Solo archivos PDF, máximo 10MB</p>
                                            </div>
                                        </div>
                                        <div id="file-info" class="hidden mt-2 p-3 bg-gray-50 rounded-lg">
                                            <div class="flex items-center">
                                                <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span id="file-name" class="ml-2 text-sm text-gray-900"></span>
                                                <span id="file-size" class="ml-2 text-xs text-gray-500"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Confirmación -->
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="confirm_signature" name="confirm_signature" type="checkbox" required 
                                                   class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="confirm_signature" class="text-gray-700">
                                                Confirmo que he firmado digitalmente este documento con mi certificado ONPE válido *
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Botón de envío -->
                                    <div class="pt-4">
                                        <button type="submit" id="uploadButton"
                                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                            Subir PDF Firmado
                                        </button>
                                    </div>
                                </form>

                                <!-- Indicador de progreso -->
                                <div id="uploadProgress" class="hidden mt-4 p-4 bg-blue-50 rounded-lg">
                                    <div class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="text-blue-700 font-medium">Procesando archivo firmado...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Solicitud ya firmada -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900">
                                <div class="text-center">
                                    <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <h2 class="mt-4 text-xl font-semibold text-gray-900">Solicitud Firmada Digitalmente</h2>
                                    <p class="mt-2 text-gray-600">Su solicitud ha sido firmada exitosamente</p>
                                    
                                    <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
                                        <a href="{{ route('permissions.download-signed-pdf', $permission) }}" 
                                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Descargar PDF Firmado
                                        </a>
                                        
                                        @if($permission->status === 'firmado')
                                            <form action="{{ route('permissions.submit-signed', $permission) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                                    </svg>
                                                    Enviar para Aprobación
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <button onclick="removeSignature()" 
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1-1H8a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Remover Firma
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Columna Lateral: Información de la Solicitud -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Resumen de Solicitud -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumen de Solicitud</h3>
                            
                            <div class="space-y-3 text-sm">
                                <div>
                                    <span class="text-gray-600">Tipo:</span>
                                    <p class="font-medium">{{ $permission->permissionType->name }}</p>
                                </div>
                                
                                <div>
                                    <span class="text-gray-600">Fecha:</span>
                                    <p class="font-medium">{{ $permission->start_datetime->format('d/m/Y H:i') }}</p>
                                </div>
                                
                                <div>
                                    <span class="text-gray-600">Duración:</span>
                                    <p class="font-medium">{{ $permission->requested_hours }} horas</p>
                                </div>
                                
                                <div>
                                    <span class="text-gray-600">Estado:</span>
                                    <p class="font-medium">{{ ucfirst($permission->status) }}</p>
                                </div>
                            </div>
                            
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <a href="{{ route('permissions.show', $permission) }}" 
                                   class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                                    Ver detalles completos →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Información sobre ONPE -->
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-amber-800">Requisitos Importantes</h3>
                                <div class="mt-2 text-sm text-amber-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        <li>Debe tener instalado el Firmador Digital ONPE</li>
                                        <li>Su certificado digital debe estar vigente</li>
                                        <li>El PDF firmado no debe exceder 10MB</li>
                                        <li>Una vez firmado, no podrá modificar la solicitud</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($hasSignature)
                        <!-- Información de la firma -->
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">Firma Verificada</h3>
                                    <div class="mt-2 text-sm text-green-700">
                                        <p>Su solicitud ha sido firmada digitalmente exitosamente.</p>
                                        <button onclick="verifySignature()" class="mt-2 text-green-600 hover:text-green-500 underline">
                                            Ver detalles de la firma
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('signed_pdf');
        const fileInfo = document.getElementById('file-info');
        const fileName = document.getElementById('file-name');
        const fileSize = document.getElementById('file-size');
        const uploadForm = document.getElementById('uploadSignedForm');
        const uploadButton = document.getElementById('uploadButton');
        const uploadProgress = document.getElementById('uploadProgress');

        // Manejar selección de archivo
        fileInput?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                fileName.textContent = file.name;
                fileSize.textContent = `(${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                fileInfo.classList.remove('hidden');
            } else {
                fileInfo.classList.add('hidden');
            }
        });

        // Manejar envío del formulario
        uploadForm?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!uploadForm.checkValidity()) {
                uploadForm.reportValidity();
                return;
            }

            const formData = new FormData(uploadForm);
            
            uploadButton.disabled = true;
            uploadButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Procesando...
            `;
            uploadProgress.classList.remove('hidden');

            fetch('{{ route("permissions.upload-signed-pdf", $permission) }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 2000);
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', error.message || 'Error al procesar el archivo');
                
                uploadButton.disabled = false;
                uploadButton.innerHTML = `
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    Subir PDF Firmado
                `;
                uploadProgress.classList.add('hidden');
            });
        });
    });

    function removeSignature() {
        if (confirm('¿Está seguro de que desea remover la firma digital? La solicitud volverá a estado borrador.')) {
            fetch('{{ route("permissions.remove-signature", $permission) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                showAlert('error', 'Error al remover la firma');
            });
        }
    }

    function verifySignature() {
        fetch('{{ route("permissions.verify-signature", $permission) }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const info = data.signature_info;
                alert(`Información de la Firma:
                
    Firmado por: ${info.signer} (DNI: ${info.signer_dni})
    Fecha de firma: ${new Date(info.signed_at).toLocaleString()}
    Verificación de integridad: ${info.integrity_check}
    Estado: ${info.is_valid ? 'VÁLIDA' : 'INVÁLIDA'}`);
            } else {
                alert('No se pudo verificar la firma: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error al verificar la firma');
        });
    }

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
        const iconPath = type === 'success' 
            ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
            : 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
        
        const alert = document.createElement('div');
        alert.className = `fixed top-4 right-4 max-w-sm p-4 border rounded-lg shadow-lg z-50 ${alertClass}`;
        alert.innerHTML = `
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" d="${iconPath}" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">${message}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button onclick="this.parentElement.parentElement.remove()" class="text-current opacity-70 hover:opacity-100">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(alert);
        
        setTimeout(() => {
            if (alert.parentElement) {
                alert.remove();
            }
        }, 5000);
    }
    </script>
</x-app-layout>