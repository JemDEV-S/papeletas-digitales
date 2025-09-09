<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Jerarquía de Departamentos
            </h2>
            <div class="flex space-x-2">
                <button onclick="expandAllDepartments()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Expandir Todo
                </button>
                <button onclick="collapseAllDepartments()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Contraer Todo
                </button>
                <a href="{{ route('admin.departments.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Lista de Departamentos
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Leyenda -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Leyenda</h3>
                    <div class="flex flex-wrap gap-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-blue-100 border border-blue-300 rounded"></div>
                            <span class="text-sm text-gray-700">Departamento Activo</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-red-50 border border-red-200 rounded opacity-50"></div>
                            <span class="text-sm text-gray-700">Departamento Inactivo</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-green-100 border border-green-300 rounded"></div>
                            <span class="text-sm text-gray-700">Con Gerente Asignado</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-yellow-100 border border-yellow-300 rounded"></div>
                            <span class="text-sm text-gray-700">Sin Gerente</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas Rápidas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Departamentos</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $rootDepartments->sum(function($dept) { return 1 + $dept->childDepartments->count(); }) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Departamentos Raíz</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $rootDepartments->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Empleados</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $rootDepartments->sum(function($dept) { return $dept->users->count() + $dept->childDepartments->sum(function($child) { return $child->users->count(); }); }) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Con Gerente</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $rootDepartments->where('manager_id', '!=', null)->count() + $rootDepartments->sum(function($dept) { return $dept->childDepartments->where('manager_id', '!=', null)->count(); }) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Árbol Jerárquico -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div id="department-hierarchy-tree" class="department-hierarchy-container">
                        @if($rootDepartments->count() > 0)
                            @foreach($rootDepartments as $rootDepartment)
                                @include('admin.departments.partials.department-node', ['department' => $rootDepartment, 'level' => 0])
                            @endforeach
                        @else
                            <div class="text-center py-8 text-gray-500">
                                <p class="text-lg">No se encontraron departamentos en la jerarquía</p>
                                <p class="text-sm">Cree departamentos para visualizar la estructura organizacional.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .department-hierarchy-container {
            font-family: 'Inter', sans-serif;
        }
        
        .department-node {
            margin: 10px 0;
            position: relative;
        }
        
        .department-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            max-width: 350px;
        }
        
        .department-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        
        .department-card.active {
            border-color: #3b82f6;
            background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
        }
        
        .department-card.with-manager {
            border-color: #10b981;
            background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
        }
        
        .department-card.no-manager {
            border-color: #f59e0b;
            background: linear-gradient(135deg, #fffbeb 0%, #ffffff 100%);
        }
        
        .department-card.inactive {
            opacity: 0.6;
            border-style: dashed;
            background: #f9fafb;
        }
        
        .department-info {
            position: relative;
            z-index: 2;
        }
        
        .department-name {
            font-weight: 700;
            font-size: 18px;
            color: #1f2937;
            margin-bottom: 4px;
        }
        
        .department-code {
            font-family: 'Monaco', monospace;
            font-size: 12px;
            font-weight: 500;
            padding: 4px 8px;
            border-radius: 6px;
            background: #f3f4f6;
            color: #374151;
            display: inline-block;
            margin-bottom: 8px;
        }
        
        .department-description {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 8px;
            line-height: 1.4;
        }
        
        .department-stats {
            display: flex;
            gap: 12px;
            margin-bottom: 8px;
        }
        
        .stat-item {
            font-size: 12px;
            color: #9ca3af;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .department-manager {
            font-size: 13px;
            color: #059669;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .subdepartments {
            margin-left: 40px;
            padding-left: 20px;
            border-left: 2px dashed #d1d5db;
            margin-top: 16px;
            position: relative;
        }
        
        .subdepartments::before {
            content: '';
            position: absolute;
            left: -6px;
            top: -8px;
            width: 10px;
            height: 10px;
            border: 2px solid #d1d5db;
            border-radius: 50%;
            background: white;
        }
        
        .expand-button-dept {
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s;
            z-index: 10;
        }
        
        .expand-button-dept:hover {
            background: #2563eb;
            transform: translateX(-50%) scale(1.1);
        }
        
        .subdepartments.collapsed {
            display: none;
        }
        
        .department-actions {
            margin-top: 10px;
            display: flex;
            gap: 8px;
        }
        
        .action-btn-dept {
            padding: 4px 8px;
            font-size: 12px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .action-btn-dept.view {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .action-btn-dept.edit {
            background: #f0f9ff;
            color: #0369a1;
        }
        
        .action-btn-dept:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        @media (max-width: 768px) {
            .subdepartments {
                margin-left: 20px;
                padding-left: 15px;
            }
            
            .department-card {
                max-width: 100%;
            }
        }
    </style>

    <script>
        function expandAllDepartments() {
            document.querySelectorAll('.subdepartments.collapsed').forEach(el => {
                el.classList.remove('collapsed');
                const button = el.parentElement.querySelector('.expand-button-dept');
                if (button) {
                    button.innerHTML = '−';
                    button.title = 'Contraer subdepartamentos';
                }
            });
        }
        
        function collapseAllDepartments() {
            document.querySelectorAll('.subdepartments:not(.collapsed)').forEach(el => {
                el.classList.add('collapsed');
                const button = el.parentElement.querySelector('.expand-button-dept');
                if (button) {
                    button.innerHTML = '+';
                    button.title = 'Expandir subdepartamentos';
                }
            });
        }
        
        function toggleSubdepartments(departmentId) {
            const subdepartmentsEl = document.getElementById('subdepartments-' + departmentId);
            const button = document.getElementById('expand-btn-dept-' + departmentId);
            
            if (subdepartmentsEl.classList.contains('collapsed')) {
                subdepartmentsEl.classList.remove('collapsed');
                button.innerHTML = '−';
                button.title = 'Contraer subdepartamentos';
            } else {
                subdepartmentsEl.classList.add('collapsed');
                button.innerHTML = '+';
                button.title = 'Expandir subdepartamentos';
            }
        }
        
        // Inicialización: contraer algunos nodos si hay muchos niveles
        document.addEventListener('DOMContentLoaded', function() {
            const allSubdepartments = document.querySelectorAll('.subdepartments');
            if (allSubdepartments.length > 8) {
                // Contraer niveles más profundos
                document.querySelectorAll('.subdepartments .subdepartments').forEach(el => {
                    el.classList.add('collapsed');
                    const button = el.parentElement.querySelector('.expand-button-dept');
                    if (button) {
                        button.innerHTML = '+';
                        button.title = 'Expandir subdepartamentos';
                    }
                });
            }
        });
    </script>
</x-app-layout>