<script setup>
import { ref } from 'vue'
import { Modal } from 'bootstrap'

defineProps({
  saving: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['confirm'])

const modalEl = ref(null)

function modal() {
  return modalEl.value ? Modal.getOrCreateInstance(modalEl.value) : null
}

function open() {
  modal()?.show()
}

function hide() {
  modal()?.hide()
}

defineExpose({ open, hide })
</script>

<template>
  <div ref="modalEl" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Удаление заметки</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
        </div>
        <div class="modal-body">Удалить эту заметку?</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
          <button type="button" class="btn btn-danger" :disabled="saving" @click="$emit('confirm')">Удалить</button>
        </div>
      </div>
    </div>
  </div>
</template>
