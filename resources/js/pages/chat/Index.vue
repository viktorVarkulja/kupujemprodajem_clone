<script setup lang="ts">
import { ref, onMounted, computed, nextTick } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue'
import { Button } from '@/components/ui/button'

type User = { id:number; name:string }
type Ad = { id:number; title:string; slug:string }
type Participant = { user: User, last_read_at?: string|null }
type Conversation = { id:number; ad?: Ad|null; participants: Participant[]; latest_message?: Message|null }
type Message = { id:number; body:string; created_at:string; user: User }

const getCsrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''

const loading = ref(false)
const conversations = ref<Conversation[]>([])
const selected = ref<Conversation | null>(null)
const messages = ref<Message[]>([])
const msgBody = ref('')
const sending = ref(false)

const fmtTime = (iso: string) => {
  const d = new Date(iso)
  return d.toLocaleTimeString('sr-RS', { hour: '2-digit', minute: '2-digit', hour12: false })
}

const titleFor = (c: Conversation) => {
  if (c.ad?.title) return c.ad.title
  // Fallback: imena učesnika
  const names = c.participants?.map(p => p.user.name) || []
  return names.join(', ')
}

async function loadConversations() {
  loading.value = true
  try {
    const res = await fetch('/conversations', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
    const json = await res.json()
    // Laravel paginator uses data
    conversations.value = json.data || []
  } finally {
    loading.value = false
  }
}

async function openConversation(c: Conversation) {
  selected.value = c
  messages.value = []
  const res = await fetch(`/conversations/${c.id}`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
  const json = await res.json()
  // messages returned paginated in DESC; show ascending in UI
  const page = json.messages
  const list: Message[] = (page?.data || [])
  messages.value = list.reverse()
  // mark as read
  fetch(`/conversations/${c.id}/read`, { method: 'POST', headers: { 'X-CSRF-TOKEN': getCsrf(), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, credentials: 'same-origin' }).catch(() => {})
  await nextTick()
  scrollToBottom()
}

function scrollToBottom() {
  const el = document.getElementById('chat-scroll')
  if (el) el.scrollTop = el.scrollHeight
}

async function send() {
  if (!selected.value || !msgBody.value.trim()) return
  sending.value = true
  try {
    const res = await fetch(`/conversations/${selected.value.id}/messages`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrf(),
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ body: msgBody.value.trim() }),
      credentials: 'same-origin',
    })
    if (!res.ok) throw new Error('Greška pri slanju')
    const m: Message = await res.json()
    messages.value.push(m)
    msgBody.value = ''
    await nextTick()
    scrollToBottom()
  } catch(e) {
    alert('Nije moguće poslati poruku.')
  } finally {
    sending.value = false
  }
}

onMounted(loadConversations)
</script>

<template>
  <AppHeaderLayout>
    <div class="container mx-auto p-4 max-w-6xl">
      <h1 class="text-2xl font-semibold mb-4">Razgovori</h1>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="border rounded p-2 md:col-span-1 h-[70vh] overflow-auto">
          <div v-if="loading" class="text-sm text-muted-foreground p-2">Učitavanje...</div>
          <div v-else>
            <button
              v-for="c in conversations"
              :key="c.id"
              type="button"
              @click="openConversation(c)"
              class="w-full text-left px-3 py-2 rounded hover:bg-muted/60 border-b"
            >
              <div class="font-medium truncate">{{ titleFor(c) }}</div>
              <div class="text-xs text-muted-foreground truncate" v-if="c.latest_message">{{ c.latest_message.body }}</div>
            </button>
            <div v-if="!conversations.length" class="text-sm text-muted-foreground p-2">Nema razgovora.</div>
          </div>
        </div>
        <div class="md:col-span-2 border rounded flex flex-col h-[70vh]">
          <div v-if="!selected" class="m-auto text-muted-foreground">Izaberite razgovor</div>
          <template v-else>
            <div class="px-3 py-2 border-b font-medium">{{ titleFor(selected) }}</div>
            <div id="chat-scroll" class="flex-1 overflow-auto p-3 space-y-3 bg-muted/20">
              <div v-for="m in messages" :key="m.id" class="bg-white dark:bg-zinc-900 rounded p-2 shadow-sm">
                <div class="text-xs text-muted-foreground">{{ m.user.name }} · {{ fmtTime(m.created_at) }}</div>
                <div class="text-sm whitespace-pre-wrap">{{ m.body }}</div>
              </div>
            </div>
            <div class="p-2 border-t space-y-2">
              <textarea v-model="msgBody" class="w-full border rounded p-2 text-sm min-h-20" placeholder="Napišite poruku..."></textarea>
              <div class="flex justify-end">
                <Button :disabled="sending || !msgBody.trim()" @click="send">Pošalji</Button>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </AppHeaderLayout>
</template>
