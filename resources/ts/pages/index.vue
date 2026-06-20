<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

definePage({
  meta: {
    layout: 'frontend',
    public: true,
  },
})

const akunGames = ref<any[]>([])
const banners = ref<any[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    const [akunRes, bannerRes] = await Promise.all([
      axios.get('/api/akun-games'),
      axios.get('/api/banners')
    ])
    akunGames.value = akunRes.data.data
    banners.value = bannerRes.data.data
  } catch (error) {
    console.error('Error fetching data:', error)
  } finally {
    loading.value = false
  }
})

const getImageUrl = (path: string) => {
  if (!path) return 'https://placehold.co/400x400/f1f5f9/94a3b8.png?text=Icon'
  return path.startsWith('http') ? path : `/images/${path}`
}

const getAkunImage = (path: string) => {
  if (!path) return 'https://placehold.co/400x400/f1f5f9/94a3b8.png?text=Image'
  return `/images/akun/${path}`
}

const formatRupiah = (angka: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka)
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
          <h3 class="text-h6 font-weight-bold text-primary mb-1">Space Iklan Tersedia</h3>
          <p class="text-caption text-medium-emphasis">Login sebagai admin untuk mengupload banner promo di sini.</p>
        </div>
      </div>

      <!-- Section Title ala UniPin -->
      <div class="d-flex align-center mb-6 justify-space-between">
        <h2 class="text-h5 font-weight-bold text-high-emphasis d-flex align-center gap-2">
          <VIcon icon="ri-store-2-fill" color="primary" size="28" />
          Marketplace Akun Game
        </h2>
      </div>

      <!-- Game Grid ala UniPin -->
      <VRow dense class="match-unipin-grid">
        <VCol
          v-for="akun in akunGames"
          :key="akun.id"
          cols="6"
          sm="4"
          md="3"
          lg="3"
        >
          <VCard elevation="2" class="h-100 d-flex flex-column unipin-card rounded-lg" :to="`/akun/${akun.id}`">
            <div class="img-wrapper bg-surface" style="position: relative;">
              <VImg
                :src="getAkunImage(akun.gambar_utama)"
                height="180"
                cover
                class="game-img"
              />
              <VChip 
                color="primary" 
                size="small" 
                variant="elevated"
                class="font-weight-bold position-absolute" 
                style="bottom: 8px; left: 8px; z-index: 2;"
              >
                {{ akun.kategori?.nama_game }}
              </VChip>
            </div>
            
            <VCardItem class="pa-4 text-left card-content flex-grow-1 d-flex flex-column justify-space-between">
              <div>
                <VCardTitle class="text-subtitle-1 font-weight-bold text-high-emphasis mb-1 line-clamp-2" style="line-height: 1.3;">{{ akun.judul_akun }}</VCardTitle>
                <div class="text-success font-weight-black text-h6 mt-2">{{ formatRupiah(akun.harga) }}</div>
              </div>
              <div class="d-flex justify-space-between align-center mt-4">
                <span class="text-caption text-medium-emphasis">Via: {{ akun.login_via }}</span>
                <VBtn size="small" color="primary" variant="tonal" class="rounded-pill font-weight-bold px-4">Beli</VBtn>
              </div>
            </VCardItem>
          </VCard>
        </VCol>
        
        <VCol cols="12" v-if="akunGames.length === 0">
          <div class="text-center pa-12">
            <VIcon icon="ri-store-3-line" size="64" color="grey-lighten-1" class="mb-4" />
            <h3 class="text-h5 font-weight-bold text-medium-emphasis">Belum ada akun game yang dijual</h3>
          </div>
        </VCol>
      </VRow>
    </template>
  </div>
</template>

<style scoped>
/* UniPin Style Tweaks */
.unipin-carousel {
  border-radius: 12px !important;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
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
  background: linear-gradient(to top, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.2) 60%, rgba(0,0,0,0) 100%);
}

.empty-banner-box {
  background: rgba(var(--v-theme-primary), 0.05);
  border: 1px dashed rgba(var(--v-theme-primary), 0.3);
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
  background: #ffffff !important; /* Latar warna putih */
  border: 1px solid rgba(0, 0, 0, 0.05);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  cursor: pointer;
  overflow: hidden;
}

.unipin-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
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
  background: #ffffff;
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
