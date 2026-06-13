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
  // Gunakan placeholder kotak (square) ala UniPin
  if (!path) return 'https://placehold.co/400x400/1e293b/ffffff.png?text=Icon'
  return path.startsWith('http') ? path : `/images/${path}`
}
</script>

<template>
  <div class="unipin-style-container">
    <VRow v-if="loading">
      <VCol cols="12" class="text-center mt-12">
        <VProgressCircular indeterminate color="primary" size="64" />
      </VCol>
    </VRow>

    <template v-else>
      <!-- Hero Carousel with Banners -->
      <VCarousel
        v-if="banners.length > 0"
        height="450"
        hide-delimiter-background
        show-arrows="hover"
        class="rounded-lg overflow-hidden mb-12 elevation-5 unipin-carousel"
        cycle
      >
        <VCarouselItem
          v-for="banner in banners"
          :key="banner.id"
        >
          <div class="carousel-bg" :style="`background-image: url('${getImageUrl(banner.gambar_banner)}')`">
            <div class="carousel-overlay d-flex align-end pb-8 pl-8">
              <div>
                <VChip color="primary" size="small" class="mb-2 font-weight-bold">PROMO</VChip>
                <h2 class="text-h4 font-weight-bold text-white mb-1 text-shadow">{{ banner.judul }}</h2>
                <p class="text-body-2 text-white text-shadow-sm mb-4 max-w-500">{{ banner.deskripsi }}</p>
                <VBtn
                  v-if="banner.link_tujuan"
                  color="primary"
                  class="font-weight-bold px-6"
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
      
      <!-- Empty State for Banner -->
      <div v-else class="mb-12 rounded-lg d-flex align-center justify-center empty-banner-box">
        <div class="text-center pa-8">
          <VIcon icon="ri-image-add-line" size="48" color="primary" class="mb-3 opacity-50" />
          <h3 class="text-h6 font-weight-bold text-white mb-1">Space Iklan Tersedia</h3>
          <p class="text-caption text-grey-lighten-1">Login sebagai admin untuk mengupload banner promo di sini.</p>
        </div>
      </div>

      <!-- Section Title ala UniPin -->
      <div class="d-flex align-center mb-6">
        <h2 class="text-h5 font-weight-bold text-white d-flex align-center gap-2">
          <VIcon icon="ri-gamepad-fill" color="primary" size="28" />
          Populer
        </h2>
      </div>

      <!-- Game Grid ala UniPin (Kecil, Rapat, Banyak Kolom) -->
      <VRow dense class="match-unipin-grid">
        <VCol
          v-for="cat in categories"
          :key="cat.id"
          cols="6"
          sm="4"
          md="3"
          lg="2"
        >
          <VCard elevation="2" class="h-100 d-flex flex-column unipin-card rounded-lg" :to="`/game/${cat.slug}`">
            <div class="img-wrapper bg-surface">
              <VImg
                :src="getImageUrl(cat.gambar_logo)"
                height="150"
                cover
                class="game-img"
              />
            </div>
            
            <VCardItem class="pa-3 text-center card-content flex-grow-1 d-flex flex-column justify-center">
              <VCardTitle class="text-subtitle-2 font-weight-bold text-white mb-0 line-clamp-1" style="font-size: 0.9rem !important;">{{ cat.nama_game }}</VCardTitle>
              <VCardSubtitle class="text-caption text-grey-lighten-1 mt-1 line-clamp-1">{{ cat.deskripsi }}</VCardSubtitle>
            </VCardItem>
          </VCard>
        </VCol>
      </VRow>
    </template>
  </div>
</template>

<style scoped>
/* UniPin Style Tweaks */
.unipin-carousel {
  border-radius: 12px !important;
  box-shadow: 0 10px 30px rgba(0,0,0,0.5) !important;
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
  background: linear-gradient(to top, rgba(11, 15, 25, 0.9) 0%, rgba(11, 15, 25, 0.2) 60%, rgba(0,0,0,0) 100%);
}

.empty-banner-box {
  background: rgba(30, 41, 59, 0.5);
  border: 1px dashed rgba(255, 255, 255, 0.2);
  min-height: 200px;
}

.text-shadow {
  text-shadow: 0 2px 8px rgba(0,0,0,0.8);
}

.text-shadow-sm {
  text-shadow: 0 1px 4px rgba(0,0,0,0.8);
}

.max-w-500 {
  max-width: 500px;
}

/* UniPin Game Card */
.match-unipin-grid {
  margin: -8px;
}
.match-unipin-grid > .v-col {
  padding: 8px;
}

.unipin-card {
  background: #1e293b !important; /* Latar warna navy/abu-abu gelap ala UniPin */
  border: 1px solid rgba(255, 255, 255, 0.05);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  cursor: pointer;
  overflow: hidden;
}

.unipin-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4) !important;
  border-color: rgba(var(--v-theme-primary), 0.5);
}

.img-wrapper {
  position: relative;
  overflow: hidden;
  border-bottom: 2px solid rgba(var(--v-theme-primary), 0.8);
}

.game-img {
  transition: transform 0.3s ease;
}

.unipin-card:hover .game-img {
  transform: scale(1.05);
}

.card-content {
  background: #1e293b;
}

.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: normal;
}

.opacity-50 {
  opacity: 0.5;
}
</style>
