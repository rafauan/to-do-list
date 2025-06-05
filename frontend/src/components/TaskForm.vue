<script setup>
import { ref, watch, defineProps, defineEmits } from 'vue'

const props = defineProps({
  task: {
    type: Object,
    default: null,
  },
  error: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['save'])

const title = ref('')
const description = ref('')

watch(
  () => props.task,
  (task) => {
    if (task) {
      title.value = task.title
      description.value = task.description
    } else {
      title.value = ''
      description.value = ''
    }
  },
  { immediate: true },
)

const handleSubmit = () => {
  emit('save', {
    title: title.value,
    description: description.value,
  })
}
</script>

<template>
  <form @submit.prevent="handleSubmit">
    <div class="mb-5">
      <label for="title" class="block mb-1 text-sm font-medium text-gray-900">Title</label>
      <input
        v-model="title"
        type="text"
        id="title"
        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5"
        placeholder="Enter task title"
        required
      />
    </div>

    <div class="mb-5">
      <label for="title" class="block mb-1 text-sm font-medium text-gray-900">Description</label>
      <textarea
        v-model="description"
        id="description"
        rows="4"
        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5"
        placeholder="Enter task description"
        required
      ></textarea>
    </div>

    <div v-if="error" class="mb-5 text-red-600 text-sm">
      {{ error }}
    </div>

    <button
      type="submit"
      class="rounded-md text-sm bg-gray-600 font-medium text-white px-4 py-2 hover:cursor-pointer hover:bg-white hover:text-gray-600 hover:border-gray-600 border-2 border-gray-600 transition-colors duration-300"
    >
      Save
    </button>
  </form>
</template>
