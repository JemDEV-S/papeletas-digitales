<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesión expirada - Sistema Municipal</title>
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
                        'fade-in': 'fadeIn 0.6s ease-out'
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
        <div class="absolute -top-4 -left-4 w-24 h-24 bg-gradient-to-r from-blue-400 to-blue-600 rounded-full opacity-20 animate-pulse-slow"></div>
        <div class="absolute top-1/4 -right-8 w-32 h-32 bg-gradient-to-r from-amber-400 to-amber-600 rounded-full opacity-15 animate-float"></div>
        <div class="absolute bottom-1/3 -left-6 w-20 h-20 bg-gradient-to-r from-indigo-400 to-indigo-600 rounded-full opacity-25 animate-float"></div>
    </div>

    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">

            <div class="bg-white/90 backdrop-blur-sm shadow-2xl rounded-3xl p-8 border border-white/20 animate-slide-up text-center">

                <!-- Icono de reloj / expiración -->
                <div class="relative inline-block mb-6 animate-fade-in">
                    <div class="w-20 h-20 bg-gradient-to-br from-amber-500 to-orange-500 rounded-2xl flex items-center justify-center mx-auto shadow-lg">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="absolute inset-0 w-20 h-20 rounded-2xl border-2 border-amber-400 opacity-30 animate-ping"></div>
                </div>

                <h1 class="text-3xl font-bold bg-gradient-to-r from-municipal-dark to-municipal-blue bg-clip-text text-transparent mb-2">
                    Sesión expirada
                </h1>
                <p class="text-sm font-semibold text-municipal-gray mb-4">Error 419</p>

                <p class="text-municipal-gray leading-relaxed mb-8">
                    Tu sesión expiró por inactividad o la página estuvo abierta demasiado tiempo.
                    No te preocupes, no perdiste nada: solo vuelve a iniciar sesión para continuar.
                </p>

                <!-- Botón volver al login -->
                <a href="{{ route('login') }}"
                   class="w-full inline-flex items-center justify-center space-x-3 bg-gradient-to-r from-municipal-blue to-municipal-light hover:from-municipal-light hover:to-municipal-blue text-white font-bold py-4 px-6 rounded-xl transition-all duration-300 transform hover:scale-[1.02] focus:outline-none focus:ring-4 focus:ring-municipal-light/30 shadow-lg hover:shadow-xl group relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent transform -skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    <span class="relative">Volver al inicio de sesión</span>
                </a>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center animate-fade-in">
                <p class="text-xs text-municipal-gray font-medium">
                    Sistema de Papeletas Digitales &copy; {{ date('Y') }}
                </p>
            </div>
        </div>
    </div>

</body>
</html>
