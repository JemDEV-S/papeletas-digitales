<x-app-layout>
   <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mi Perfil') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Banner institucional -->
            <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-6 border-l-4 border-green-600">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-green-600 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Portal del Trabajador Digital</h3>
                        <p class="text-sm text-gray-600">Gestiona tu información personal de forma segura</p>
                    </div>
                </div>
            </div>

            <!-- Información del Perfil -->
            <div class="bg-white shadow-xl rounded-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-green-600 px-6 py-4">
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-white">Información Personal</h3>
                    </div>
                </div>

                <div class="p-6">
                    <section class="space-y-6">
                        <div class="mb-4">
                            <p class="text-gray-600 text-sm leading-relaxed">
                                {{ __("Actualice la información de perfil y dirección de correo electrónico de su cuenta.") }}
                            </p>
                            <div class="mt-2 flex items-center text-xs text-blue-700">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Información protegida por la Municipalidad
                            </div>
                        </div>

                        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                            @csrf
                        </form>

                        <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
                            @csrf
                            @method('patch')

                            <!-- Campo Nombre -->
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-5">
                                <div class="flex items-center mb-3">
                                    <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <x-input-label for="name" :value="__('Nombre Completo')" class="text-gray-700 font-medium" />
                                        <p class="text-xs text-gray-500 mt-1">Ingrese su nombre tal como aparece en su DNI</p>
                                    </div>
                                </div>
                                
                                <x-text-input 
                                    id="name" 
                                    name="name" 
                                    type="text" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" 
                                    :value="old('name', $user->name)" 
                                    required 
                                    autofocus 
                                    autocomplete="name"
                                    placeholder="Ej: Juan Carlos Pérez López" 
                                />
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            </div>

                            <!-- Campo Email -->
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-5">
                                <div class="flex items-center mb-3">
                                    <div class="bg-green-100 p-2 rounded-lg mr-3">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <x-input-label for="email" :value="__('Correo Electrónico')" class="text-gray-700 font-medium" />
                                        <p class="text-xs text-gray-500 mt-1">Para recibir notificaciones municipales</p>
                                    </div>
                                </div>
                                
                                <x-text-input 
                                    id="email" 
                                    name="email" 
                                    type="email" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-200" 
                                    :value="old('email', $user->email)" 
                                    required 
                                    autocomplete="username"
                                    placeholder="ejemplo@correo.com" 
                                />
                                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                    <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <div class="flex items-start">
                                            <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                            <div>
                                                <p class="text-sm text-yellow-800 mb-2">
                                                    {{ __('Su dirección de correo electrónico no está verificada.') }}
                                                </p>
                                                <button form="send-verification" class="text-sm text-yellow-700 underline hover:text-yellow-900 font-medium">
                                                    {{ __('Haga clic aquí para reenviar el correo de verificación.') }}
                                                </button>
                                            </div>
                                        </div>

                                        @if (session('status') === 'verification-link-sent')
                                            <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-md">
                                                <p class="text-sm text-green-700 flex items-center">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    {{ __('Un nuevo enlace de verificación ha sido enviado a su dirección de correo electrónico.') }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <!-- Botón Guardar -->
                            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                                <x-primary-button class="px-6 py-3 bg-gradient-to-r from-blue-600 to-green-600 hover:from-blue-700 hover:to-green-700 font-semibold text-white rounded-lg shadow-lg transform transition duration-150 hover:scale-105">
                                    <svg class="w-4 h-4 mr-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    {{ __('Guardar Cambios') }}
                                </x-primary-button>

                                @if (session('status') === 'profile-updated')
                                    <p
                                        x-data="{ show: true }"
                                        x-show="show"
                                        x-transition
                                        x-init="setTimeout(() => show = false, 3000)"
                                        class="text-sm text-green-600 flex items-center bg-green-50 px-3 py-2 rounded-lg"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ __('Guardado correctamente.') }}
                                    </p>
                                @endif
                            </div>
                        </form>
                    </section>
                </div>
            </div>

            <!-- Cambiar Contraseña -->
            <div class="bg-white shadow-xl rounded-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-orange-500 to-red-500 px-6 py-4">
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-white">Seguridad de la Cuenta</h3>
                    </div>
                </div>

                <div class="p-6">
                    <section class="space-y-6">
                        <div class="mb-4">
                            <h4 class="text-lg font-medium text-gray-900 mb-2">
                                {{ __('Actualizar Contraseña') }}
                            </h4>
                            <p class="text-gray-600 text-sm leading-relaxed">
                                {{ __('Asegúrese de que su cuenta esté usando una contraseña larga y aleatoria para mantenerse seguro.') }}
                            </p>
                            <div class="mt-2 flex items-center text-xs text-orange-700">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.25-1.776a302.06 302.06 0 00-.776-1.043m0 0a301.286 301.286 0 00-2.253-2.231m2.253 2.231L15.75 9m-6-3.5l1.5 1.5M12 6l-3-3m0 0l-3.5 3.5M9 3L9 21"></path>
                                </svg>
                                Se recomienda cambiar la contraseña periódicamente
                            </div>
                        </div>

                        <form method="post" action="{{ route('password.update') }}" class="space-y-6">
                            @csrf
                            @method('put')

                            <!-- Contraseña Actual -->
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-5">
                                <div class="flex items-center mb-3">
                                    <div class="bg-orange-100 p-2 rounded-lg mr-3">
                                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </div>
                                    <x-input-label for="update_password_current_password" :value="__('Contraseña Actual')" class="text-gray-700 font-medium" />
                                </div>
                                <x-text-input 
                                    id="update_password_current_password" 
                                    name="current_password" 
                                    type="password" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition duration-200" 
                                    autocomplete="current-password"
                                    placeholder="Ingrese su contraseña actual"
                                />
                                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                            </div>

                            <!-- Nueva Contraseña -->
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-5">
                                <div class="flex items-center mb-3">
                                    <div class="bg-red-100 p-2 rounded-lg mr-3">
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                    </div>
                                    <x-input-label for="update_password_password" :value="__('Nueva Contraseña')" class="text-gray-700 font-medium" />
                                </div>
                                <x-text-input 
                                    id="update_password_password" 
                                    name="password" 
                                    type="password" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition duration-200" 
                                    autocomplete="new-password"
                                    placeholder="Mínimo 8 caracteres"
                                />
                                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                            </div>

                            <!-- Confirmar Contraseña -->
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-5">
                                <div class="flex items-center mb-3">
                                    <div class="bg-purple-100 p-2 rounded-lg mr-3">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <x-input-label for="update_password_password_confirmation" :value="__('Confirmar Contraseña')" class="text-gray-700 font-medium" />
                                </div>
                                <x-text-input 
                                    id="update_password_password_confirmation" 
                                    name="password_confirmation" 
                                    type="password" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200" 
                                    autocomplete="new-password"
                                    placeholder="Repita la nueva contraseña"
                                />
                                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                            </div>

                            <!-- Botón Actualizar -->
                            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                                <x-primary-button class="px-6 py-3 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 font-semibold text-white rounded-lg shadow-lg transform transition duration-150 hover:scale-105">
                                    <svg class="w-4 h-4 mr-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    {{ __('Actualizar Contraseña') }}
                                </x-primary-button>

                                @if (session('status') === 'password-updated')
                                    <p
                                        x-data="{ show: true }"
                                        x-show="show"
                                        x-transition
                                        x-init="setTimeout(() => show = false, 3000)"
                                        class="text-sm text-green-600 flex items-center bg-green-50 px-3 py-2 rounded-lg"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ __('Contraseña actualizada correctamente.') }}
                                    </p>
                                @endif
                            </div>
                        </form>
                    </section>
                </div>
            </div>

            <!-- Footer institucional -->
            <div class="bg-gradient-to-r from-gray-50 to-blue-50 rounded-lg p-6 border border-gray-200 text-center">
                <div class="flex items-center justify-center mb-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-green-600 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900">Municipalidad Distrital de San Jerónimo</h4>
                </div>
                <p class="text-sm text-gray-600">
                    Trabajando juntos por el desarrollo sostenible de nuestro distrito
                </p>
                <div class="mt-3 text-xs text-gray-500">
                    Portal Ciudadano Digital • Versión 2024
                </div>
            </div>
        </div>
    </div>
</x-app-layout>