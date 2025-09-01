<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'parent_department_id',
        'manager_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the parent department
     */
    public function parentDepartment()
    {
        return $this->belongsTo(Department::class, 'parent_department_id');
    }

    /**
     * Get child departments
     */
    public function childDepartments()
    {
        return $this->hasMany(Department::class, 'parent_department_id');
    }

    /**
     * Get the department manager
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get all users in this department
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get active departments only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the full hierarchy path
     */
    public function getHierarchyPath(): string
    {
        $path = [$this->name];
        $parent = $this->parentDepartment;
        
        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parentDepartment;
        }
        
        return implode(' > ', $path);
    }

    /**
     * Check if this department is a parent of another
     */
    public function isParentOf(Department $department): bool
    {
        $current = $department->parentDepartment;
        
        while ($current) {
            if ($current->id === $this->id) {
                return true;
            }
            $current = $current->parentDepartment;
        }
        
        return false;
    }
}