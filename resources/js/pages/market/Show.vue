<script setup lang="ts">
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue'

type Image = { id:number; path:string; is_cover:boolean; position:number }
type Category = { id:number; name:string }
type Ad = {
  id:number; slug:string; title:string; description:string; price:string; currency:string;
  city:string|null; phone:string|null; condition:string; delivery_options?: string[] | null;
  category: Category; images: Image[]
}

const props = defineProps<{ ad: Ad }>()

const selected = ref<Image | null>(null)
const cover = computed(() => selected.value || props.ad.images.find(i => i.is_cover) || props.ad.images[0])

const currentIndex = computed(() => {
  if (!cover.value) return 0
  return props.ad.images.findIndex(i => i.id === cover.value!.id)
})

function setByIndex(index: number) {
  if (!props.ad.images.length) return
  const len = props.ad.images.length
  const idx = ((index % len) + len) % len
  selected.value = props.ad.images[idx]
}

function prevImage() { setByIndex(currentIndex.value - 1) }
function nextImage() { setByIndex(currentIndex.value + 1) }

// Zoom overlay state
const isZoomOpen = ref(false)
const scale = ref(1)
const translateX = ref(0)
const translateY = ref(0)
const dragging = ref(false)
let startX = 0, startY = 0, startTX = 0, startTY = 0

function openZoom() {
  isZoomOpen.value = true
  scale.value = 1
  translateX.value = 0
  translateY.value = 0
}
function closeZoom() {
  isZoomOpen.value = false
}
function zoomIn(step = 0.2) { scale.value = Math.min(4, +(scale.value + step).toFixed(3)) }
function zoomOut(step = 0.2) { scale.value = Math.max(1, +(scale.value - step).toFixed(3)) }
function resetZoom() { scale.value = 1; translateX.value = 0; translateY.value = 0 }

function onWheel(e: WheelEvent) {
  e.preventDefault()
  if (e.deltaY < 0) zoomIn(0.15)
  else zoomOut(0.15)
}
function startDrag(e: MouseEvent) {
  dragging.value = true
  startX = e.clientX
  startY = e.clientY
  startTX = translateX.value
  startTY = translateY.value
}
function onDrag(e: MouseEvent) {
  if (!dragging.value) return
  translateX.value = startTX + (e.clientX - startX)
  translateY.value = startTY + (e.clientY - startY)
}
function endDrag() { dragging.value = false }

function onKey(e: KeyboardEvent) {
  if (!isZoomOpen.value) return
  if (e.key === 'Escape') closeZoom()
  if (e.key === 'ArrowRight') nextImage()
  if (e.key === 'ArrowLeft') prevImage()
}

onMounted(() => window.addEventListener('keydown', onKey))
onBeforeUnmount(() => window.removeEventListener('keydown', onKey))
</script>

<template>
  <AppHeaderLayout>
  <div class="container mx-auto p-4 space-y-6 max-w-5xl">
    <div class="flex items-center justify-between">
      <Link href="/market"><Button variant="outline">Back</Button></Link>
      <div class="text-sm text-muted-foreground">{{ ad.category?.name }}</div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="md:col-span-2 space-y-4">
        <div class="aspect-video bg-muted overflow-hidden rounded relative">
          <img
            v-if="cover"
            :src="`/media/${cover.id}`"
            class="w-full h-full object-contain cursor-zoom-in select-none"
            alt="Selected image"
            @click="openZoom"
            draggable="false"
          />
          <!-- Prev/Next arrows -->
          <button
            type="button"
            class="absolute inset-y-0 left-0 px-3 text-2xl text-white/90 hover:text-white bg-black/20 hover:bg-black/30 transition flex items-center"
            @click.stop="prevImage"
            aria-label="Previous image"
          >‹</button>
          <button
            type="button"
            class="absolute inset-y-0 right-0 px-3 text-2xl text-white/90 hover:text-white bg-black/20 hover:bg-black/30 transition flex items-center"
            @click.stop="nextImage"
            aria-label="Next image"
          >›</button>
        </div>
        <div class="grid grid-cols-6 gap-2">
          <img
            v-for="img in ad.images"
            :key="img.id"
            :src="`/media/${img.id}`"
            @click="selected = img"
            :class="[
              'h-16 w-full object-cover rounded cursor-pointer transition ring-1',
              cover && cover.id === img.id ? 'ring-primary' : 'ring-transparent hover:ring-primary/50'
            ]"
            alt="Thumbnail"
          />
        </div>
        <div>
          <h1 class="text-2xl font-semibold">{{ ad.title }}</h1>
        </div>
        <div class="prose max-w-none" v-html="ad.description.replace(/\n/g, '<br/>')"></div>
      </div>
      <div class="space-y-4">
        <div class="border rounded p-4 space-y-2">
          <div class="text-2xl font-bold">{{ Number(ad.price).toLocaleString() }} {{ ad.currency }}</div>
          <div class="text-muted-foreground">Condition: {{ ad.condition }}</div>
          <div class="text-muted-foreground">City: {{ ad.city }}</div>
          <div class="text-muted-foreground" v-if="ad.delivery_options?.length">Delivery: {{ ad.delivery_options.join(', ') }}</div>
        </div>
        <div class="border rounded p-4 space-y-2">
          <div class="font-medium">Contact</div>
          <div>Phone: <span class="font-mono">{{ ad.phone || '—' }}</span></div>
          <Button as-child>
            <a :href="ad.phone ? `tel:${ad.phone}` : '#'" :aria-disabled="!ad.phone">Call Seller</a>
          </Button>
        </div>
      </div>
    </div>
  </div>
  </AppHeaderLayout>

  <!-- Zoom Overlay -->
  <div v-if="isZoomOpen" class="fixed inset-0 z-50 bg-black/90">
    <div class="absolute inset-0 flex flex-col">
      <div class="flex items-center justify-between p-3">
        <div class="text-white/80 text-sm">Use mouse wheel to zoom; drag to pan</div>
        <div class="flex items-center gap-2">
          <button class="px-3 py-1 rounded bg-white/10 text-white hover:bg-white/20" @click="zoomOut()" aria-label="Zoom out">-</button>
          <button class="px-3 py-1 rounded bg-white/10 text-white hover:bg-white/20" @click="resetZoom()" aria-label="Reset zoom">100%</button>
          <button class="px-3 py-1 rounded bg-white/10 text-white hover:bg-white/20" @click="zoomIn()" aria-label="Zoom in">+</button>
          <button class="ml-2 px-3 py-1 rounded bg-white/10 text-white hover:bg-white/20" @click="closeZoom" aria-label="Close">✕</button>
        </div>
      </div>
      <div class="relative flex-1 overflow-hidden select-none">
        <div
          class="absolute inset-0 overflow-hidden cursor-grab active:cursor-grabbing"
          @wheel.prevent="onWheel"
          @mousedown="startDrag"
          @mousemove="onDrag"
          @mouseup="endDrag"
          @mouseleave="endDrag"
        >
          <img
            v-if="cover"
            :src="`/media/${cover.id}`"
            class="absolute top-1/2 left-1/2 max-w-none pointer-events-none"
            :style="{
              transform: `translate(calc(-50% + ${translateX}px), calc(-50% + ${translateY}px)) scale(${scale})`
            }"
            alt="Zoomed image"
            draggable="false"
          />
        </div>
        <!-- Prev/Next in overlay -->
        <button
          type="button"
          class="absolute inset-y-0 left-0 px-4 text-3xl text-white/90 hover:text-white bg-black/20 hover:bg-black/30 transition flex items-center"
          @click="prevImage"
          aria-label="Previous image"
        >‹</button>
        <button
          type="button"
          class="absolute inset-y-0 right-0 px-4 text-3xl text-white/90 hover:text-white bg-black/20 hover:bg-black/30 transition flex items-center"
          @click="nextImage"
          aria-label="Next image"
        >›</button>
      </div>
      <div class="p-3 text-center text-white/70 text-sm">Image {{ currentIndex + 1 }} / {{ ad.images.length }}</div>
    </div>
  </div>
</template>
