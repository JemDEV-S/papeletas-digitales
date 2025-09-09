<div class="department-node">
    <div class="department-card {{ $department->is_active ? 'active' : 'inactive' }} {{ $department->manager ? 'with-manager' : 'no-manager' }}"
         onclick="event.stopPropagation();">
        
        <div class="department-info">
            <div class="department-name">
                {{ $department->name }}
                @if(!$department->is_active)
                    <span class="text-red-500 text-xs ml-1">(Inactivo)</span>
                @endif
            </div>
            
            <div class="department-code">
                {{ $department->code }}
            </div>
            
            @if($department->description)
                <div class="department-description">
                    {{ Str::limit($department->description, 80) }}
                </div>
            @endif
            
            <div class="department-stats">
                <div class="stat-item">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                    </svg>
                    {{ $department->users->count() }} empleados
                </div>
                @if($department->childDepartments->count() > 0)
                    <div class="stat-item">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        {{ $department->childDepartments->count() }} subdepts.
                    </div>
                @endif
            </div>
            
            @if($department->manager)
                <div class="department-manager">
                    👨‍💼 {{ $department->manager->full_name }}
                </div>
            @else
                <div class="text-amber-600 text-sm">
                    ⚠️ Sin gerente asignado
                </div>
            @endif
            
            <div class="department-actions">
                <a href="{{ route('admin.departments.show', $department) }}" class="action-btn-dept view">
                    Ver
                </a>
                <a href="{{ route('admin.departments.edit', $department) }}" class="action-btn-dept edit">
                    Editar
                </a>
            </div>
        </div>
        
        @if($department->childDepartments->count() > 0)
            <button class="expand-button-dept" 
                    id="expand-btn-dept-{{ $department->id }}"
                    onclick="toggleSubdepartments({{ $department->id }})"
                    title="Contraer subdepartamentos">
                −
            </button>
        @endif
    </div>
    
    @if($department->childDepartments->count() > 0)
        <div class="subdepartments" id="subdepartments-{{ $department->id }}">
            @foreach($department->childDepartments->sortBy('name') as $childDepartment)
                @include('admin.departments.partials.department-node', ['department' => $childDepartment, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>