<?php
namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\TaskResource;

class TaskController extends Controller
{
    // Index all tasks for the authenticated employee
    public function index()
    {
        if (Auth::user()->role === 'employee') {
            // Return only the tasks assigned to the authenticated employee
            $tasks = Task::where('employee_id', Auth::id())->get();
        } else {
            // Return all tasks for managers (to see all tasks for all employees)
            $tasks = Task::with('employee')->get();
        }

        return TaskResource::collection($tasks);
    }

    // Show a specific task (accessible by managers and employees)
    public function show(Task $task)
    {
        if (Auth::user()->role === 'employee') {
            // Ensure employees can only view their own tasks
            if ($task->employee_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        return new TaskResource($task);
    }

    // Store a new task (accessible by managers only)
    public function store(Request $request)
    {
        // Ensure only managers can create tasks
        $this->authorize('create', Task::class);

        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:pending,completed',
        ]);

        $task = Task::create($request->only('employee_id', 'name', 'description', 'status'));

        return new TaskResource($task);
    }

    // Update a specific task (accessible by managers only)
    public function update(Request $request, Task $task)
    {
        // Ensure only managers can update tasks
        $this->authorize('update', $task);

        $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:pending,completed',
        ]);

        $task->update($request->only('name', 'description', 'status'));

        return new TaskResource($task);
    }

    // Delete a specific task (accessible by managers only)
    public function destroy(Task $task)
    {
        // Ensure only managers can delete tasks
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
}
