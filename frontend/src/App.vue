<script setup>
import NoteList from './components/NoteList.vue'
</script>

<template>
  <div class="min-vh-100 bg-body-tertiary">
    <nav class="navbar navbar-expand navbar-dark bg-dark">
      <div class="container">
        <span class="navbar-brand mb-0 h1">Заметки</span>
        <a class="btn btn-outline-light btn-sm" href="/api/doc" target="_blank" rel="noreferrer">Swagger</a>
      </div>
    </nav>

    <NoteList />
  </div>
</template>
