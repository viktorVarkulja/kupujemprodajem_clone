<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue'

type Ad = {
  id: number
  slug: string
  title: string
  price: string
  currency: 'RSD' | 'EUR' | 'USD'
  city: string | null
  status: string
  category: { id:number; name:string }
  cover_image?: { id:number } | null
}

const ads = ref<Ad[]>([])
const loading = ref(false)

async function fetchMine() {
  loading.value = true
  try {
    const res = await fetch('/listings?mine=1&per_page=24')
    const data = await res.json()
    ads.value = data.data
  } finally {
    loading.value = false
  }
}

function destroyAd(slug: string) {
  if (!confirm('Delete this listing?')) return
  router.delete(`/listings/${slug}`, {
    preserveScroll: true,
    onSuccess: () => fetchMine(),
  })
}

onMounted(fetchMine)
</script>

<template>
  <AppHeaderLayout>
    <div class="container mx-auto p-4 space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">My Listings</h1>
        <Link href="/listing/create"><Button>Create Ad</Button></Link>
      </div>

      <div v-if="loading">Loading...</div>
      <div v-else-if="ads.length === 0" class="text-sm text-muted-foreground">You have no listings yet.</div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div v-for="ad in ads" :key="ad.id" class="border rounded overflow-hidden">
          <Link :href="`/listing/${ad.slug}/view`">
            <div class="aspect-video bg-muted overflow-hidden">
              <img v-if="ad.cover_image?.id" :src="`/media/${ad.cover_image.id}`" class="w-full h-full object-cover" alt="" />
            </div>
          </Link>
          <div class="p-3 space-y-1">
            <div class="font-medium line-clamp-1">{{ ad.title }}</div>
            <div class="text-sm text-muted-foreground">{{ ad.category?.name }} • {{ ad.status }}</div>
            <div class="flex justify-between text-sm">
              <div class="font-semibold">{{ Number(ad.price).toLocaleString() }} {{ ad.currency }}</div>
              <div class="text-muted-foreground">{{ ad.city }}</div>
            </div>
            <div class="flex gap-2 pt-2">
              <Link :href="`/listing/${ad.slug}/view`"><Button size="sm" variant="outline">View</Button></Link>
              <Link :href="`/listing/${ad.slug}/edit`"><Button size="sm">Edit</Button></Link>
              <Button size="sm" variant="destructive" @click="destroyAd(ad.slug)">Delete</Button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppHeaderLayout>
</template>
