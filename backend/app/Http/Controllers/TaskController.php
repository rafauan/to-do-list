<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::all();

        return response()->json([
            'message' => 'Tasks retrieved successfully',
            'tasks' => $tasks,
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            // 'status' => 'required|in:pending,completed', - ustawia się automatycznie na 'pending'
        ]);

        $task = Task::create($data);
        $task->refresh(); // Refresh aby uzyskać aktualny status po utworzeniu

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task,
        ], 201);
    }

    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:pending,completed',
        ]);

        $task->update($data);

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task,
        ], 200);
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return response()->noContent();
    }
}
