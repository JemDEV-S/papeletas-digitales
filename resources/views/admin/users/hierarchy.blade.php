<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Jerarquía Organizacional
            </h2>
            <div class="flex space-x-2">
                <button onclick="expandAll()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Expandir Todo
                </button>
                <button onclick="collapseAll()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Contraer Todo
                </button>
                <a href="{{ route('admin.users.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Lista de Usuarios
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
                            <div class="w-4 h-4 bg-red-100 border border-red-300 rounded"></div>
                            <span class="text-sm text-gray-700">Administrador</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-purple-100 border border-purple-300 rounded"></div>
                            <span class="text-sm text-gray-700">Jefe RRHH</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-blue-100 border border-blue-300 rounded"></div>
                            <span class="text-sm text-gray-700">Jefe Inmediato</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-gray-100 border border-gray-300 rounded"></div>
                            <span class="text-sm text-gray-700">Empleado</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-red-50 border border-red-200 rounded opacity-50"></div>
                            <span class="text-sm text-gray-700">Usuario Inactivo</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Árbol Jerárquico -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div id="hierarchy-tree" class="hierarchy-container">
                        @if($rootUsers->count() > 0)
                            @foreach($rootUsers as $rootUser)
                                @include('admin.users.partials.user-node', ['user' => $rootUser, 'level' => 0])
                            @endforeach
                        @else
                            <div class="text-center py-8 text-gray-500">
                                <p class="text-lg">No se encontraron usuarios en la jerarquía</p>
                                <p class="text-sm">Asegúrese de que los usuarios tengan roles y supervisores asignados correctamente.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hierarchy-container {
            font-family: 'Inter', sans-serif;
        }
        
        .user-node {
            margin: 8px 0;
            position: relative;
        }
        
        .user-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            max-width: 300px;
        }
        
        .user-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        
        .user-card.admin {
            border-color: #fca5a5;
            background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
        }
        
        .user-card.jefe_rrhh {
            border-color: #c4b5fd;
            background: linear-gradient(135deg, #f3f4f6 0%, #ffffff 100%);
        }
        
        .user-card.jefe_inmediato {
            border-color: #93c5fd;
            background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
        }
        
        .user-card.empleado {
            border-color: #d1d5db;
            background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
        }
        
        .user-card.inactive {
            opacity: 0.6;
            border-style: dashed;
        }
        
        .user-info {
            position: relative;
            z-index: 2;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 16px;
            color: #1f2937;
            margin-bottom: 4px;
        }
        
        .user-role {
            font-size: 12px;
            font-weight: 500;
            padding: 4px 8px;
            border-radius: 6px;
            display: inline-block;
            margin-bottom: 8px;
        }
        
        .user-email {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 4px;
        }
        
        .user-department {
            font-size: 12px;
            color: #9ca3af;
        }
        
        .subordinates {
            margin-left: 40px;
            padding-left: 20px;
            border-left: 2px dashed #d1d5db;
            margin-top: 16px;
            position: relative;
        }
        
        .subordinates::before {
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
        
        .expand-button {
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: #4f46e5;
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
        
        .expand-button:hover {
            background: #3730a3;
            transform: translateX(-50%) scale(1.1);
        }
        
        .subordinates.collapsed {
            display: none;
        }
        
        .user-actions {
            margin-top: 8px;
            display: flex;
            gap: 8px;
        }
        
        .action-btn {
            padding: 4px 8px;
            font-size: 12px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .action-btn.view {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .action-btn.edit {
            background: #f0f9ff;
            color: #0369a1;
        }
        
        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        @media (max-width: 768px) {
            .subordinates {
                margin-left: 20px;
                padding-left: 15px;
            }
            
            .user-card {
                max-width: 100%;
            }
        }
    </style>

    <script>
        function expandAll() {
            document.querySelectorAll('.subordinates.collapsed').forEach(el => {
                el.classList.remove('collapsed');
                const button = el.parentElement.querySelector('.expand-button');
                if (button) {
                    button.innerHTML = '−';
                    button.title = 'Contraer subordinados';
                }
            });
        }
        
        function collapseAll() {
            document.querySelectorAll('.subordinates:not(.collapsed)').forEach(el => {
                el.classList.add('collapsed');
                const button = el.parentElement.querySelector('.expand-button');
                if (button) {
                    button.innerHTML = '+';
                    button.title = 'Expandir subordinados';
                }
            });
        }
        
        function toggleSubordinates(userId) {
            const subordinatesEl = document.getElementById('subordinates-' + userId);
            const button = document.getElementById('expand-btn-' + userId);
            
            if (subordinatesEl.classList.contains('collapsed')) {
                subordinatesEl.classList.remove('collapsed');
                button.innerHTML = '−';
                button.title = 'Contraer subordinados';
            } else {
                subordinatesEl.classList.add('collapsed');
                button.innerHTML = '+';
                button.title = 'Expandir subordinados';
            }
        }
        
        // Inicialización: contraer todos los nodos por defecto si hay muchos niveles
        document.addEventListener('DOMContentLoaded', function() {
            const allSubordinates = document.querySelectorAll('.subordinates');
            if (allSubordinates.length > 10) {
                collapseAll();
            }
        });
    </script>
</x-app-layout>