@if($myRequests->count() > 0)
    {{-- Vista móvil --}}
    <div class="block sm:hidden">
        @foreach($myRequests as $request)
            <div class="p-4 border-b border-gray-100 last:border-b-0 hover:bg-gray-50 transition-colors duration-150">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center min-w-0 flex-1">
                        <div class="bg-blue-100 rounded-full p-2 mr-3 flex-shrink-0">
                            <i class="fas fa-file-alt text-blue-600 text-xs"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-gray-900 text-sm truncate">{{ $request->request_number }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $request->permissionType->name }}</p>
                        </div>
                    </div>
                    @php
                        $statusConfig = [
                            'pending' => ['bg-yellow-100', 'text-yellow-800', 'fas fa-clock'],
                            'approved' => ['bg-green-100', 'text-green-800', 'fas fa-check-circle'],
                            'rejected' => ['bg-red-100', 'text-red-800', 'fas fa-times-circle'],
                            'draft' => ['bg-gray-100', 'text-gray-800', 'fas fa-edit']
                        ];
                        $config = $statusConfig[$request->status] ?? ['bg-gray-100', 'text-gray-800', 'fas fa-question'];
                    @endphp
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $config[0] }} {{ $config[1] }} flex-shrink-0">
                        <i class="{{ $config[2] }} mr-1"></i>
                        {{ $request->getStatusLabel() }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="text-xs text-gray-500">
                        {{ $request->created_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('permissions.show', $request) }}" 
                           class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded text-xs transition-colors duration-150">
                            <i class="fas fa-eye mr-1"></i>Ver
                        </a>
                        @if($request->status === 'draft')
                            <a href="{{ route('permissions.edit', $request) }}" 
                               class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 px-2 py-1 rounded text-xs transition-colors duration-150">
                                <i class="fas fa-edit mr-1"></i>Editar
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Vista desktop --}}
    <div class="hidden sm:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <i class="fas fa-hashtag mr-1"></i>
                        Número
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <i class="fas fa-tag mr-1"></i>
                        Tipo
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <i class="fas fa-calendar mr-1"></i>
                        Fecha/Hora
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <i class="fas fa-info-circle mr-1"></i>
                        Estado
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <i class="fas fa-cog mr-1"></i>
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($myRequests as $request)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $request->request_number }}
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="bg-blue-100 rounded-full p-2 mr-3">
                                    <i class="fas fa-file-alt text-blue-600 text-xs"></i>
                                </div>
                                <div class="text-sm text-gray-900">
                                    {{ $request->permissionType->name }}
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $request->created_at->format('d/m/Y') }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $request->created_at->format('H:i') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusConfig = [
                                    'pending' => ['bg-yellow-100', 'text-yellow-800', 'fas fa-clock'],
                                    'approved' => ['bg-green-100', 'text-green-800', 'fas fa-check-circle'],
                                    'rejected' => ['bg-red-100', 'text-red-800', 'fas fa-times-circle'],
                                    'draft' => ['bg-gray-100', 'text-gray-800', 'fas fa-edit']
                                ];
                                $config = $statusConfig[$request->status] ?? ['bg-gray-100', 'text-gray-800', 'fas fa-question'];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $config[0] }} {{ $config[1] }}">
                                <i class="{{ $config[2] }} mr-1"></i>
                                {{ $request->getStatusLabel() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('permissions.show', $request) }}" 
                                   class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded-md transition-colors duration-150">
                                    <i class="fas fa-eye mr-1"></i>
                                    Ver
                                </a>
                                @if($request->status === 'draft')
                                    <a href="{{ route('permissions.edit', $request) }}" 
                                       class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 px-3 py-1 rounded-md transition-colors duration-150">
                                        <i class="fas fa-edit mr-1"></i>
                                        Editar
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="p-8 sm:p-12 text-center">
        <div class="mx-auto w-16 h-16 sm:w-24 sm:h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4 animate-pulse">
            <i class="fas fa-file-alt text-2xl sm:text-3xl text-gray-400"></i>
        </div>
        <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-2">No hay solicitudes</h3>
        <p class="text-gray-500 mb-4 sm:mb-6 text-sm sm:text-base">Aún no has creado ninguna solicitud de papeleta.</p>
        <a href="{{ route('permissions.create') }}" 
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-150 transform hover:scale-105">
            <i class="fas fa-plus mr-2"></i>
            Crear primera solicitud
        </a>
    </div>
@endif