<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Municipal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'municipal-blue': '#1e3a8a',
                        'municipal-light': '#3b82f6',
                        'municipal-accent': '#10b981',
                        'municipal-dark': '#0f172a',
                        'municipal-gray': '#64748b'
                    },
                    animation: {
                        'float': 'float 3s ease-in-out infinite',
                        'pulse-slow': 'pulse 3s infinite',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'fade-in': 'fadeIn 0.6s ease-out',
                        'bounce-gentle': 'bounceGentle 2s ease-in-out infinite'
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-10px)' }
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' }
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        },
                        bounceGentle: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-5px)' }
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 relative overflow-hidden">
    
    <!-- Elementos decorativos de fondo -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <!-- Círculos decorativos -->
        <div class="absolute -top-4 -left-4 w-24 h-24 bg-gradient-to-r from-blue-400 to-blue-600 rounded-full opacity-20 animate-pulse-slow"></div>
        <div class="absolute top-1/4 -right-8 w-32 h-32 bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-full opacity-15 animate-float"></div>
        <div class="absolute bottom-1/3 -left-6 w-20 h-20 bg-gradient-to-r from-indigo-400 to-indigo-600 rounded-full opacity-25 animate-bounce-gentle"></div>
        
        <!-- Formas geométricas -->
        <div class="absolute top-1/2 right-1/4 w-16 h-16 bg-gradient-to-br from-blue-300 to-blue-500 transform rotate-45 opacity-10 animate-pulse-slow"></div>
        <div class="absolute bottom-1/4 left-1/3 w-12 h-12 bg-gradient-to-br from-emerald-300 to-emerald-500 transform rotate-12 opacity-15 animate-float"></div>
        
        <!-- Líneas decorativas -->
        <svg class="absolute top-0 left-0 w-full h-full opacity-5" viewBox="0 0 100 100" preserveAspectRatio="none">
            <defs>
                <linearGradient id="grid" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#3b82f6;stop-opacity:1" />
                    <stop offset="100%" style="stop-color:#10b981;stop-opacity:0" />
                </linearGradient>
            </defs>
            <pattern id="smallGrid" width="10" height="10" patternUnits="userSpaceOnUse">
                <path d="M 10 0 L 0 0 0 10" fill="none" stroke="url(#grid)" stroke-width="0.5"/>
            </pattern>
            <rect width="100%" height="100%" fill="url(#smallGrid)" />
        </svg>
    </div>

    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            
            <!-- Contenedor principal del formulario -->
            <div class="bg-white/90 backdrop-blur-sm shadow-2xl rounded-3xl p-8 border border-white/20 animate-slide-up">
                
                <!-- Header con logo y título -->
                <div class="text-center mb-8 animate-fade-in">
                    <div class="relative inline-block mb-4">
                        <div class="w-20 h-20 bg-gradient-to-br from-municipal-blue to-municipal-light rounded-2xl flex items-center justify-center mx-auto shadow-lg transform hover:scale-105 transition-transform duration-300">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.5-4L12 17l-3-3" />
                            </svg>
                        </div>
                        <!-- Efecto de anillo pulsante -->
                        <div class="absolute inset-0 w-20 h-20 rounded-2xl border-2 border-municipal-light opacity-30 animate-ping"></div>
                    </div>
                    <h2 class="text-2xl font-bold bg-gradient-to-r from-municipal-dark to-municipal-blue bg-clip-text text-transparent mb-2">
                        Bienvenido al Sistema
                    </h2>
                    <p class="text-municipal-gray">Gestión de Papeletas Digitales</p>
                </div>

                <!-- Session Status -->
                <div class="mb-6">
                    @if (session('status'))
                        <div class="bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-200 rounded-xl p-4 animate-slide-up relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-br from-emerald-200/20 to-green-200/20 rounded-full transform translate-x-6 -translate-y-6"></div>
                            <div class="flex items-center relative">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-green-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-emerald-800">
                                        {{ session('status') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    <!-- CSRF Token -->
                    @csrf
                    <!-- Campo DNI -->
                    <div class="space-y-2 animate-slide-up" style="animation-delay: 0.1s">
                        <label for="dni" class="block text-sm font-semibold text-municipal-dark">
                            <span class="flex items-center">
                                <div class="w-5 h-5 mr-2 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-municipal-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V4a2 2 0 114 0v2m-4 0a2 2 0 104 0m-4 0a2 2 0 014 0"/>
                                    </svg>
                                </div>
                                Número de DNI
                            </span>
                        </label>
                        <div class="relative group">
                            <input id="dni" 
                                   class="w-full px-4 py-4 pl-12 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-municipal-light/20 focus:border-municipal-light transition-all duration-300 bg-gray-50/50 hover:bg-white group-hover:border-municipal-light/50 placeholder-gray-400" 
                                   type="text" 
                                   name="dni" 
                                   value="{{ old('dni') }}" 
                                   required 
                                   autofocus 
                                   autocomplete="username"
                                   placeholder="12345678"
                                   maxlength="8"
                                   pattern="[0-9]{8}">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-municipal-gray group-focus-within:text-municipal-blue transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V4a2 2 0 114 0v2m-4 0a2 2 0 104 0m-4 0a2 2 0 014 0"/>
                                </svg>
                            </div>
                            <!-- Indicador de validación -->
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
                                <div class="w-2 h-2 rounded-full bg-gray-300 transition-colors" id="dni-indicator"></div>
                            </div>
                        </div>
                        <div class="text-red-500 text-sm flex items-center mt-2 hidden" id="dni-error">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ $message ?? 'DNI inválido' }}</span>
                        </div>
                    </div>

                    <!-- Campo Contraseña -->
                    <div class="space-y-2 animate-slide-up" style="animation-delay: 0.2s">
                        <label for="password" class="block text-sm font-semibold text-municipal-dark">
                            <span class="flex items-center">
                                <div class="w-5 h-5 mr-2 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-municipal-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                Contraseña
                            </span>
                        </label>
                        <div class="relative group">
                            <input id="password" 
                                   class="w-full px-4 py-4 pl-12 pr-12 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-municipal-light/20 focus:border-municipal-light transition-all duration-300 bg-gray-50/50 hover:bg-white group-hover:border-municipal-light/50 placeholder-gray-400" 
                                   type="password" 
                                   name="password" 
                                   required 
                                   autocomplete="current-password"
                                   placeholder="••••••••">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-municipal-gray group-focus-within:text-municipal-blue transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <!-- Botón mostrar/ocultar contraseña -->
                            <button type="button" class="absolute inset-y-0 right-0 pr-4 flex items-center text-municipal-gray hover:text-municipal-blue transition-colors" onclick="togglePassword()">
                                <svg class="w-5 h-5" id="eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        <div class="text-red-500 text-sm flex items-center mt-2 hidden" id="password-error">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ $message ?? 'Contraseña requerida' }}</span>
                        </div>
                    </div>

                    <!-- Opciones adicionales -->
                    <div class="flex items-center justify-between animate-slide-up" style="animation-delay: 0.3s">
                        <label for="remember_me" class="flex items-center cursor-pointer group">
                            <div class="relative">
                                <input id="remember_me" 
                                       type="checkbox" 
                                       class="sr-only" 
                                       name="remember">
                                <div class="w-5 h-5 bg-gray-200 rounded border-2 border-gray-300 group-hover:border-municipal-light transition-colors" id="checkbox-bg"></div>
                                <svg class="w-3 h-3 text-white absolute top-0.5 left-0.5 hidden" id="checkbox-check" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="ml-3 text-sm text-municipal-gray group-hover:text-municipal-dark transition-colors select-none">
                                Recordar sesión
                            </span>
                        </label>
                    </div>

                    <!-- Botón de envío -->
                    <div class="animate-slide-up" style="animation-delay: 0.4s">
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-municipal-blue to-municipal-light hover:from-municipal-light hover:to-municipal-blue text-white font-bold py-4 px-6 rounded-xl transition-all duration-300 transform hover:scale-[1.02] focus:outline-none focus:ring-4 focus:ring-municipal-light/30 flex items-center justify-center space-x-3 shadow-lg hover:shadow-xl group relative overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent transform -skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                            <svg class="w-5 h-5 group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            <span class="relative">Iniciar Sesión</span>
                        </button>
                    </div>
                </form>

                <!-- Panel informativo -->
                <div class="mt-8 animate-slide-up" style="animation-delay: 0.5s">
                    <div class="bg-gradient-to-r from-emerald-50 to-blue-50 border border-emerald-200/50 rounded-2xl p-4 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-emerald-200/30 to-blue-200/30 rounded-full transform translate-x-8 -translate-y-8"></div>
                        <div class="flex items-start relative">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h5 class="text-sm font-semibold text-emerald-800 mb-1">Sistema Seguro</h5>
                                <p class="text-xs text-emerald-700 leading-relaxed">
                                    Acceso exclusivo para personal autorizado de la Municipalidad Distrital de San Jerónimo.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center animate-fade-in" style="animation-delay: 0.6s">
                <div class="space-y-3">
                    <p class="text-xs text-municipal-gray font-medium">
                        Sistema de Papeletas Digitales &copy; 2025
                    </p>
                    <div class="flex justify-center items-center space-x-6 text-xs">
                        <a href="#" class="text-municipal-gray hover:text-municipal-blue transition-colors flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            Manual(Proximamente)
                        </a>
                        <div class="w-1 h-1 bg-municipal-gray rounded-full opacity-50"></div>
                        <a href="#" class="text-municipal-gray hover:text-municipal-blue transition-colors flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Soporte OTI
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Validación de DNI en tiempo real
        const dniInput = document.getElementById('dni');
        const dniIndicator = document.getElementById('dni-indicator');
        const dniError = document.getElementById('dni-error');

        dniInput.addEventListener('input', function(e) {
            const value = e.target.value;
            const isValid = /^\d{8}$/.test(value);
            
            if (value.length === 0) {
                dniIndicator.className = 'w-2 h-2 rounded-full bg-gray-300 transition-colors';
                dniError.classList.add('hidden');
            } else if (isValid) {
                dniIndicator.className = 'w-2 h-2 rounded-full bg-emerald-500 transition-colors';
                dniError.classList.add('hidden');
            } else {
                dniIndicator.className = 'w-2 h-2 rounded-full bg-red-500 transition-colors';
                dniError.classList.remove('hidden');
            }
        });

        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        }

        // Custom checkbox
        const checkbox = document.getElementById('remember_me');
        const checkboxBg = document.getElementById('checkbox-bg');
        const checkboxCheck = document.getElementById('checkbox-check');

        checkbox.addEventListener('change', function() {
            if (this.checked) {
                checkboxBg.className = 'w-5 h-5 bg-municipal-blue rounded border-2 border-municipal-blue transition-colors';
                checkboxCheck.classList.remove('hidden');
            } else {
                checkboxBg.className = 'w-5 h-5 bg-gray-200 rounded border-2 border-gray-300 group-hover:border-municipal-light transition-colors';
                checkboxCheck.classList.add('hidden');
            }
        });

        // Animaciones adicionales al cargar
        window.addEventListener('load', function() {
            const elements = document.querySelectorAll('.animate-slide-up');
            elements.forEach((el, index) => {
                setTimeout(() => {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>