<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::with(['parentDepartment', 'manager', 'users']);
        
        // Filtros de búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('parent_department_id')) {
            $query->where('parent_department_id', $request->parent_department_id);
        }
        
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        $departments = $query->paginate(15);
        $parentDepartments = Department::whereNull('parent_department_id')
            ->active()
            ->orderBy('name')
            ->get();
        
        return view('admin.departments.index', compact('departments', 'parentDepartments'));
    }

    public function hierarchy()
    {
        // Departamentos padre (raíz de la jerarquía)
        $rootDepartments = Department::whereNull('parent_department_id')
            ->with(['childDepartments', 'manager', 'users'])
            ->get();
        
        return view('admin.departments.hierarchy', compact('rootDepartments'));
    }

    public function getHierarchyData()
    {
        $departments = Department::with(['childDepartments', 'manager', 'users'])->get();
        $hierarchyData = $this->buildHierarchyTree($departments);
        
        return response()->json($hierarchyData);
    }

    private function buildHierarchyTree($departments, $parentId = null)
    {
        $tree = [];
        
        foreach ($departments as $department) {
            if ($department->parent_department_id == $parentId) {
                $node = [
                    'id' => $department->id,
                    'name' => $department->name,
                    'code' => $department->code,
                    'manager' => $department->manager ? $department->manager->full_name : 'Sin gerente',
                    'users_count' => $department->users->count(),
                    'is_active' => $department->is_active,
                    'children' => $this->buildHierarchyTree($departments, $department->id)
                ];
                $tree[] = $node;
            }
        }
        
        return $tree;
    }

    public function create()
    {
        $parentDepartments = Department::whereNull('parent_department_id')
            ->orWhereNotNull('parent_department_id')
            ->active()
            ->orderBy('name')
            ->get();
        
        $managers = User::whereIn('role_id', [
            \App\Models\Role::where('name', 'jefe_inmediato')->first()?->id,
            \App\Models\Role::where('name', 'jefe_rrhh')->first()?->id,
            \App\Models\Role::where('name', 'admin')->first()?->id,
        ])->where('is_active', true)->get();
        
        return view('admin.departments.create', compact('parentDepartments', 'managers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments',
            'code' => 'required|string|max:10|unique:departments',
            'description' => 'nullable|string|max:500',
            'parent_department_id' => 'nullable|exists:departments,id',
            'manager_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        // Validar que el departamento padre no sea el mismo
        if ($request->parent_department_id) {
            $parentDept = Department::find($request->parent_department_id);
            if (!$parentDept->is_active) {
                return back()->withErrors(['parent_department_id' => 'No se puede asignar un departamento padre inactivo.']);
            }
        }

        $department = Department::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'parent_department_id' => $request->parent_department_id,
            'manager_id' => $request->manager_id,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Departamento creado exitosamente.');
    }

    public function show(Department $department)
    {
        $department->load(['parentDepartment', 'childDepartments', 'manager', 'users.role']);
        
        return view('admin.departments.show', compact('department'));
    }

    public function edit(Department $department)
    {
        $parentDepartments = Department::where('id', '!=', $department->id)
            ->active()
            ->orderBy('name')
            ->get();
        
        $managers = User::whereIn('role_id', [
            \App\Models\Role::where('name', 'jefe_inmediato')->first()?->id,
            \App\Models\Role::where('name', 'jefe_rrhh')->first()?->id,
            \App\Models\Role::where('name', 'admin')->first()?->id,
        ])->where('is_active', true)->get();
        
        return view('admin.departments.edit', compact('department', 'parentDepartments', 'managers'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments')->ignore($department)],
            'code' => ['required', 'string', 'max:10', Rule::unique('departments')->ignore($department)],
            'description' => 'nullable|string|max:500',
            'parent_department_id' => 'nullable|exists:departments,id',
            'manager_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        // Validar que no se asigne como padre a sí mismo
        if ($request->parent_department_id && $request->parent_department_id == $department->id) {
            return back()->withErrors(['parent_department_id' => 'Un departamento no puede ser padre de sí mismo.']);
        }

        // Validar que no cree ciclos en la jerarquía
        if ($request->parent_department_id && $department->isParentOf(Department::find($request->parent_department_id))) {
            return back()->withErrors(['parent_department_id' => 'Esta asignación crearía un ciclo en la jerarquía de departamentos.']);
        }

        $department->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'parent_department_id' => $request->parent_department_id,
            'manager_id' => $request->manager_id,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Departamento actualizado exitosamente.');
    }

    public function destroy(Department $department)
    {
        // Verificar que el departamento no tenga usuarios activos
        if ($department->users()->where('is_active', true)->exists()) {
            return back()->withErrors(['error' => 'No se puede eliminar un departamento que tiene usuarios activos.']);
        }

        // Verificar que no tenga departamentos hijos activos
        if ($department->childDepartments()->where('is_active', true)->exists()) {
            return back()->withErrors(['error' => 'No se puede eliminar un departamento que tiene subdepartamentos activos.']);
        }

        $department->update(['is_active' => false]);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Departamento desactivado exitosamente.');
    }

    public function activate(Department $department)
    {
        $department->update(['is_active' => true]);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Departamento activado exitosamente.');
    }

    public function getUsersByDepartment(Department $department)
    {
        $users = $department->users()->with('role')->get();
        
        return response()->json($users);
    }
}