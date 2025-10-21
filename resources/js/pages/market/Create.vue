<script setup lang="ts">
import {  ref, computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue'

interface Category { id: number; name: string; slug: string; parent_id: number | null }
const props = defineProps<{ categories: Category[] }>()

const form = useForm({
  title: '',
  description: '',
  category_id: '' as number | '' ,
  price: '' as string | number,
  currency: 'RSD',
  city: '',
  phone: '',
  condition: 'used',
  delivery_options: [] as string[],
  is_negotiable: false,
  images: [] as File[],
  // cover by index (1-based on server)
  cover_image_index: null as number | null,
})

const errors = ref<Record<string, string[] | string>>({})
const submitting = ref(false)

function onFileChange(e: Event) {
  const input = e.target as HTMLInputElement
  if (input.files) {
    form.images = Array.from(input.files)
    // default cover: first image
    form.cover_image_index = form.images.length ? 1 : null
  }
}

function categoryLabel(c: Category) {
  if (!c.parent_id) return c.name
  const p = props.categories.find(x => x.id === c.parent_id)
  return p ? `${p.name} / ${c.name}` : c.name
}

async function submit() {
  submitting.value = true
  errors.value = {}
  form.transform((data) => data)
  form.post('/listings', {
    forceFormData: true,
    onError: (err) => { errors.value = err },
    onFinish: () => { submitting.value = false },
    onSuccess: () => {
      form.reset('title','description','category_id','price','currency','city','phone','condition','delivery_options','is_negotiable','images')
    }
  })
}

// Local previews and cover selection
const previews = computed(() => form.images.map(f => ({ url: URL.createObjectURL(f), name: f.name })))
function setCover(idx: number) {
  form.cover_image_index = idx + 1
}
function removeSelected(idx: number) {
  const files = [...form.images]
  files.splice(idx, 1)
  form.images = files
  // Adjust cover if needed
  if (!files.length) form.cover_image_index = null
  else if (!form.cover_image_index || form.cover_image_index > files.length) form.cover_image_index = 1
}
</script>

<template>
  <AppHeaderLayout>
  <div class="container mx-auto p-4 space-y-6 max-w-3xl">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold">Kreiraj oglas</h1>
      <Link href="/market"><Button variant="outline">Nazad</Button></Link>
    </div>

    <div class="grid gap-4">
      <div>
        <Label for="title">Naslov</Label>
        <Input id="title" v-model="form.title" />
        <div v-if="errors.title" class="text-sm text-red-500">{{ errors.title }}</div>
      </div>
      <div>
        <Label for="description">Opis</Label>
        <textarea id="description" v-model="form.description" class="w-full border rounded p-2 min-h-32" />
        <div v-if="errors.description" class="text-sm text-red-500">{{ errors.description }}</div>
      </div>
      <div>
        <Label for="category">Kategorija</Label>
        <select id="category" v-model="form.category_id" class="w-full border rounded h-10 px-3 text-sm">
          <option value="">Izaberite kategoriju</option>
          <option v-for="c in props.categories" :key="c.id" :value="c.id">{{ categoryLabel(c) }}</option>
        </select>
        <div v-if="errors.category_id" class="text-sm text-red-500">{{ errors.category_id }}</div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
          <Label>Cena</Label>
          <Input v-model="form.price" type="number" min="0" step="0.01" />
          <div v-if="errors.price" class="text-sm text-red-500">{{ errors.price }}</div>
        </div>
        <div>
          <Label>Valuta</Label>
          <select v-model="form.currency" class="w-full border rounded h-10 px-3 text-sm">
            <option value="RSD">RSD</option>
            <option value="EUR">EUR</option>
            <option value="USD">USD</option>
          </select>
        </div>
        <div class="flex items-center gap-2 mt-6">
          <input id="neg" type="checkbox" v-model="form.is_negotiable" class="h-4 w-4" />
          <Label for="neg">Po dogovoru</Label>
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
          <Label>Grad</Label>
          <Input v-model="form.city" placeholder="Beograd" />
        </div>
        <div>
          <Label>Telefon</Label>
          <Input v-model="form.phone" placeholder="06x xxx xxxx" />
        </div>
        <div>
          <Label>Stanje</Label>
          <select v-model="form.condition" class="w-full border rounded h-10 px-3 text-sm">
            <option value="new">Novo</option>
            <option value="like_new">Kao novo</option>
            <option value="used">Polovno</option>
            <option value="for_parts">Za delove</option>
          </select>
        </div>
      </div>

      <div>
        <Label>Način isporuke</Label>
        <div class="flex flex-wrap gap-4 mt-2">
          <label class="inline-flex items-center gap-2"><input type="checkbox" value="pickup" v-model="form.delivery_options" /> Preuzimanje lično</label>
          <label class="inline-flex items-center gap-2"><input type="checkbox" value="courier" v-model="form.delivery_options" /> Kurirska služba</label>
          <label class="inline-flex items-center gap-2"><input type="checkbox" value="cod" v-model="form.delivery_options" /> Plaćanje pouzećem</label>
        </div>
      </div>

      <div>
        <Label for="images">Slike</Label>
        <input id="images" type="file" accept=".jpg,.jpeg,.png,.webp" multiple @change="onFileChange" class="block w-full text-sm" />
        <div class="text-sm text-muted-foreground mt-1">Do 10 slika, max 5MB po slici.</div>
        <div v-if="errors.images" class="text-sm text-red-500">{{ errors.images }}</div>
      </div>

      <div v-if="previews.length" class="space-y-2">
        <Label>Izabrane slike</Label>
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
          <div v-for="(p, idx) in previews" :key="p.url" class="border rounded p-2 space-y-2">
            <img :src="p.url" class="w-full h-24 object-cover rounded" alt="" />
            <div class="flex items-center gap-2 text-sm">
              <input type="radio" name="cover-new" :checked="form.cover_image_index === idx + 1" @change="setCover(idx)" :id="`cvn-${idx}`" />
              <Label :for="`cvn-${idx}`">Naslovna</Label>
            </div>
            <div>
              <Button variant="destructive" size="sm" @click.prevent="removeSelected(idx)">Ukloni</Button>
            </div>
          </div>
        </div>
      </div>

      <div>
        <Button :disabled="submitting" @click="submit">Objavi</Button>
      </div>
    </div>
  </div>
  </AppHeaderLayout>
</template>
