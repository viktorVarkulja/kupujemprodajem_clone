<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const auth = computed(() => (page.props as any).auth || {})
const siteName = computed(() => (page.props as any).name || 'Oglasi')
</script>

<template>
  <header class="border-b border-sidebar-border/80">
    <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl w-full">
      <Link href="/" class="text-lg font-semibold">{{ siteName }}</Link>

      <div class="ml-auto flex items-center gap-2">
        <template v-if="auth?.user">
          <span class="hidden sm:inline text-sm">Dobrodošli, {{ auth.user.name }}</span>
          <Link href="/listing/create">
            <Button size="sm">Kreiraj oglas</Button>
          </Link>
          <Link href="/my-listings">
            <Button size="sm" variant="outline">Moji oglasi</Button>
          </Link>
          <Link href="/logout" method="post" as="button">
            <Button size="sm" variant="ghost">Odjava</Button>
          </Link>
        </template>
        <template v-else>
          <Link href="/login">
            <Button size="sm" variant="outline">Prijava</Button>
          </Link>
          <Link href="/register">
            <Button size="sm">Registracija</Button>
          </Link>
        </template>
      </div>
    </div>
  </header>
</template>
