<script setup>
defineProps({
  note: {
    type: Object,
    required: true,
  },
  selected: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['toggle', 'edit', 'remove'])

function formatDate(value) {
  return new Date(value).toLocaleString('ru-RU', {
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<template>
  <div class="list-group-item" :class="{ 'list-group-item-primary': selected }">
    <button class="btn btn-link text-decoration-none text-start text-reset w-100 p-0" type="button" @click="$emit('toggle')">
      <span class="d-block fw-semibold">{{ note.title }}</span>
      <span class="d-block small text-secondary text-truncate">{{ note.content }}</span>
      <span class="d-block small text-secondary">{{ formatDate(note.updatedAt) }}</span>
    </button>
    <div v-if="selected" class="d-flex gap-2 mt-3">
      <button class="btn btn-sm btn-outline-primary" type="button" @click="$emit('edit')">Редактировать</button>
      <button class="btn btn-sm btn-outline-danger" type="button" @click="$emit('remove')">Удалить</button>
    </div>
  </div>
</template>
