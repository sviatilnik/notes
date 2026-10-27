<script setup>
import { computed, ref } from 'vue'
import { Modal } from 'bootstrap'

defineProps({
  saving: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['save'])

const form = ref({ title: '', content: '' })
const editing = ref(false)
const modalEl = ref(null)

const heading = computed(() => (editing.value ? 'Редактирование' : 'Новая заметка'))
const submitLabel = computed(() => (editing.value ? 'Сохранить' : 'Создать'))

function modal() {
  return modalEl.value ? Modal.getOrCreateInstance(modalEl.value) : null
}

function openCreate() {
  editing.value = false
  form.value = { title: '', content: '' }
  modal()?.show()
}

function openEdit(note) {
  editing.value = true
  form.value = { title: note.title, content: note.content }
  modal()?.show()
}

function hide() {
  modal()?.hide()
}

function submit() {
  emit('save', {
    title: form.value.title.trim(),
    content: form.value.content.trim(),
  })
}

defineExpose({ openCreate, openEdit, hide })
</script>

<template>
  <div ref="modalEl" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form @submit.prevent="submit">
          <div class="modal-header">
            <h5 class="modal-title">{{ heading }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label" for="note-title">Заголовок</label>
              <input
                id="note-title"
                v-model="form.title"
                class="form-control"
                maxlength="255"
                required
                placeholder="Название заметки"
              />
            </div>
            <div>
              <label class="form-label" for="note-content">Текст</label>
              <textarea
                id="note-content"
                v-model="form.content"
                class="form-control"
                required
                rows="8"
                placeholder="Содержание"
              ></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
            <button class="btn btn-primary" type="submit" :disabled="saving">{{ submitLabel }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
