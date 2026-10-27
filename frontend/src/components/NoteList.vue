<script setup>
import { computed, onMounted, ref } from 'vue'
import { notesApi } from '../api'
import NoteCard from './NoteCard.vue'
import NoteDeleteDialog from './NoteDeleteDialog.vue'
import NoteForm from './NoteForm.vue'

const PAGE_SIZE = 5

const notes = ref([])
const total = ref(0)
const loading = ref(false)
const loadingMore = ref(false)
const error = ref('')
const selectedId = ref(null)
const saving = ref(false)
const formRef = ref(null)
const deleteRef = ref(null)

const selected = computed(() => notes.value.find((note) => note.id === selectedId.value) || null)
const hasMore = computed(() => notes.value.length < total.value)

async function loadNotes(reset = true) {
  if (reset) {
    loading.value = true
  } else {
    loadingMore.value = true
  }
  error.value = ''

  try {
    const data = await notesApi.list({
      limit: PAGE_SIZE,
      offset: reset ? 0 : notes.value.length,
    })
    total.value = data.total
    notes.value = reset ? data.items : [...notes.value, ...data.items]
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

function toggleNote(note) {
  selectedId.value = selectedId.value === note.id ? null : note.id
}

function openCreate() {
  selectedId.value = null
  formRef.value?.openCreate()
}

function openEdit() {
  if (!selected.value) {
    return
  }
  formRef.value?.openEdit(selected.value)
}

async function saveNote(payload) {
  saving.value = true
  error.value = ''

  try {
    const saved = selectedId.value
      ? await notesApi.update(selectedId.value, payload)
      : await notesApi.create(payload)
    formRef.value?.hide()
    await loadNotes(true)
    selectedId.value = saved.id
  } catch (err) {
    error.value = err.message
  } finally {
    saving.value = false
  }
}

function askDelete() {
  if (!selectedId.value) {
    return
  }
  deleteRef.value?.open()
}

async function confirmDelete() {
  if (!selectedId.value) {
    return
  }

  saving.value = true
  error.value = ''
  try {
    deleteRef.value?.hide()
    await notesApi.remove(selectedId.value)
    selectedId.value = null
    await loadNotes(true)
  } catch (err) {
    error.value = err.message
  } finally {
    saving.value = false
  }
}

onMounted(() => loadNotes(true))
</script>

<template>
  <div class="container py-4">
    <div v-if="error" class="alert alert-danger" role="alert">{{ error }}</div>

    <div class="row justify-content-center">
      <div class="col-lg-6 col-md-8">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <span>Список</span>
            <button class="btn btn-primary btn-sm" type="button" @click="openCreate">Добавить</button>
          </div>

          <div v-if="loading" class="card-body text-center">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Загрузка…</span>
            </div>
          </div>

          <div v-else-if="notes.length" class="list-group list-group-flush">
            <NoteCard
              v-for="note in notes"
              :key="note.id"
              :note="note"
              :selected="note.id === selectedId"
              @toggle="toggleNote(note)"
              @edit="openEdit"
              @remove="askDelete"
            />
          </div>

          <div v-else-if="!error" class="card-body">
            <p class="text-secondary mb-0">Пока нет заметок. Нажмите «Добавить».</p>
          </div>

          <div v-if="hasMore" class="card-footer">
            <button
              class="btn btn-outline-primary w-100"
              type="button"
              :disabled="loadingMore"
              @click="loadNotes(false)"
            >
              <span v-if="loadingMore" class="spinner-border spinner-border-sm me-2" role="status"></span>
              Показать ещё
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <NoteForm ref="formRef" :saving="saving" @save="saveNote" />
  <NoteDeleteDialog ref="deleteRef" :saving="saving" @confirm="confirmDelete" />
</template>
