<div class="user-node">
    <div class="user-card {{ $user->role->name ?? 'empleado' }} {{ !$user->is_active ? 'inactive' : '' }}"
         onclick="event.stopPropagation();">
        
        <div class="user-info">
            <div class="user-name">
                {{ $user->full_name }}
                @if(!$user->is_active)
                    <span class="text-red-500 text-xs ml-1">(Inactivo)</span>
                @endif
            </div>
            
            <div class="user-role {{ $user->role->name ?? 'empleado' }}">
                {{ ucfirst(str_replace('_', ' ', $user->role->name ?? 'Sin rol')) }}
            </div>
            
            <div class="user-email">
                {{ $user->email }}
            </div>
            
            <div class="user-department">
                {{ $user->department->name ?? 'Sin departamento' }}
            </div>
            
            <div class="user-actions">
                <a href="{{ route('admin.users.show', $user) }}" class="action-btn view">
                    Ver
                </a>
                <a href="{{ route('admin.users.edit', $user) }}" class="action-btn edit">
                    Editar
                </a>
            </div>
        </div>
        
        @if($user->subordinates->count() > 0)
            <button class="expand-button" 
                    id="expand-btn-{{ $user->id }}"
                    onclick="toggleSubordinates({{ $user->id }})"
                    title="Contraer subordinados">
                −
            </button>
        @endif
    </div>
    
    @if($user->subordinates->count() > 0)
        <div class="subordinates" id="subordinates-{{ $user->id }}">
            @foreach($user->subordinates->sortBy('role.name') as $subordinate)
                @include('admin.users.partials.user-node', ['user' => $subordinate, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>

<style>
    .user-role.admin {
        background: #fecaca;
        color: #991b1b;
    }
    
    .user-role.jefe_rrhh {
        background: #ddd6fe;
        color: #5b21b6;
    }
    
    .user-role.jefe_inmediato {
        background: #bfdbfe;
        color: #1e40af;
    }
    
    .user-role.empleado {
        background: #f3f4f6;
        color: #374151;
    }
</style>