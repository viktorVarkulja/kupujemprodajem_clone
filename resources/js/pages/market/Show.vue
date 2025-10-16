<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'

type Image = { id:number; path:string; is_cover:boolean; position:number }
type Category = { id:number; name:string }
type Ad = {
  id:number; slug:string; title:string; description:string; price:string; currency:string;
  city:string|null; phone:string|null; condition:string; delivery_options?: string[] | null;
  category: Category; images: Image[]
}

const props = defineProps<{ ad: Ad }>()

const cover = computed(() => props.ad.images.find(i => i.is_cover) || props.ad.images[0])
</script>

<template>
  <div class="container mx-auto p-4 space-y-6 max-w-5xl">
    <div class="flex items-center justify-between">
      <Link href="/market"><Button variant="outline">Back</Button></Link>
      <div class="text-sm text-muted-foreground">{{ ad.category?.name }}</div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="md:col-span-2 space-y-4">
        <div class="aspect-video bg-muted overflow-hidden rounded">
          <img v-if="cover" :src="`/storage/${cover.path}`" class="w-full h-full object-cover" alt="" />
        </div>
        <div class="grid grid-cols-6 gap-2">
          <img v-for="img in ad.images" :key="img.id" :src="`/storage/${img.path}`" class="h-16 w-full object-cover rounded" />
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
</template>

