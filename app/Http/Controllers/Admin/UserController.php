<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['role', 'department', 'immediateSupervisor']);
        
        // Filtros de búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('dni', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }
        
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        $users = $query->paginate(15);
        $departments = Department::active()->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        
        return view('admin.users.index', compact('users', 'departments', 'roles'));
    }

    public function hierarchy()
    {
        // Usuarios sin jefe inmediato (raíz de la jerarquía)
        $rootUsers = User::whereNull('immediate_supervisor_id')
            ->with(['role', 'department', 'subordinates.role', 'subordinates.department'])
            ->get();
        
        return view('admin.users.hierarchy', compact('rootUsers'));
    }

    public function getHierarchyData()
    {
        $users = User::with(['role', 'department', 'subordinates'])->get();
        $hierarchyData = $this->buildHierarchyTree($users);
        
        return response()->json($hierarchyData);
    }

    private function buildHierarchyTree($users, $parentId = null)
    {
        $tree = [];
        
        foreach ($users as $user) {
            if ($user->immediate_supervisor_id == $parentId) {
                $node = [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'role' => $user->role->name ?? 'Sin rol',
                    'department' => $user->department->name ?? 'Sin departamento',
                    'is_active' => $user->is_active,
                    'children' => $this->buildHierarchyTree($users, $user->id)
                ];
                $tree[] = $node;
            }
        }
        
        return $tree;
    }

    public function create()
    {
        $roles = Role::all();
        $departments = Department::active()->get();
        $supervisors = User::where('role_id', Role::where('name', 'jefe_inmediato')->first()?->id)
            ->orWhere('role_id', Role::where('name', 'jefe_rrhh')->first()?->id)
            ->get();
        
        return view('admin.users.create', compact('roles', 'departments', 'supervisors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'dni' => 'required|string|max:20|unique:users',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'required|exists:departments,id',
            'immediate_supervisor_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        // Validar que no se asigne como supervisor a sí mismo
        if ($request->immediate_supervisor_id && $request->immediate_supervisor_id == $request->user()->id) {
            return back()->withErrors(['immediate_supervisor_id' => 'No puede asignarse como supervisor de sí mismo.']);
        }

        $user = User::create([
            'dni' => $request->dni,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'department_id' => $request->department_id,
            'immediate_supervisor_id' => $request->immediate_supervisor_id,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function show(User $user)
    {
        $user->load(['role', 'department', 'immediateSupervisor', 'subordinates.role']);
        
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $departments = Department::active()->get();
        $supervisors = User::where('id', '!=', $user->id)
            ->where(function ($query) {
                $query->where('role_id', Role::where('name', 'jefe_inmediato')->first()?->id)
                      ->orWhere('role_id', Role::where('name', 'jefe_rrhh')->first()?->id);
            })->get();
        
        return view('admin.users.edit', compact('user', 'roles', 'departments', 'supervisors'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'dni' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($user)],
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'required|exists:departments,id',
            'immediate_supervisor_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        // Validar que no se asigne como supervisor a sí mismo
        if ($request->immediate_supervisor_id && $request->immediate_supervisor_id == $user->id) {
            return back()->withErrors(['immediate_supervisor_id' => 'No puede asignarse como supervisor de sí mismo.']);
        }

        $updateData = [
            'dni' => $request->dni,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'department_id' => $request->department_id,
            'immediate_supervisor_id' => $request->immediate_supervisor_id,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user)
    {
        // Verificar que el usuario no tenga subordinados activos
        if ($user->subordinates()->where('is_active', true)->exists()) {
            return back()->withErrors(['error' => 'No se puede eliminar un usuario que tiene subordinados activos.']);
        }

        // Verificar que no sea el último admin
        if ($user->hasRole('admin')) {
            $adminCount = User::whereHas('role', function ($query) {
                $query->where('name', 'admin');
            })->where('is_active', true)->count();
            
            if ($adminCount <= 1) {
                return back()->withErrors(['error' => 'No se puede eliminar el último administrador del sistema.']);
            }
        }

        $user->update(['is_active' => false]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario desactivado exitosamente.');
    }

    public function activate(User $user)
    {
        $user->update(['is_active' => true]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario activado exitosamente.');
    }

    public function resetPassword(User $user)
    {
        $newPassword = 'temporal123';
        $user->update(['password' => Hash::make($newPassword)]);

        return redirect()->route('admin.users.show', $user)
            ->with('success', "Contraseña restablecida. Nueva contraseña temporal: {$newPassword}");
    }
}