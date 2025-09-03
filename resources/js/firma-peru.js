/**
 * FIRMA PERÚ Integration for Permission Requests
 * Handles digital signature workflow with 3 stages
 */

class FirmaPeruIntegration {
    constructor() {
        this.port = 48596; // Puerto recomendado por FIRMA PERÚ
        this.jsUrl = 'https://apps.firmaperu.gob.pe/web/clienteweb/firmaperu.min.js';
        this.currentPermissionId = null;
        this.currentStage = null;
        this.isProcessing = false;
        this.scriptLoaded = false; // Inicializar explícitamente

        this.routes = window.firmaPeruRoutes || {};
        
        console.log('🚀 Inicializando FirmaPeruIntegration...');
        this.init();
    }
    /**
 * Obtener URL de ruta con parámetros
 */
    route(routeName, params = {}) {
        let url = this.routes[routeName];
        
        if (!url) {
            console.error(`❌ Ruta '${routeName}' no encontrada`);
            return null;
        }

        // Reemplazar parámetros en la URL
        Object.keys(params).forEach(key => {
            url = url.replace(`{${key}}`, params[key]);
        });

        return url;
    }

    /**
     * Inicializar la integración
     */
    init() {
        this.loadFirmaPeruScript();
        this.setupEventListeners();
        this.addComponentDiv();
        
        // Verificar que jqFirmaPeru esté disponible
        this.ensureJQueryConfiguration();
    }

    /**
     * Cargar el script de FIRMA PERÚ
     */
    loadFirmaPeruScript() {
        if (!document.getElementById('firma-peru-script')) {
            console.log('🔄 Cargando script de FIRMA PERÚ...');
            
            const script = document.createElement('script');
            script.id = 'firma-peru-script';
            script.src = this.jsUrl;
            
            script.onload = () => {
                console.log('📦 FIRMA PERÚ script cargado desde servidor');
                
                // Verificar que las funciones estén disponibles después de cargar
                setTimeout(() => {
                    this.verifyScriptFunctions();
                }, 100);
            };
            
            script.onerror = () => {
                console.error('❌ Error al cargar script de FIRMA PERÚ');
                this.showError('Error al cargar el componente de firma digital');
            };
            
            document.head.appendChild(script);
        } else {
            console.log('📦 Script FIRMA PERÚ ya existe, verificando funciones...');
            this.verifyScriptFunctions();
        }
    }

    /**
     * Verificar que las funciones del script estén disponibles
     */
    verifyScriptFunctions(retryCount = 0, maxRetries = 20) {
        const requiredFunctions = ['startSignature'];
        let allAvailable = true;
        
        console.log(`🔍 Verificando funciones de FIRMA PERÚ (intento ${retryCount + 1}/${maxRetries})`);
        
        for (const funcName of requiredFunctions) {
            if (typeof window[funcName] !== 'function') {
                console.warn(`⚠️ Función ${funcName} no disponible aún`);
                allAvailable = false;
            } else {
                console.log(`✅ Función ${funcName} disponible`);
            }
        }
        
        // Verificar también que jQuery esté disponible para FIRMA PERÚ
        if (typeof window.jqFirmaPeru === 'undefined') {
            console.warn('⚠️ jqFirmaPeru no disponible aún');
            allAvailable = false;
        }
        
        if (allAvailable) {
            console.log('✅ Todas las funciones de FIRMA PERÚ están disponibles');
            this.scriptLoaded = true;
            return;
        }
        
        if (retryCount < maxRetries) {
            console.log('⏳ Esperando funciones de FIRMA PERÚ...');
            
            // Aumentar el tiempo de espera gradualmente
            const delay = Math.min(1000, 200 + (retryCount * 100));
            setTimeout(() => {
                this.verifyScriptFunctions(retryCount + 1, maxRetries);
            }, delay);
        } else {
            console.error('❌ Timeout: No se pudieron cargar las funciones de FIRMA PERÚ después de múltiples intentos');
            console.log('🔍 Estado final:', {
                startSignature: typeof window.startSignature,
                jqFirmaPeru: typeof window.jqFirmaPeru,
                jQuery: typeof window.jQuery,
                windowKeys: Object.keys(window).filter(key => key.toLowerCase().includes('firma'))
            });
        }
    }

    /**
     * Agregar div requerido por FIRMA PERÚ
     */
    addComponentDiv() {
        if (!document.getElementById('addComponent')) {
            const div = document.createElement('div');
            div.id = 'addComponent';
            div.style.display = 'none';
            document.body.appendChild(div);
        }
    }

    /**
     * Asegurar configuración correcta de jQuery para FIRMA PERÚ
     */
    ensureJQueryConfiguration() {
        if (typeof window.jqFirmaPeru === 'undefined') {
            console.error('❌ jqFirmaPeru no está definido. Verificando jQuery...');
            
            // Intentar usar jQuery si está disponible
            if (typeof window.jQuery !== 'undefined') {
                window.jqFirmaPeru = window.jQuery.noConflict(true);
                console.log('✅ jqFirmaPeru configurado usando jQuery existente');
            } else if (typeof window.$ !== 'undefined') {
                window.jqFirmaPeru = window.$;
                console.log('✅ jqFirmaPeru configurado usando $');
            } else {
                console.error('❌ jQuery no está disponible. FIRMA PERÚ no funcionará.');
                this.showError('jQuery no está disponible. Por favor, recarga la página.');
                return false;
            }
        } else {
            console.log('✅ jqFirmaPeru ya está configurado');
        }
        return true;
    }

    /**
     * Esperar a que el componente FIRMA PERÚ esté disponible
     */
    async waitForFirmaPeruComponent(maxRetries = 10, delayMs = 500) {
        console.log('🔍 Verificando disponibilidad del componente FIRMA PERÚ...');
        
        for (let i = 0; i < maxRetries; i++) {
            // Verificar si el script está cargado y las funciones están disponibles
            if (this.scriptLoaded && 
                typeof window.startSignature === 'function' &&
                typeof window.jqFirmaPeru !== 'undefined') {
                
                console.log('✅ Componente FIRMA PERÚ disponible');
                return true;
            }
            
            console.log(`⏳ Intento ${i + 1}/${maxRetries} - Esperando componente...`);
            
            // Esperar antes del siguiente intento
            await new Promise(resolve => setTimeout(resolve, delayMs));
        }
        
        console.error('❌ Timeout esperando componente FIRMA PERÚ');
        console.log('Estado del componente:', {
            scriptLoaded: this.scriptLoaded,
            startSignatureExists: typeof window.startSignature,
            jqFirmaPeruExists: typeof window.jqFirmaPeru,
            windowKeys: Object.keys(window).filter(key => key.includes('firma') || key.includes('Firma'))
        });
        
        return false;
    }

    /**
     * Configurar event listeners
     */
    setupEventListeners() {
        // Event listeners para botones de firma
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-sign-employee')) {
                this.initiateEmployeeSignature(e.target.dataset.permissionId);
            } else if (e.target.classList.contains('btn-sign-level1')) {
                this.initiateLevel1Signature(e.target.dataset.permissionId);
            } else if (e.target.classList.contains('btn-sign-level2')) {
                this.initiateLevel2Signature(e.target.dataset.permissionId);
            }
        });

        // Event listener para verificar firmas
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-verify-signatures')) {
                this.verifySignatures(e.target.dataset.permissionId);
            }
        });

        // Actualizar estado de firmas periódicamente
        setInterval(() => {
            this.updateSignatureStatus();
        }, 30000); // Cada 30 segundos
    }

    /**
     * Iniciar proceso de firma para empleado (Stage 1)
     */
    async initiateEmployeeSignature(permissionId) {
        if (this.isProcessing) {
            this.showWarning('Ya hay un proceso de firma en curso');
            return;
        }

        this.isProcessing = true;
        this.currentPermissionId = permissionId;
        this.currentStage = 1;

        try {
            this.showLoading('Iniciando proceso de firma de empleado...');

            const url = this.route('initiateEmployee', { permission: permissionId });
            if (!url) {
                throw new Error('URL de ruta no encontrada para iniciar firma de empleado');
            }
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Error al iniciar proceso de firma');
            }

            await this.startSignatureProcess(data);

        } catch (error) {
            this.handleSignatureError(error);
        } finally {
            this.isProcessing = false;
            this.hideLoading();
        }
    }

    /**
     * Iniciar proceso de firma para jefe inmediato (Stage 2)
     */
    async initiateLevel1Signature(permissionId) {
        if (this.isProcessing) {
            this.showWarning('Ya hay un proceso de firma en curso');
            return;
        }

        this.isProcessing = true;
        this.currentPermissionId = permissionId;
        this.currentStage = 2;

        try {
            this.showLoading('Iniciando proceso de aprobación y firma de jefe inmediato...');

            const url = this.route('initiateLevel1', { permission: permissionId });
            if (!url) {
                throw new Error('URL de ruta no encontrada para iniciar firma de jefe inmediato');
            }
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Error al iniciar proceso de firma');
            }

            await this.startSignatureProcess(data);

        } catch (error) {
            this.handleSignatureError(error);
        } finally {
            this.isProcessing = false;
            this.hideLoading();
        }
    }

    /**
     * Iniciar proceso de firma para RRHH (Stage 3)
     */
    async initiateLevel2Signature(permissionId) {
        if (this.isProcessing) {
            this.showWarning('Ya hay un proceso de firma en curso');
            return;
        }

        this.isProcessing = true;
        this.currentPermissionId = permissionId;
        this.currentStage = 3;

        try {
            this.showLoading('Iniciando proceso de aprobación final y firma de RRHH...');

            const url = this.route('initiateLevel2', { permission: permissionId });
            if (!url) {
                throw new Error('URL de ruta no encontrada para iniciar firma de RRHH');
            }
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Error al iniciar proceso de firma');
            }

            await this.startSignatureProcess(data);

        } catch (error) {
            this.handleSignatureError(error);
        } finally {
            this.isProcessing = false;
            this.hideLoading();
        }
    }

    /**
     * Iniciar proceso de firma con parámetros de FIRMA PERÚ
     */
    async startSignatureProcess(data) {
        // Intentar esperar a que el componente esté disponible
        const isAvailable = await this.waitForFirmaPeruComponent();
        
        if (!isAvailable) {
            // Mostrar información de diagnóstico
            console.error('🚫 Diagnóstico del componente FIRMA PERÚ:', {
                scriptElement: !!document.getElementById('firma-peru-script'),
                scriptLoaded: this.scriptLoaded,
                startSignature: typeof window.startSignature,
                jqFirmaPeru: typeof window.jqFirmaPeru,
                jQuery: typeof window.jQuery,
                scriptSrc: this.jsUrl
            });
            
            throw new Error('El componente de FIRMA PERÚ no está disponible. Verifique que el servicio esté funcionando y no haya bloqueadores de script.');
        }

        // Guardar el param_token para uso en callbacks
        this.currentParamToken = data.param_token;

        console.log('Iniciando proceso de firma con datos:', {
            param_token: data.param_token,
            js_url: data.js_url,
            port: data.port
        });

        // Configurar funciones callback requeridas por FIRMA PERÚ
        window.signatureInit = () => {
            this.onSignatureInit();
        };

        window.signatureOk = () => {
            this.onSignatureOk();
        };

        window.signatureCancel = () => {
            this.onSignatureCancel();
        };

        // Guardar referencia de this para usar en callbacks
        const self = this;
        
        // Función para enviar parámetros (llamada por firmaperu.min.js)
        window.sendParam = async () => {
            try {
                console.log('sendParam llamado con param_token:', self.currentParamToken);
                
                const paramUrl = self.route('param');
                if (!paramUrl) {
                    throw new Error('URL de parámetros no encontrada');
                }
                
                console.log('URL para sendParam:', paramUrl);
                
                const formData = new FormData();
                formData.append('param_token', self.currentParamToken);
                
                const response = await fetch(paramUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: formData
                });

                // Si la respuesta es exitosa, debería ser los parámetros Base64 directamente
                if (response.ok) {
                    const paramData = await response.text();
                    console.log('Parámetros recibidos:', paramData.substring(0, 100) + '...');
                    return paramData;
                } else {
                    // Si hay error, intentar leer como JSON
                    try {
                        const errorData = await response.json();
                        throw new Error(errorData.message || 'Error al obtener parámetros');
                    } catch (e) {
                        throw new Error('Error de comunicación con el servidor');
                    }
                }
            } catch (error) {
                console.error('Error en sendParam:', error);
                throw error;
            }
        };

        // Iniciar el proceso de firma
        const fullParamUrl = window.location.origin + '/api/firma-peru/param';
        const param = {
            'param_url': fullParamUrl,
            'param_token': self.currentParamToken,
            'document_extension': 'pdf'
        };

        console.log('Iniciando startSignature con parámetros:', param);
        window.startSignature(this.port, btoa(JSON.stringify(param)));
    }

    /**
     * Callback: Proceso de firma iniciado
     */
    onSignatureInit() {
        console.log('Proceso de firma iniciado');
        this.showInfo('Proceso de firma iniciado. Por favor, siga las instrucciones en la ventana de FIRMA PERÚ.');
        
        // Actualizar UI para mostrar que está en proceso
        this.updateButtonState('processing');
    }

    /**
     * Callback: Firma completada exitosamente
     */
    onSignatureOk() {
        console.log('Firma completada exitosamente');
        this.showSuccess('¡Documento firmado exitosamente!');
        
        // Actualizar estado de la página
        this.refreshSignatureStatus();
        
        // Actualizar UI
        this.updateButtonState('completed');
        
        // Llamar callback personalizado si existe
        if (typeof this.onSignatureComplete === 'function') {
            console.log('🔄 Ejecutando callback onSignatureComplete...');
            this.onSignatureComplete();
        } else {
            // Comportamiento por defecto: recargar la página
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    }

    /**
     * Callback: Proceso de firma cancelado
     */
    onSignatureCancel() {
        console.log('Proceso de firma cancelado');
        this.showWarning('Proceso de firma cancelado por el usuario');
        
        // Restaurar estado de botones
        this.updateButtonState('ready');
    }

    /**
     * Actualizar estado de botones según la fase
     */
    updateButtonState(state) {
        const permissionId = this.currentPermissionId;
        const stage = this.currentStage;
        
        if (!permissionId || !stage) return;

        const buttonSelector = stage === 1 ? '.btn-sign-employee' : 
                              stage === 2 ? '.btn-sign-level1' : 
                              '.btn-sign-level2';
        
        const button = document.querySelector(`${buttonSelector}[data-permission-id="${permissionId}"]`);
        
        if (button) {
            switch (state) {
                case 'processing':
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Firmando...';
                    break;
                case 'completed':
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-check"></i> Firmado';
                    button.classList.add('bg-green-500');
                    break;
                case 'ready':
                    button.disabled = false;
                    button.innerHTML = this.getOriginalButtonText(stage);
                    break;
            }
        }
    }

    /**
     * Obtener texto original del botón
     */
    getOriginalButtonText(stage) {
        switch (stage) {
            case 1:
                return '<i class="fas fa-pen"></i> Firmar como Empleado';
            case 2:
                return '<i class="fas fa-check-circle"></i> Aprobar y Firmar';
            case 3:
                return '<i class="fas fa-stamp"></i> Aprobación Final RRHH';
            default:
                return 'Firmar';
        }
    }

    /**
     * Verificar integridad de todas las firmas
     */
    async verifySignatures(permissionId) {
        try {
            this.showLoading('Verificando integridad de firmas...');

            const url = this.route('verifySignatures', { permission: permissionId });
            if (!url) {
                throw new Error('URL de ruta no encontrada para verificar firmas');
            }
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            const data = await response.json();

            if (data.success) {
                this.showVerificationResults(data);
            } else {
                throw new Error(data.message || 'Error al verificar firmas');
            }

        } catch (error) {
            this.showError('Error al verificar firmas: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Mostrar resultados de verificación
     */
    showVerificationResults(data) {
        const modal = this.createVerificationModal(data);
        document.body.appendChild(modal);
        
        // Mostrar modal
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
    }

    /**
     * Crear modal de resultados de verificación
     */
    createVerificationModal(data) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 signature-verification-modal';
        
        const allValid = data.all_signatures_valid;
        const statusColor = allValid ? 'text-green-600' : 'text-red-600';
        const statusIcon = allValid ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
        const statusText = allValid ? 'Todas las firmas son válidas' : 'Se encontraron problemas en las firmas';

        modal.innerHTML = `
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full ${allValid ? 'bg-green-100' : 'bg-red-100'}">
                        <i class="${statusIcon} ${statusColor} text-xl"></i>
                    </div>
                    <div class="mt-2 px-7 py-3">
                        <h4 class="text-lg font-semibold text-gray-900 text-center">Verificación de Firmas Digitales</h4>
                        <p class="text-sm ${statusColor} text-center mt-2 font-medium">${statusText}</p>
                        <p class="text-xs text-gray-500 text-center">Verificado el: ${new Date(data.verified_at).toLocaleString()}</p>
                        
                        <div class="mt-4">
                            <h5 class="font-medium text-gray-900 mb-2">Detalle de firmas:</h5>
                            <div class="space-y-2">
                                ${data.signature_details.map(sig => `
                                    <div class="border rounded p-3 ${sig.integrity_valid && sig.document_exists ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'}">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="font-medium">${this.getStageLabel(sig.signature_type)}</p>
                                                <p class="text-sm text-gray-600">Firmado por: ${sig.signer}</p>
                                                <p class="text-xs text-gray-500">Fecha: ${new Date(sig.signed_at).toLocaleString()}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs ${sig.integrity_valid ? 'text-green-600' : 'text-red-600'}">
                                                    <i class="fas fa-${sig.integrity_valid ? 'check' : 'times'}"></i>
                                                    ${sig.integrity_valid ? 'Íntegra' : 'Comprometida'}
                                                </p>
                                                <p class="text-xs ${sig.document_exists ? 'text-green-600' : 'text-red-600'}">
                                                    <i class="fas fa-${sig.document_exists ? 'file-pdf' : 'times'}"></i>
                                                    ${sig.document_exists ? 'Archivo OK' : 'Archivo faltante'}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300" onclick="this.closest('.signature-verification-modal').remove()">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        `;

        return modal;
    }

    /**
     * Obtener etiqueta de etapa
     */
    getStageLabel(signatureType) {
        switch (signatureType) {
            case 'employee':
                return 'Etapa 1: Firma del Empleado';
            case 'level1_supervisor':
                return 'Etapa 2: Aprobación Jefe Inmediato';
            case 'level2_hr':
                return 'Etapa 3: Aprobación Final RRHH';
            default:
                return 'Firma desconocida';
        }
    }

    /**
     * Actualizar estado de firmas de la solicitud actual
     */
    async refreshSignatureStatus() {
        if (!this.currentPermissionId) return;

        try {
            // CAMBIO: Usar route() en lugar de URL hardcodeada
            const url = this.route('signatureStatus', { permission: this.currentPermissionId });
            if (!url) {
                console.error('URL de estado de firma no encontrada');
                return;
            }

            const response = await fetch(url);
            const data = await response.json();

            if (data.success) {
                this.updateSignatureDisplay(data);
            }
        } catch (error) {
            console.error('Error al actualizar estado de firmas:', error);
        }
    }

    /**
     * Actualizar periódicamente el estado de firmas en la página
     */
    async updateSignatureStatus() {
        const permissionCards = document.querySelectorAll('[data-permission-id]');
        
        for (const card of permissionCards) {
            const permissionId = card.dataset.permissionId;
            if (!permissionId) continue;

            try {
                // CAMBIO: Usar route() en lugar de URL hardcodeada
                const url = this.route('signatureStatus', { permission: permissionId });
                if (!url) {
                    console.error(`URL de estado de firma no encontrada para permiso ${permissionId}`);
                    continue; // CAMBIO: usar continue en lugar de return para procesar otros permisos
                }

                const response = await fetch(url);
                const data = await response.json();

                if (data.success) {
                    this.updateSignatureDisplay(data, permissionId);
                }
            } catch (error) {
                console.error(`Error al actualizar estado de solicitud ${permissionId}:`, error);
            }
        }
    }

    /**
     * Actualizar display de firmas en la UI
     */
    updateSignatureDisplay(data, permissionId = null) {
        const targetId = permissionId || this.currentPermissionId;
        const statusContainer = document.querySelector(`[data-permission-id="${targetId}"] .signature-status`);
        
        if (statusContainer) {
            statusContainer.innerHTML = this.generateSignatureStatusHTML(data);
        }
    }

    /**
     * Generar HTML para mostrar estado de firmas
     */
    generateSignatureStatusHTML(data) {
        if (data.total_signatures === 0) {
            return '<p class="text-gray-500 text-sm">Sin firmas digitales</p>';
        }

        let html = `<div class="signature-progress mb-2">`;
        
        // Barra de progreso
        const progress = (data.signatures.length / 3) * 100;
        html += `
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: ${progress}%"></div>
            </div>
            <p class="text-xs text-gray-600 mt-1">${data.signatures.length} de 3 firmas completadas</p>
        `;
        
        html += `</div><div class="signature-list space-y-1">`;
        
        // Lista de firmas
        data.signatures.forEach(signature => {
            const stageIcon = signature.stage === 1 ? 'fas fa-user' : 
                             signature.stage === 2 ? 'fas fa-user-tie' : 
                             'fas fa-building';
            
            const statusIcon = signature.integrity_valid ? 'fas fa-check text-green-500' : 'fas fa-exclamation-triangle text-red-500';
            
            html += `
                <div class="flex items-center justify-between text-xs">
                    <span><i class="${stageIcon}"></i> ${signature.signer_name}</span>
                    <span><i class="${statusIcon}"></i> ${new Date(signature.signed_at).toLocaleDateString()}</span>
                </div>
            `;
        });
        
        html += `</div>`;
        
        if (data.is_fully_signed) {
            html += `<p class="text-green-600 text-xs font-medium mt-2"><i class="fas fa-check-circle"></i> Completamente firmado</p>`;
        }
        
        return html;
    }

    /**
     * Manejar errores de firma
     */
    handleSignatureError(error) {
        console.error('Error en proceso de firma:', error);
        this.showError(error.message || 'Error desconocido en el proceso de firma');
        
        // Restaurar estado de botones
        this.updateButtonState('ready');
    }

    /**
     * Mostrar mensaje de éxito
     */
    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    /**
     * Mostrar mensaje de error
     */
    showError(message) {
        this.showNotification(message, 'error');
    }

    /**
     * Mostrar mensaje de advertencia
     */
    showWarning(message) {
        this.showNotification(message, 'warning');
    }

    /**
     * Mostrar mensaje de información
     */
    showInfo(message) {
        this.showNotification(message, 'info');
    }

    /**
     * Mostrar notificación
     */
    showNotification(message, type = 'info') {
        // Remover notificaciones anteriores
        const existing = document.querySelector('.firma-peru-notification');
        if (existing) {
            existing.remove();
        }

        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg firma-peru-notification max-w-md`;
        
        const colors = {
            success: 'bg-green-500 text-white',
            error: 'bg-red-500 text-white',
            warning: 'bg-yellow-500 text-black',
            info: 'bg-blue-500 text-white'
        };
        
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-times-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };

        notification.className += ` ${colors[type]}`;
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="${icons[type]} mr-2"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-lg">×</button>
            </div>
        `;

        document.body.appendChild(notification);

        // Auto-remover después de 5 segundos (excepto errores que duran 8 segundos)
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, type === 'error' ? 8000 : 5000);
    }

    /**
     * Mostrar indicador de carga
     */
    showLoading(message = 'Procesando...') {
        const loading = document.createElement('div');
        loading.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 firma-peru-loading';
        loading.innerHTML = `
            <div class="bg-white p-6 rounded-lg shadow-lg text-center">
                <i class="fas fa-spinner fa-spin text-3xl text-blue-500 mb-4"></i>
                <p class="text-gray-700">${message}</p>
            </div>
        `;
        document.body.appendChild(loading);
    }

    /**
     * Ocultar indicador de carga
     */
    hideLoading() {
        const loading = document.querySelector('.firma-peru-loading');
        if (loading) {
            loading.remove();
        }
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.firmaPeruIntegration = new FirmaPeruIntegration();
});

// Exportar para uso global
window.FirmaPeruIntegration = FirmaPeruIntegration;