<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sistema de Papeletas - Municipalidad de San Jerónimo') }}</title>
        <meta name="description" content="Sistema Digital de Papeletas - Municipalidad Distrital de San Jerónimo">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .municipal-gradient {
                background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%);
            }
            
            .municipal-pattern {
                background-image: 
                    radial-gradient(circle at 25% 25%, rgba(255,255,255,0.1) 0%, transparent 50%),
                    radial-gradient(circle at 75% 75%, rgba(255,255,255,0.05) 0%, transparent 50%);
            }

            .card-shadow {
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.05);
            }

            .input-focus {
                @apply focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200;
            }

            .btn-municipal {
                background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
                transition: all 0.3s ease;
            }

            .btn-municipal:hover {
                background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
                transform: translateY(-1px);
                box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
            }
        </style>
    </head>
    <body class="font-inter antialiased">
        <div class="min-h-screen municipal-gradient municipal-pattern flex flex-col justify-center items-center p-4">
            <!-- Header Municipal -->
            <div class="text-center mb-8">
                <div class="flex justify-center items-center mb-4">
                    <!-- Logo Placeholder - Reemplazar con el logo real -->
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-lg mr-4">
                        <svg class="w-10 h-10 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h4a2 2 0 012 2v2a2 2 0 01-2 2H8a2 2 0 01-2-2v-2z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="text-left">
                        <h1 class="text-2xl font-bold text-white">Municipalidad Distrital</h1>
                        <h2 class="text-lg font-semibold text-blue-100">San Jerónimo</h2>
                    </div>
                </div>
                <p class="text-blue-100 font-medium">Sistema Digital de Papeletas</p>
                <p class="text-blue-200 text-sm mt-1">Gestión Municipal Moderna y Eficiente</p>
            </div>

            <!-- Card de Login -->
            <div class="w-full max-w-md">
                <div class="bg-white/95 backdrop-blur-sm card-shadow rounded-2xl overflow-hidden">
                    <!-- Header del Card -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-6 text-center">
                        <h3 class="text-xl font-semibold text-white">Acceso al Sistema</h3>
                        <p class="text-blue-100 text-sm mt-1">Ingrese sus credenciales</p>
                    </div>

                    <!-- Contenido -->
                    <div class="px-8 py-8">
                        {{ $slot }}
                    </div>

                    <!-- Footer del Card -->
                    <div class="bg-gray-50 px-8 py-4 text-center border-t">
                        <p class="text-xs text-gray-500">
                            © {{ date('Y') }} Municipalidad Distrital de San Jerónimo
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            Sistema desarrollado para la modernización municipal
                        </p>
                    </div>
                </div>

                <!-- Información adicional -->
                <div class="mt-6 text-center">
                    <p class="text-white/80 text-sm">
                        ¿Problemas con el acceso? 
                        <a href="#" class="text-white font-medium hover:underline ml-1">
                            Contactar Soporte Técnico
                        </a>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center">
                <div class="flex justify-center items-center space-x-6 text-white/70 text-sm">
                    <a href="#" class="hover:text-white transition-colors">Términos de Uso</a>
                    <span>•</span>
                    <a href="#" class="hover:text-white transition-colors">Política de Privacidad</a>
                    <span>•</span>
                    <a href="#" class="hover:text-white transition-colors">Ayuda</a>
                </div>
            </div>
        </div>
    </body>
</html>