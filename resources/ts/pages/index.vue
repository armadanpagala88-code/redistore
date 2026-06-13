<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})

const categories = ref<any[]>([])
const banners = ref<any[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    const [catRes, bannerRes] = await Promise.all([
      axios.get('/api/kategori-game'),
      axios.get('/api/banners')
    ])
    categories.value = catRes.data.data
    banners.value = bannerRes.data.data
  } catch (error) {
    console.error('Error fetching data:', error)
  } finally {
    loading.value = false
  }
})

const getImageUrl = (path: string) => {
  if (!path) return 'https://placehold.co/800x400?text=Image'
  return path.startsWith('http') ? path : `/images/${path}`
}
</script>

<template>
  <div>
    <VRow v-if="loading">
      <VCol cols="12" class="text-center mt-12">
        <VProgressCircular indeterminate color="primary" size="64" />
      </VCol>
    </VRow>

    <template v-else>
      <!-- Hero Carousel with Banners -->
      <VCarousel
        v-if="banners.length > 0"
        height="400"
        hide-delimiter-background
        show-arrows="hover"
        class="rounded-xl overflow-hidden mb-12 elevation-10 hero-carousel"
        cycle
      >
        <VCarouselItem
          v-for="banner in banners"
          :key="banner.id"
        >
          <div class="carousel-bg" :style="`background-image: url('${getImageUrl(banner.gambar_banner)}')`">
            <div class="carousel-overlay d-flex align-end pb-10 pl-10">
              <div>
                <VChip color="primary" size="small" class="mb-3 font-weight-bold">PROMO</VChip>
                <h2 class="text-h3 font-weight-bold text-white mb-2 text-shadow">{{ banner.judul }}</h2>
                <p class="text-body-1 text-white text-shadow-sm mb-4 max-w-500">{{ banner.deskripsi }}</p>
                <VBtn
                  v-if="banner.link_tujuan"
                  color="primary"
                  size="large"
                  class="rounded-pill px-8 font-weight-bold"
                  :href="banner.link_tujuan"
                  target="_blank"
                >
                  Lihat Detail
                </VBtn>
              </div>
            </div>
          </div>
        </VCarouselItem>
      </VCarousel>

      <!-- Section Title -->
      <div class="d-flex align-center mb-8">
        <VIcon icon="ri-fire-fill" color="error" size="32" class="mr-2" />
        <h2 class="text-h4 font-weight-bold text-white">Trending Top Up</h2>
      </div>

      <!-- Game Grid -->
      <VRow>
        <VCol
          v-for="cat in categories"
          :key="cat.id"
          cols="12"
          md="4"
          sm="6"
        >
          <VCard elevation="0" class="h-100 d-flex flex-column game-card rounded-xl overflow-hidden" :to="`/game/${cat.slug}`">
            <div class="img-wrapper">
              <VImg
                :src="getImageUrl(cat.gambar_logo)"
                height="220"
                cover
                class="game-img"
              />
              <div class="img-overlay"></div>
            </div>
            
            <VCardItem class="flex-grow-1 card-content position-relative z-1 pa-5">
              <VCardTitle class="text-h6 font-weight-bold text-white mb-1">{{ cat.nama_game }}</VCardTitle>
              <VCardSubtitle class="text-body-2 text-grey-lighten-1">{{ cat.deskripsi }}</VCardSubtitle>
            </VCardItem>
            
            <VCardActions class="pa-5 pt-0 mt-auto card-content position-relative z-1">
              <VBtn
                color="primary"
                variant="outlined"
                block
                class="rounded-pill font-weight-bold action-btn"
              >
                Top Up
              </VBtn>
            </VCardActions>
          </VCard>
        </VCol>
      </VRow>
    </template>
  </div>
</template>

<style scoped>
/* Hero Carousel */
.hero-carousel {
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 20px 40px rgba(0,0,0,0.4) !important;
}

.carousel-bg {
  width: 100%;
  height: 100%;
  background-size: cover;
  background-position: center;
}

.carousel-overlay {
  width: 100%;
  height: 100%;
  background: linear-gradient(to top, rgba(11, 15, 25, 1) 0%, rgba(11, 15, 25, 0.4) 50%, rgba(0,0,0,0) 100%);
}

.text-shadow {
  text-shadow: 0 4px 12px rgba(0,0,0,0.8);
}

.text-shadow-sm {
  text-shadow: 0 2px 4px rgba(0,0,0,0.8);
}

.max-w-500 {
  max-width: 500px;
}

/* Game Card */
.game-card {
  background: rgba(255, 255, 255, 0.03) !important;
  border: 1px solid rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  cursor: pointer;
}

.game-card:hover {
  transform: translateY(-10px);
  border-color: rgba(var(--v-theme-primary), 0.5);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5), 0 0 20px rgba(var(--v-theme-primary), 0.2) !important;
}

.img-wrapper {
  position: relative;
  overflow: hidden;
}

.game-img {
  transition: transform 0.6s ease;
}

.game-card:hover .game-img {
  transform: scale(1.08);
}

.img-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(to bottom, rgba(0,0,0,0) 0%, rgba(11, 15, 25, 0.8) 100%);
  z-index: 0;
}

.card-content {
  background: linear-gradient(to bottom, rgba(11, 15, 25, 0.8) 0%, rgba(11, 15, 25, 1) 100%);
}

.action-btn {
  border-width: 2px;
  transition: all 0.3s ease;
}

.game-card:hover .action-btn {
  background: rgb(var(--v-theme-primary));
  color: white !important;
}

.z-1 {
  z-index: 1;
}
</style>
