<script setup lang="ts">
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue'

interface Category { id: number; name: string; slug: string; parent_id: number | null }
type Image = { id:number; path:string; is_cover:boolean; position:number }
type Ad = {
  id:number; slug:string; title:string; description:string; price:number|string; currency:string;
  city:string|null; phone:string|null; condition:string; delivery_options?: string[] | null;
  category_id:number; images: Image[]
}

const props = defineProps<{ categories: Category[]; ad: Ad }>()

const form = useForm({
  title: props.ad.title,
  description: props.ad.description,
  category_id: props.ad.category_id,
  price: String(props.ad.price ?? ''),
  currency: props.ad.currency,
  city: props.ad.city ?? '',
  phone: props.ad.phone ?? '',
  condition: props.ad.condition,
  delivery_options: props.ad.delivery_options ?? [],
  is_negotiable: false,
  images: [] as File[],
  remove_image_ids: [] as number[],
  cover_image_id: (props.ad.images.find(i => i.is_cover)?.id ?? null) as number | null,
})

const errors = ref<Record<string, string[] | string>>({})
const submitting = ref(false)

function onFileChange(e: Event) {
  const input = e.target as HTMLInputElement
  if (input.files) {
    form.images = Array.from(input.files)
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
  form.transform((data) => ({ ...data, _method: 'PUT' }))
  form.post(`/listings/${props.ad.slug}`, {
    forceFormData: true,
    onError: (err) => { errors.value = err },
    onFinish: () => { submitting.value = false },
  })
}
</script>

<template>
  <AppHeaderLayout>
    <div class="container mx-auto p-4 space-y-6 max-w-3xl">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Uredi oglas</h1>
        <Link :href="`/listing/${props.ad.slug}/view`"><Button variant="outline">Nazad</Button></Link>
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
          <Label for="images">Dodaj slike</Label>
          <input id="images" type="file" accept=".jpg,.jpeg,.png,.webp" multiple @change="onFileChange" class="block w-full text-sm" />
          <div class="text-sm text-muted-foreground mt-1">Do 10 slika, max 5MB po slici.</div>
        </div>

        <div v-if="props.ad.images?.length" class="space-y-2">
          <Label>Postojeće slike</Label>
          <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
            <div v-for="img in props.ad.images" :key="img.id" class="border rounded p-2 space-y-2">
              <img :src="`/media/${img.id}`" class="w-full h-24 object-cover rounded" alt="" />
              <div class="flex items-center gap-2 text-sm">
                <input type="checkbox" :value="img.id" v-model="form.remove_image_ids" id="rm-{{img.id}}" />
                <Label :for="`rm-${img.id}`">Ukloni</Label>
              </div>
              <div class="flex items-center gap-2 text-sm">
                <input type="radio" name="cover" :value="img.id" v-model="form.cover_image_id" :disabled="form.remove_image_ids.includes(img.id)" id="cv-{{img.id}}" />
                <Label :for="`cv-${img.id}`">Naslovna</Label>
              </div>
            </div>
          </div>
        </div>

        <div>
          <Button :disabled="submitting" @click="submit">Sačuvaj izmene</Button>
        </div>
      </div>
    </div>
  </AppHeaderLayout>
</template>
