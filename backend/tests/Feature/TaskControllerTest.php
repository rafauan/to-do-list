<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_all_tasks()
    {
        Task::factory()->count(3)->create();

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'tasks' => [
                         '*' => ['id', 'title', 'description', 'status', 'created_at', 'updated_at'],
                     ],
                 ]);
    }

    public function test_store_creates_a_new_task()
    {
        $data = [
            'title' => 'New Task',
            'description' => 'Task description',
        ];

        $response = $this->postJson('/api/tasks', $data);

        $response->assertStatus(201)
                 ->assertJson([
                     'message' => 'Task created successfully',
                 ]);

        $this->assertDatabaseHas('tasks', $data);
    }

    public function test_update_modifies_an_existing_task()
    {
        $task = Task::factory()->create();

        $data = [
            'title' => 'Updated Task',
            'description' => 'Updated description',
            'status' => 'completed',
        ];

        $response = $this->putJson("/api/tasks/{$task->id}", $data);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Task updated successfully',
                 ]);

        $this->assertDatabaseHas('tasks', array_merge(['id' => $task->id], $data));
    }

    public function test_destroy_deletes_a_task()
    {
        $task = Task::factory()->create();

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
