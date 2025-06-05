<script setup>
import { ref, onMounted } from 'vue'
import TaskForm from './TaskForm.vue'

const apiUrl = import.meta.env.VITE_API_URL
const tasks = ref([])
const selectedTask = ref(null)
const showButtons = ref(false)
const showModal = ref(null)
const errorMessage = ref('')

onMounted(async () => {
  try {
    const response = await fetch(`${apiUrl}/tasks`)
    const data = await response.json()
    tasks.value = data.tasks || []
  } catch (error) {
    console.error('Error fetching tasks:', error)
  }
})

const formatDate = (dateString) => {
  const date = new Date(dateString)
  const pad = (n) => n.toString().padStart(2, '0')
  const year = date.getFullYear()
  const month = pad(date.getMonth() + 1)
  const day = pad(date.getDate())
  const hours = pad(date.getHours())
  const minutes = pad(date.getMinutes())
  return `${year}-${month}-${day} ${hours}:${minutes}`
}

const selectTask = (task) => {
  selectedTask.value = task
  showButtons.value = true
}

const formatStatus = (status) => {
  return status.charAt(0).toUpperCase() + status.slice(1)
}

const addTask = async (task) => {
  try {
    const response = await fetch(`${apiUrl}/tasks`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(task),
    })

    const data = await response.json()

    if (response.ok) {
      tasks.value.push(data.task)
      showModal.value = null
    } else {
      console.error('Error adding task:', data.message)
      errorMessage.value = data.message || 'An error occurred while adding the task.'
    }
  } catch (error) {
    console.error('Error adding task:', error)
    errorMessage.value = 'An error occurred while adding the task.'
  }
}

const editTask = async (task) => {
  if (!selectedTask.value) return

  try {
    const response = await fetch(`${apiUrl}/tasks/${selectedTask.value.id}`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(task),
    })

    const data = await response.json()

    if (response.ok) {
      const index = tasks.value.findIndex((t) => t.id === selectedTask.value.id)
      if (index !== -1) {
        tasks.value[index] = data.task
      }
      showModal.value = null
      selectedTask.value = null
      showButtons.value = false
    } else {
      console.error('Error editing task:', data.message)
      errorMessage.value = data.message || 'An error occurred while editing the task.'
    }
  } catch (error) {
    console.error('Error editing task:', error)
    errorMessage.value = 'An error occurred while editing the task.'
  }
}

const removeTask = async () => {
  if (!selectedTask.value) return

  try {
    const response = await fetch(`${apiUrl}/tasks/${selectedTask.value.id}`, {
      method: 'DELETE',
    })

    if (response.ok) {
      tasks.value = tasks.value.filter((task) => task.id !== selectedTask.value.id)
      selectedTask.value = null
      showButtons.value = false
      showModal.value = null
    } else {
      const data = await response.json()
      console.error('Error removing task:', data.message)
      errorMessage.value = data.message || 'An error occurred while removing the task.'
    }
  } catch (error) {
    console.error('Error removing task:', error)
    errorMessage.value = 'An error occurred while removing the task.'
  }
}

const closeModal = () => {
  showModal.value = null
  selectedTask.value = null
  showButtons.value = false
  errorMessage.value = ''
}
</script>

<template>
  <div>
    <div class="container mx-auto p-4">
      <div class="overflow-x-auto">
        <div class="flex gap-4 mb-4">
          <button
            @click="showModal = 'new'"
            class="rounded-md text-sm bg-gray-600 font-medium text-white px-4 py-2 hover:cursor-pointer hover:bg-white hover:text-gray-600 hover:border-gray-600 border-2 border-gray-600 transition-colors duration-300"
          >
            Add new task
          </button>
          <button
            v-if="showButtons"
            @click="showModal = 'edit'"
            class="rounded-md text-sm bg-gray-600 font-medium text-white px-4 py-2 hover:cursor-pointer hover:bg-white hover:text-gray-600 hover:border-gray-600 border-2 border-gray-600 transition-colors duration-300"
          >
            Edit
          </button>
          <button
            v-if="showButtons"
            @click="showModal = 'markStatus'"
            class="rounded-md text-sm bg-gray-600 font-medium text-white px-4 py-2 hover:cursor-pointer hover:bg-white hover:text-gray-600 hover:border-gray-600 border-2 border-gray-600 transition-colors duration-300"
          >
            Mark as finished
          </button>
          <button
            v-if="showButtons"
            @click="showModal = 'remove'"
            class="rounded-md text-sm bg-red-600 font-medium text-white px-4 py-2 hover:cursor-pointer hover:bg-white hover:text-red-600 hover:border-red-600 border-2 border-red-600 transition-colors duration-300"
          >
            Remove
          </button>
        </div>
        <table class="min-w-full divide-y-2 divide-gray-200">
          <thead class="ltr:text-left rtl:text-right">
            <tr class="*:font-medium *:text-gray-900">
              <th class="px-3 py-2 whitespace-nowrap">ID</th>
              <th class="px-3 py-2 whitespace-nowrap">Title</th>
              <th class="px-3 py-2 whitespace-nowrap">Description</th>
              <th class="px-3 py-2 whitespace-nowrap">Status</th>
              <th class="px-3 py-2 whitespace-nowrap">Created at</th>
              <th class="px-3 py-2 whitespace-nowrap">Updated at</th>
            </tr>
          </thead>

          <TransitionGroup
            tag="tbody"
            enter-active-class="transition-opacity duration-300"
            leave-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
          >
            <tr v-if="tasks.length === 0">
              <td colspan="6" class="text-center text-gray-400 py-8">
                No tasks found.
              </td>
            </tr>
            <tr
              v-for="task in tasks"
              :key="task.id"
              class="*:text-gray-900 *:first:font-medium hover:bg-gray-100 hover:cursor-pointer transition-colors duration-300"
              :class="{ 'bg-gray-100': selectedTask && selectedTask.id === task.id }"
              @click="selectTask(task)"
            >
              <td class="px-3 py-2 whitespace-nowrap">{{ task.id }}</td>
              <td class="px-3 py-2 whitespace-nowrap">{{ task.title }}</td>
              <td class="px-3 py-2 whitespace-nowrap">{{ task.description }}</td>
              <td class="px-3 py-2 whitespace-nowrap">
                <span
                  class="py-1 px-2 rounded-md"
                  :class="{
                    'bg-green-200 text-green-600': task.status === 'completed',
                    'bg-yellow-200 text-yellow-600': task.status === 'pending',
                  }"
                >
                  {{ formatStatus(task.status) }}
                </span>
              </td>
              <td class="px-3 py-2 whitespace-nowrap">{{ formatDate(task.created_at) }}</td>
              <td class="px-3 py-2 whitespace-nowrap">{{ formatDate(task.updated_at) }}</td>
            </tr>
          </TransitionGroup>
        </table>
      </div>
    </div>
    <!-- Gdybym miał więcej czasu, przeniósłbym ten modal na osobny komponent -->
    <div>
      <Transition
        enter-active-class="transition-opacity duration-200"
        leave-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="showModal === 'new'"
          @click.self="closeModal"
          class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50"
        >
          <div class="bg-white p-6 rounded-xl shadow-xl min-w-[500px] max-w-[90vw]">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-xl font-semibold text-gray-900">New Task</h2>
              <button
                @click="showModal = null"
                class="cursor-pointer text-gray-500 hover:text-gray-900 transition-colors duration-300 text-2xl"
              >
                &times;
              </button>
            </div>
            <TaskForm @save="addTask" :error="errorMessage" />
          </div>
        </div>
      </Transition>

      <Transition
        enter-active-class="transition-opacity duration-200"
        leave-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="showModal === 'edit' && selectedTask"
          @click.self="closeModal"
          class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50"
        >
          <div class="bg-white p-6 rounded-xl shadow-xl min-w-[500px] max-w-[90vw]">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-xl font-semibold text-gray-900">Edit task</h2>
              <button
                @click="showModal = null"
                class="cursor-pointer text-gray-500 hover:text-gray-900 transition-colors duration-300 text-2xl"
              >
                &times;
              </button>
            </div>
            <TaskForm @save="editTask" :task="selectedTask" />
          </div>
        </div>
      </Transition>

      <Transition
        enter-active-class="transition-opacity duration-200"
        leave-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="showModal === 'remove' && selectedTask"
          @click.self="closeModal"
          class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50"
        >
          <div class="bg-white p-6 rounded-xl shadow-xl min-w-[500px] max-w-[90vw]">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-xl font-semibold text-gray-900">
                Are you sure you want to delete this task?
              </h2>
              <button
                @click="showModal = null"
                class="cursor-pointer text-gray-500 hover:text-gray-900 transition-colors duration-300 text-2xl"
              >
                &times;
              </button>
            </div>
            <div class="mb-4 text-gray-700">
              {{ selectedTask ? selectedTask.title : '' }}
            </div>
            <div class="flex justify-end gap-2">
              <button
                @click="showModal = null"
                class="rounded-md text-sm bg-gray-300 font-medium text-black px-4 py-2 hover:cursor-pointer hover:bg-white hover:text-gray-300 hover:border-gray-300 border-2 border-gray-300 transition-colors duration-300"
              >
                No
              </button>
              <button
                @click="removeTask"
                class="rounded-md text-sm bg-red-600 font-medium text-white px-4 py-2 hover:cursor-pointer hover:bg-white hover:text-red-600 hover:border-red-600 border-2 border-red-600 transition-colors duration-300"
              >
                Yes
              </button>
            </div>
          </div>
        </div>
      </Transition>

      <Transition
        enter-active-class="transition-opacity duration-200"
        leave-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="showModal === 'markStatus' && selectedTask"
          @click.self="closeModal"
          class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50"
        >
          <div class="bg-white p-6 rounded-xl shadow-xl min-w-[500px] max-w-[90vw]">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-xl font-semibold text-gray-900">
                Are you sure to mark this task as completed?
              </h2>
              <button
                @click="showModal = null"
                class="cursor-pointer text-gray-500 hover:text-gray-900 transition-colors duration-300 text-2xl"
              >
                &times;
              </button>
            </div>
            <div class="mb-4 text-gray-700">
              {{ selectedTask ? selectedTask.title : '' }}
            </div>
            <div class="flex justify-end gap-2">
              <button
                @click="showModal = null"
                class="rounded-md text-sm bg-gray-300 font-medium text-black px-4 py-2 hover:cursor-pointer hover:bg-white hover:text-gray-300 hover:border-gray-300 border-2 border-gray-300 transition-colors duration-300"
              >
                No
              </button>
              <button
                @click="editTask({ ...selectedTask, status: 'completed' })"
                class="rounded-md text-sm bg-gray-600 font-medium text-white px-4 py-2 hover:cursor-pointer hover:bg-white hover:text-gray-600 hover:border-gray-600 border-2 border-gray-600 transition-colors duration-300"
              >
                Yes
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </div>
</template>
