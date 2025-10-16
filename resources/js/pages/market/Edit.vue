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
        <h1 class="text-2xl font-semibold">Edit Ad</h1>
        <Link :href="`/listing/${props.ad.slug}/view`"><Button variant="outline">Back</Button></Link>
      </div>

      <div class="grid gap-4">
        <div>
          <Label for="title">Title</Label>
          <Input id="title" v-model="form.title" />
          <div v-if="errors.title" class="text-sm text-red-500">{{ errors.title }}</div>
        </div>
        <div>
          <Label for="description">Description</Label>
          <textarea id="description" v-model="form.description" class="w-full border rounded p-2 min-h-32" />
          <div v-if="errors.description" class="text-sm text-red-500">{{ errors.description }}</div>
        </div>
        <div>
          <Label for="category">Category</Label>
          <select id="category" v-model="form.category_id" class="w-full border rounded h-10 px-3 text-sm">
            <option v-for="c in props.categories" :key="c.id" :value="c.id">{{ categoryLabel(c) }}</option>
          </select>
          <div v-if="errors.category_id" class="text-sm text-red-500">{{ errors.category_id }}</div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div>
            <Label>Price</Label>
            <Input v-model="form.price" type="number" min="0" step="0.01" />
            <div v-if="errors.price" class="text-sm text-red-500">{{ errors.price }}</div>
          </div>
          <div>
            <Label>Currency</Label>
            <select v-model="form.currency" class="w-full border rounded h-10 px-3 text-sm">
              <option value="RSD">RSD</option>
              <option value="EUR">EUR</option>
              <option value="USD">USD</option>
            </select>
          </div>
          <div class="flex items-center gap-2 mt-6">
            <input id="neg" type="checkbox" v-model="form.is_negotiable" class="h-4 w-4" />
            <Label for="neg">Negotiable</Label>
          </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div>
            <Label>City</Label>
            <Input v-model="form.city" placeholder="Belgrade" />
          </div>
          <div>
            <Label>Phone</Label>
            <Input v-model="form.phone" placeholder="06x xxx xxxx" />
          </div>
          <div>
            <Label>Condition</Label>
            <select v-model="form.condition" class="w-full border rounded h-10 px-3 text-sm">
              <option value="new">New</option>
              <option value="like_new">Like new</option>
              <option value="used">Used</option>
              <option value="for_parts">For parts</option>
            </select>
          </div>
        </div>

        <div>
          <Label for="images">Add Images</Label>
          <input id="images" type="file" accept="image/*" multiple @change="onFileChange" class="block w-full text-sm" />
          <div class="text-sm text-muted-foreground mt-1">Up to 10 images, max 5MB each.</div>
        </div>

        <div>
          <Button :disabled="submitting" @click="submit">Save Changes</Button>
        </div>
      </div>
    </div>
  </AppHeaderLayout>
</template>
