<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function getAllDepartments(Request $request)
    {
        // Get the authenticated user (the manager)
        $manager = Auth::user();

        // Check if the authenticated user is a manager
        if ($manager->role !== 'manager') {
            return response()->json([
                'message' => 'Only managers can access this data.',
            ], 403); // Forbidden response for non-managers
        }

        // Retrieve all departments
        $departments = Department::all();

        return response()->json([
            'message' => 'Departments retrieved successfully.',
            'departments' => $departments,
        ], 200);
    }
    public function getEmployeesByDepartment(Request $request)
{
    // Validate the request to ensure 'department' is provided
    $request->validate([
        'department' => 'required|string|exists:departments,name',
    ]);

    // Get the manager from the authenticated user
    $manager = Auth::user();

    // Check if the user is a manager
    if ($manager->role !== 'manager') {
        return response()->json([
            'message' => 'Only managers can access this data.',
        ], 403);
    }

    // Find the department by its name
    $department = Department::where('name', $request->department)->first();

    // Retrieve all employees from the same department
    $employees = User::where('department_id', $department->id)
                      ->where('role', 'employee')
                      ->get();

    return response()->json([
        'message' => 'Employees retrieved successfully',
        'department' => $department->name,
        'employees' => $employees,
    ], 200);
}
public function getEmployeeDetails(Request $request, $employeeId)
{
    // Get the authenticated user (the manager)
    $manager = Auth::user();

    // Check if the user is a manager
    if ($manager->role !== 'manager') {
        return response()->json([
            'message' => 'Only managers can access this data.',
        ], 403);
    }

    // Find the employee by ID (or you can switch to email if needed)
    $employee = User::where('id', $employeeId)
                    ->where('role', 'employee')
                    ->first();

    // Check if the employee exists
    if (!$employee) {
        return response()->json([
            'message' => 'Employee not found.',
        ], 404);
    }

    // Return employee details
    return response()->json([
        'message' => 'Employee details retrieved successfully.',
        'employee' => $employee,
    ], 200);
}


}
