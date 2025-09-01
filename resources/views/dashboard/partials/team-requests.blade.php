@if($teamRequests->count() > 0)
    {{-- Vista móvil --}}
    <div class="block sm:hidden">
        @foreach($teamRequests as $request)
            <div class="p-4 border-b border-gray-100 last:border-b-0 hover:bg-gray-50 transition-colors duration-150">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center min-w-0 flex-1">
                        <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                            <span class="text-xs font-medium text-gray-600">
                                {{ substr($request->user->first_name, 0, 1) }}{{ substr($request->user->last_name, 0, 1) }}
                            </span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-gray-900 text-sm truncate">{{ $request->user->full_name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $request->user->employee_id ?? 'N/A' }}</p>
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
                    <div class="min-w-0 flex-1">
                        <p class="text-xs text-gray-600 truncate">{{ $request->permissionType->name }}</p>
                        <p class="text-xs text-gray-500">{{ $request->created_at->format('d/m/Y') }}</p>
                    </div>
                    <div class="flex-shrink-0 ml-2">
                        @if(auth()->user()->canApprove($request))
                            <a href="{{ route('approvals.show', $request) }}" 
                               class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 px-2 py-1 rounded text-xs transition-colors duration-150">
                                <i class="fas fa-check mr-1"></i>Revisar
                            </a>
                        @else
                            <span class="text-gray-400 text-xs">Sin acciones</span>
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
                        <i class="fas fa-user mr-1"></i>
                        Empleado
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <i class="fas fa-tag mr-1"></i>
                        Tipo
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <i class="fas fa-calendar mr-1"></i>
                        Fecha
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
                @foreach($teamRequests as $request)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-xs font-medium text-gray-600">
                                        {{ substr($request->user->first_name, 0, 1) }}{{ substr($request->user->last_name, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $request->user->full_name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $request->user->employee_id ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $request->permissionType->name }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $request->created_at->format('d/m/Y') }}
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
                            @if(auth()->user()->canApprove($request))
                                <a href="{{ route('approvals.show', $request) }}" 
                                   class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 px-3 py-1 rounded-md transition-colors duration-150">
                                    <i class="fas fa-check mr-1"></i>
                                    Revisar
                                </a>
                            @else
                                <span class="text-gray-400 text-sm">Sin acciones</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="p-8 sm:p-12 text-center">
        <div class="mx-auto w-16 h-16 sm:w-24 sm:h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4 animate-pulse">
            <i class="fas fa-users text-2xl sm:text-3xl text-gray-400"></i>
        </div>
        <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-2">Sin solicitudes del equipo</h3>
        <p class="text-gray-500 text-sm sm:text-base">No hay solicitudes pendientes de su equipo.</p>
    </div>
@endif