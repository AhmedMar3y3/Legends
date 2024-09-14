<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;
use App\Http\Resources\ShiftResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ShiftController extends Controller
{
    // Index all shifts (accessible by managers and employees)
    public function index()
    {
        if (Auth::user()->role === 'employee') {
            // Return only the shifts of the authenticated employee
            $shifts = Shift::where('employee_id', Auth::id())->get();
        } else {
            // Return all shifts for managers
            $shifts = Shift::all();
        }

        // Return a collection of shifts using the ShiftResource
        return ShiftResource::collection($shifts);
    }

    // Show a specific shift (accessible by managers and employees)
    public function show(Shift $shift)
    {
        if (Auth::user()->role === 'employee') {
            // Ensure employees can only view their own shifts
            if ($shift->employee_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        // Return a single shift resource
        return new ShiftResource($shift);
    }

    // Store a new shift (accessible by managers only)
    public function store(Request $request)
    {
        // Ensure only managers can create shifts
        $this->authorize('create', Shift::class);

        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'from' => 'required|date_format:H:i',
            'to' => 'required|date_format:H:i',
            'day' => 'required|string',
        ]);

        $shift = Shift::create([
            'employee_id' => $request->employee_id,
            'from' => $request->from,
            'to' => $request->to,
            'day' => $request->day,
        ]);

        // Return the newly created shift resource
        return new ShiftResource($shift);
    }

    // Update a specific shift (accessible by managers only)
    public function update(Request $request, Shift $shift)
    {
        // Ensure only managers can update shifts
        $this->authorize('update', $shift);

        $request->validate([
            'from' => 'nullable|date_format:H:i',
            'to' => 'nullable|date_format:H:i',
            'day' => 'nullable|string',
        ]);

        $shift->update($request->only('from', 'to', 'day'));

        // Return the updated shift resource
        return new ShiftResource($shift);
    }

    // Delete a specific shift (accessible by managers only)
    public function destroy(Shift $shift)
    {
        // Ensure only managers can delete shifts
        $this->authorize('delete', $shift);

        $shift->delete();

        return response()->json(['message' => 'Shift deleted successfully']);
    }
    public function shiftEmployees(Shift $shift)
    {
        $this->authorize('viewEmployees', Shift::class);

    // Get all employees who have the same shift timing (from, to, day)
    $employees = Shift::where('from', $shift->from)
                      ->where('to', $shift->to)
                      ->where('day', $shift->day)
                      ->with('employee')  // Retrieve the employee details
                      ->get()
                      ->pluck('employee');

    return response()->json($employees);
    }
}
