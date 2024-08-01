<?php

namespace App\Http\Controllers;

use App\Models\Priority;
use App\Models\Task;
use App\Models\TipoTarea;
use App\Models\User;
use App\Repositories\TaskRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    protected $taskRepository;

    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    // Listar todas las tareas con id y nombre (API)
    public function index()
    {
        $tasks = $this->taskRepository->all();
        return response()->json($tasks);
    }

    // Obtener tareas de un usuario por ID (API)
    public function getUserTasks($userId)
    {
        $tasks = $this->taskRepository->getByUserId($userId);
        return response()->json($tasks);
    }

    // Mostrar tareas del usuario autenticado
    public function userTasks()
    {
        $tasks = Task::where('user_id', Auth::id())->get();
        return view('tasks.index', ['tasks' => $tasks]);
    }

    // Mostrar formulario para crear una nueva tarea
    public function create()
    {
        return view('tasks.create', [
            'priorities' => Priority::all(),
            'user' => User::all(),
            'tipoTarea' => TipoTarea::all(),
        ]);
    }

    // Mostrar una tarea específica
    public function show(Task $task)
    {
        return view('tasks.show', ['task' => $task]);
    }

    // Almacenar una nueva tarea
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'min:3', 'max:255'],
            'description' => ['required', 'min:3'],
            'priority_id' => 'required|exists:priorities,id',
            'user_id' => 'required|exists:users,id',
            'tags' => 'array',
            'tags.*' => 'exists:tipoTarea,id',
        ]);

        $task = Task::create([
            'name' => $data['name'],
            'description' => $data['description'],
            'priority_id' => $data['priority_id'],
            'user_id' => $data['user_id'],
        ]);

        $task->tipoTarea()->attach($data['tags']);
        return redirect('/tasks');
    }

    // Mostrar formulario para editar una tarea existente
    public function edit(Task $task)
    {
        if (Gate::allows('edit-post', $task)) {
            $tipotarea = TipoTarea::all();
            $priority = Priority::all();
            return view('tasks.edit', [
                'task' => $task,
                'priority' => $priority,
                'tipotarea' => $tipotarea,
            ]);
        } else {
            return response()->json(['message' => 'You are not authorized to edit this task'], 403);
        }
    }

    // Actualizar una tarea existente
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $this->authorize('update', $task);

        $validatedData = $request->validate([
            'name' => ['required', 'min:3', 'max:255'],
            'description' => ['required', 'min:3'],
            'tipoTareas' => ['required', 'array'],
            'tipoTareas.*' => ['exists:tipoTarea,id'],
            'user_id' => 'required|exists:users,id',
        ]);

        $task->update([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'user_id' => $validatedData['user_id'],
        ]);

        $task->tipoTarea()->sync($validatedData['tipoTareas']);
        return redirect('/tasks/' . $task->id);
    }

    // Eliminar una tarea existente
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        if ($task->delete()) {
            return response()->json(['message' => 'Task deleted successfully']);
        } else {
            return response()->json(['message' => 'Task could not be deleted'], 500);
        }
    }

    // Marcar una tarea como completada
    public function completed(Task $task)
    {
        $task->completed = 1;
        $task->save();
        return redirect('/tasks/');
    }
}
