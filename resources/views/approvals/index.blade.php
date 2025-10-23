<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Aprobaciones') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Mensajes de alerta --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            {{-- Solicitudes Pendientes de Aprobación --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-semibold mb-4 flex flex-wrap items-center gap-2">
                        <span>Solicitudes Pendientes de Aprobación</span>
                        @if($pendingApprovals->total() > 0)
                            <span class="bg-red-600 text-white rounded-full px-2 sm:px-3 py-1 text-xs sm:text-sm">{{ $pendingApprovals->total() }}</span>
                        @endif
                    </h3>

                    @if($pendingApprovals->count() > 0)
                        {{-- Vista Desktop --}}
                        <div class="hidden lg:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            N° Solicitud
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Solicitante
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Departamento
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tipo de Permiso
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Fecha
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Horas
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($pendingApprovals as $request)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $request->request_number }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $request->user->full_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $request->user->department->name ?? 'Sin asignar' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $request->permissionType->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $request->created_at->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                -
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('approvals.show', $request) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-xs">
                                                    Revisar
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Vista Mobile/Tablet (Cards) --}}
                        <div class="lg:hidden space-y-4">
                            @foreach($pendingApprovals as $request)
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase">N° Solicitud</p>
                                            <p class="text-sm font-semibold text-gray-900">{{ $request->request_number }}</p>
                                        </div>
                                        <span class="text-xs text-gray-500">{{ $request->created_at->format('d/m/Y') }}</span>
                                    </div>
                                    
                                    <div class="space-y-2 mb-3">
                                        <div>
                                            <p class="text-xs text-gray-500">Solicitante</p>
                                            <p class="text-sm font-medium text-gray-900">{{ $request->user->full_name }}</p>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <p class="text-xs text-gray-500">Departamento</p>
                                                <p class="text-sm text-gray-700">{{ $request->user->department->name ?? 'Sin asignar' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500">Tipo de Permiso</p>
                                                <p class="text-sm text-gray-700">{{ $request->permissionType->name }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <a href="{{ route('approvals.show', $request) }}" class="block w-full text-center bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                                        Revisar Solicitud
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-4">
                            {{ $pendingApprovals->links() }}
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">No hay solicitudes pendientes de aprobación.</p>
                    @endif
                </div>
            </div>

            {{-- Historial de Aprobaciones --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-semibold mb-4">Historial de Aprobaciones</h3>

                    @if($approvalHistory->count() > 0)
                        {{-- Vista Desktop --}}
                        <div class="hidden lg:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            N° Solicitud
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Solicitante
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tipo
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Decisión
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Fecha Aprobación
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($approvalHistory as $approval)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $approval->permissionRequest->request_number }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $approval->permissionRequest->user->full_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $approval->permissionRequest->permissionType->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $approval->getStatusColor() }}-100 text-{{ $approval->getStatusColor() }}-800">
                                                    {{ $approval->getStatusLabel() }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $approval->approved_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('permissions.show', $approval->permissionRequest) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    Ver detalles
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Vista Mobile/Tablet (Cards) --}}
                        <div class="lg:hidden space-y-4">
                            @foreach($approvalHistory as $approval)
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase">N° Solicitud</p>
                                            <p class="text-sm font-semibold text-gray-900">{{ $approval->permissionRequest->request_number }}</p>
                                        </div>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $approval->getStatusColor() }}-100 text-{{ $approval->getStatusColor() }}-800">
                                            {{ $approval->getStatusLabel() }}
                                        </span>
                                    </div>
                                    
                                    <div class="space-y-2 mb-3">
                                        <div>
                                            <p class="text-xs text-gray-500">Solicitante</p>
                                            <p class="text-sm font-medium text-gray-900">{{ $approval->permissionRequest->user->full_name }}</p>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <p class="text-xs text-gray-500">Tipo</p>
                                                <p class="text-sm text-gray-700">{{ $approval->permissionRequest->permissionType->name }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500">Fecha Aprobación</p>
                                                <p class="text-sm text-gray-700">{{ $approval->approved_at->format('d/m/Y H:i') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <a href="{{ route('permissions.show', $approval->permissionRequest) }}" class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-sm">
                                        Ver Detalles
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-4">
                            {{ $approvalHistory->links() }}
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">No hay historial de aprobaciones.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>