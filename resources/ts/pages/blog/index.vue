<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})

const router = useRouter()
const artikels = ref<any[]>([])
const loading = ref(true)

const fetchArtikels = async () => {
  try {
    const res = await axios.get('/api/artikels')
    artikels.value = res.data.data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchArtikels()
})

const formatDate = (dateString: string) => {
  const options: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'long', day: 'numeric' }
  return new Date(dateString).toLocaleDateString('id-ID', options)
}
</script>

<template>
  <div>
    <VRow justify="center">
      <VCol cols="12" md="10" lg="10">
        <h1 class="text-h3 font-weight-black mb-2 text-center text-primary">Kabar & Tips Game</h1>
        <p class="text-center text-medium-emphasis mb-10">Temukan panduan, tips trik, dan berita terbaru seputar game favoritmu di sini!</p>
        
        <VRow v-if="loading">
          <VCol cols="12" class="text-center py-12">
            <VProgressCircular indeterminate color="primary" size="48" />
          </VCol>
        </VRow>

        <VRow v-else-if="artikels.length === 0">
          <VCol cols="12" class="text-center py-12">
            <VIcon icon="ri-article-line" size="64" color="grey" class="mb-4" />
            <div class="text-h6 text-medium-emphasis">Belum ada artikel saat ini.</div>
          </VCol>
        </VRow>

        <VRow v-else>
          <VCol v-for="artikel in artikels" :key="artikel.id" cols="12" md="4">
            <VCard elevation="3" class="h-100 d-flex flex-column hover-card" @click="router.push(`/blog/${artikel.slug}`)">
              <!-- Jika ada gambar -->
              <VImg
                v-if="artikel.gambar"
                :src="artikel.gambar"
                height="200"
                cover
                class="bg-grey-lighten-2"
              ></VImg>
              <div v-else class="bg-primary-lighten-4 d-flex align-center justify-center" style="height: 200px">
                <VIcon icon="ri-image-line" size="64" color="primary" opacity="0.5" />
              </div>

              <VCardText class="flex-grow-1">
                <div class="d-flex align-center text-caption text-medium-emphasis mb-2 gap-2">
                  <span><VIcon icon="ri-calendar-line" size="small" class="mr-1" />{{ formatDate(artikel.created_at) }}</span>
                  <span><VIcon icon="ri-eye-line" size="small" class="mr-1" />{{ artikel.views }} Views</span>
                </div>
                <h2 class="text-h5 font-weight-bold mb-2">{{ artikel.judul }}</h2>
                <p class="text-body-2 text-medium-emphasis line-clamp-3">
                  {{ artikel.konten.replace(/<[^>]+>/g, '').substring(0, 150) }}...
                </p>
              </VCardText>
              
              <VCardActions class="px-4 pb-4">
                <VBtn color="primary" variant="text" :to="`/blog/${artikel.slug}`">Baca Selengkapnya <VIcon icon="ri-arrow-right-s-line" class="ml-1" /></VBtn>
              </VCardActions>
            </VCard>
          </VCol>
        </VRow>
      </VCol>
    </VRow>
  </div>
</template>

<style scoped>
.hover-card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  cursor: pointer;
}
.hover-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(var(--v-theme-primary), 0.15) !important;
}
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
