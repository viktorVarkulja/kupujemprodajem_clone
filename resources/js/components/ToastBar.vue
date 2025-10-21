<script setup lang="ts">
import { onMounted, onBeforeUnmount, reactive } from 'vue'

type Toast = { id:number; message:string; type?: 'success'|'error'|'info' }
const state = reactive({ toasts: [] as Toast[] })
let nextId = 1

function addToast(message: string, type: 'success'|'error'|'info' = 'info') {
  const id = nextId++
  state.toasts.push({ id, message, type })
  setTimeout(() => removeToast(id), 3000)
}
function removeToast(id: number) {
  const i = state.toasts.findIndex(t => t.id === id)
  if (i !== -1) state.toasts.splice(i, 1)
}

function onEvent(e: Event) {
  const detail = (e as CustomEvent).detail || {}
  addToast(detail.message || String(detail) || 'OK', detail.type || 'info')
}

onMounted(() => window.addEventListener('toast', onEvent as any))
onBeforeUnmount(() => window.removeEventListener('toast', onEvent as any))
</script>

<template>
  <div class="fixed z-50 bottom-4 left-1/2 -translate-x-1/2 space-y-2 w-[92vw] max-w-md">
    <div v-for="t in state.toasts" :key="t.id" class="px-3 py-2 rounded shadow text-sm"
      :class="{
        'bg-emerald-600 text-white': t.type === 'success',
        'bg-red-600 text-white': t.type === 'error',
        'bg-zinc-800 text-white': !t.type || t.type === 'info',
      }">
      {{ t.message }}
    </div>
  </div>
  
</template>

