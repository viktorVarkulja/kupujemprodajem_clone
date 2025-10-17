<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue'

interface Category { id: number; name: string; slug: string; parent_id: number | null }
const props = defineProps<{ categories: Category[] }>()

type Ad = {
  id: number
  slug: string
  title: string
  price: string
  currency: 'RSD' | 'EUR' | 'USD'
  city: string | null
  condition: string
  category: { id:number; name:string }
  cover_image?: { path: string } | null
}

const ads = ref<Ad[]>([])
const loading = ref(false)
const currentPage = ref(1)
const lastPage = ref(1)

const q = ref('')
const category_id = ref<number | ''>('')
const price_min = ref<string>('')
const price_max = ref<string>('')
const currency = ref<string>('')
const city = ref<string>('')
const condition = ref<string>('')
const sort = ref<string>('')

const categoryOptions = computed(() => {
  const parents = props.categories.filter(c => !c.parent_id)
  const children = (pid: number) => props.categories.filter(c => c.parent_id === pid)
  const list: { id:number; label:string }[] = []
  for (const p of parents) {
    list.push({ id: p.id, label: p.name })
    for (const ch of children(p.id)) list.push({ id: ch.id, label: `${p.name} / ${ch.name}` })
  }
  return list
})

async function fetchAds() {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (q.value) params.set('q', q.value)
    if (category_id.value) params.set('category_id', String(category_id.value))
    if (price_min.value) params.set('price_min', price_min.value)
    if (price_max.value) params.set('price_max', price_max.value)
    if (currency.value) params.set('currency', currency.value)
    if (city.value) params.set('city', city.value)
    if (condition.value) params.set('condition', condition.value)
    if (sort.value) params.set('sort', sort.value)
    params.set('page', String(currentPage.value))
    params.set('per_page', '12')
    const res = await fetch(`/listings?${params.toString()}`)
    const data = await res.json()
    ads.value = data.data
    lastPage.value = data.last_page ?? 1
  } finally {
    loading.value = false
  }
}

watch([q, category_id, price_min, price_max, currency, city, condition, sort], () => {
  currentPage.value = 1
  fetchAds()
})

watch(currentPage, () => fetchAds())
onMounted(() => fetchAds())
</script>

<template>
  <AppHeaderLayout>
  <div class="container mx-auto p-4 space-y-6">

    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold">Oglasi</h1>
      <Link href="/listing/create">
        <Button>Kreiraj oglas</Button>
      </Link>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
      <div class="md:col-span-2">
        <Label for="q">Pretraga</Label>
        <Input id="q" v-model="q" placeholder="Pretraži naslov ili opis" />
      </div>
      <div>
        <Label for="category">Kategorija</Label>
        <select id="category" v-model="category_id" class="w-full border rounded h-10 px-3 text-sm">
          <option value="">Sve</option>
          <option v-for="opt in categoryOptions" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
        </select>
      </div>
      <div>
        <Label for="currency">Valuta</Label>
        <select id="currency" v-model="currency" class="w-full border rounded h-10 px-3 text-sm">
          <option value="">Bilo koja</option>
          <option value="RSD">RSD</option>
          <option value="EUR">EUR</option>
          <option value="USD">USD</option>
        </select>
      </div>
      <div>
        <Label>Minimalna cena</Label>
        <Input v-model="price_min" placeholder="0" />
      </div>
      <div>
        <Label>Maksimalna cena</Label>
        <Input v-model="price_max" placeholder="" />
      </div>
      <div>
        <Label>Grad</Label>
        <Input v-model="city" placeholder="Beograd" />
      </div>
      <div>
        <Label>Stanje</Label>
        <select v-model="condition" class="w-full border rounded h-10 px-3 text-sm">
          <option value="">Bilo koje</option>
          <option value="new">Novo</option>
          <option value="like_new">Kao novo</option>
          <option value="used">Polovno</option>
          <option value="for_parts">Za delove</option>
        </select>
      </div>
      <div>
        <Label>Sortiranje</Label>
        <select v-model="sort" class="w-full border rounded h-10 px-3 text-sm">
          <option value="">Najnovije</option>
          <option value="price_asc">Cena: rastuće</option>
          <option value="price_desc">Cena: opadajuće</option>
        </select>
      </div>
      <div class="md:col-span-6">
        <Button :disabled="loading" @click="fetchAds">Primeni</Button>
      </div>
    </div>

    <div v-if="loading">Učitavanje...</div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <div v-for="ad in ads" :key="ad.id" class="border rounded overflow-hidden hover:shadow">
        <Link :href="`/listing/${ad.slug}/view`">
          <div class="aspect-video bg-muted overflow-hidden">
            <img v-if="ad.cover_image?.id" :src="`/media/${ad.cover_image.id}`" class="w-full h-full object-cover" alt="" />
          </div>
          <div class="p-3 space-y-1">
            <div class="font-medium line-clamp-1">{{ ad.title }}</div>
            <div class="text-sm text-muted-foreground">{{ ad.category?.name }}</div>
            <div class="flex justify-between text-sm">
              <div class="font-semibold">{{ Number(ad.price).toLocaleString() }} {{ ad.currency }}</div>
              <div class="text-muted-foreground">{{ ad.city }}</div>
            </div>
          </div>
        </Link>
      </div>
    </div>

    <div class="flex items-center justify-center gap-2" v-if="lastPage > 1">
      <Button variant="outline" :disabled="currentPage <= 1" @click="currentPage = currentPage - 1">Prethodna</Button>
      <span>Strana {{ currentPage }} / {{ lastPage }}</span>
      <Button variant="outline" :disabled="currentPage >= lastPage" @click="currentPage = currentPage + 1">Sledeća</Button>
    </div>
  </div>
  </AppHeaderLayout>
</template>
